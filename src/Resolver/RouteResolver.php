<?php

declare(strict_types=1);

namespace Yeremi\RouteMapper\Resolver;

use Yeremi\RouteMapper\Attribute\ApiRoute;
use Yeremi\RouteMapper\Exception\InvalidRouteParameterException;
use Yeremi\RouteMapper\Exception\UnresolvedRouteParameterException;

class RouteResolver
{
    private const PLACEHOLDER_PATTERN = '/\{([A-Za-z_][A-Za-z0-9_]*)\}/';

    /**
     * RFC 3986 sub-delimiters kept literal after encoding: they cannot change
     * the structure of a path segment or a query value. Characters that can
     * (`/ ? # & = + ;`, braces, `%`) stay percent-encoded.
     */
    private const SAFE_CHARACTERS = [
        '%21' => '!',
        '%24' => '$',
        '%27' => "'",
        '%28' => '(',
        '%29' => ')',
        '%2A' => '*',
        '%2C' => ',',
        '%3A' => ':',
        '%40' => '@',
    ];

    /**
     * Replaces every `{placeholder}` in the route path with its percent-encoded
     * parameter value. All placeholders are substituted in a single pass, so a
     * value is never scanned again for placeholders.
     *
     * @param ApiRoute $apiRoute
     * @param array $parameters
     *
     * @throws UnresolvedRouteParameterException When a placeholder has no matching parameter.
     * @throws InvalidRouteParameterException When a value turns a path segment into `.` or `..`.
     *
     * @return string
     */
    public function resolve(ApiRoute $apiRoute, array $parameters): string
    {
        $path = $apiRoute->getPath();

        if (preg_match_all(self::PLACEHOLDER_PATTERN, $path, $matches) > 0) {
            $missing = array_diff(array_unique($matches[1]), array_map('strval', array_keys($parameters)));
            if ($missing !== []) {
                throw UnresolvedRouteParameterException::forPath($path, array_values($missing));
            }
        }

        $replacements = [];
        foreach ($parameters as $key => $value) {
            $replacements['{' . $key . '}'] = $this->encode((string)$value);
        }

        $this->assertNoDotSegments($path, $replacements);

        return strtr($path, $replacements);
    }

    /**
     * Rejects path segments that the parameters turn into `.` or `..`, which
     * HTTP clients normalise away (RFC 3986 §5.2.4). Only segments of the path
     * part where a placeholder was replaced are checked: literal segments come from
     * the route author, and the query string and fragment are not normalised.
     * Encoded values contain no `/`, `?` or `#`, so each template segment maps
     * to exactly one resolved segment.
     *
     * @param array<string, string> $replacements
     *
     * @throws InvalidRouteParameterException
     */
    private function assertNoDotSegments(string $path, array $replacements): void
    {
        $pathPart = substr($path, 0, strcspn($path, '?#'));

        foreach (explode('/', $pathPart) as $segment) {
            $resolved = strtr($segment, $replacements);
            if ($resolved === $segment || ($resolved !== '.' && $resolved !== '..')) {
                continue;
            }

            $parameters = [];
            foreach ($replacements as $placeholder => $value) {
                if (str_contains($segment, $placeholder)) {
                    $parameters[] = substr($placeholder, 1, -1);
                }
            }

            throw InvalidRouteParameterException::forDotSegment($path, $parameters, $resolved);
        }
    }

    private function encode(string $value): string
    {
        return strtr(rawurlencode($value), self::SAFE_CHARACTERS);
    }
}
