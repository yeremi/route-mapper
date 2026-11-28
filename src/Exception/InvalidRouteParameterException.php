<?php

declare(strict_types=1);

namespace Yeremi\RouteMapper\Exception;

use InvalidArgumentException;

/**
 * Thrown when parameter values would change the endpoint a route points to,
 * e.g. by turning a path segment into the dot segment `.` or `..`.
 */
class InvalidRouteParameterException extends InvalidArgumentException
{
    /**
     * @param string[] $parameters
     */
    public static function forDotSegment(string $path, array $parameters, string $segment): self
    {
        return new self(sprintf(
            'Route parameter(s) %s resolve to the dot segment "%s" in "%s".',
            implode(', ', $parameters),
            $segment,
            $path
        ));
    }
}
