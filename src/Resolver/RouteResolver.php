<?php

declare(strict_types=1);

namespace Yeremi\RouteMapper\Resolver;

use Yeremi\RouteMapper\Attribute\ApiRoute;
use Yeremi\RouteMapper\Exception\UnresolvedRouteParameterException;

class RouteResolver
{
    private const PLACEHOLDER_PATTERN = '/\{([A-Za-z_][A-Za-z0-9_]*)\}/';

    /**
     * @param ApiRoute $apiRoute
     * @param array $parameters
     *
     * @throws UnresolvedRouteParameterException When a placeholder has no matching parameter.
     *
     * @return string
     */
    public function resolve(ApiRoute $apiRoute, array $parameters): string
    {
        $route = $apiRoute->getPath();
        foreach ($parameters as $key => $value) {
            $route = str_replace("{" . $key . "}", (string)$value, $route);
        }

        if (preg_match_all(self::PLACEHOLDER_PATTERN, $route, $matches) > 0) {
            throw UnresolvedRouteParameterException::forPath(
                $apiRoute->getPath(),
                array_values(array_unique($matches[1]))
            );
        }

        return $route;
    }
}
