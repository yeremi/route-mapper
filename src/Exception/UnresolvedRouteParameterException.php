<?php

declare(strict_types=1);

namespace Yeremi\RouteMapper\Exception;

use InvalidArgumentException;

/**
 * Thrown when a route still contains `{placeholder}` segments after resolution,
 * i.e. a required parameter was not provided.
 */
class UnresolvedRouteParameterException extends InvalidArgumentException
{
    /**
     * @param string[] $missing
     */
    public static function forPath(string $path, array $missing): self
    {
        return new self(sprintf(
            'Missing value for route parameter(s) %s in "%s".',
            implode(', ', $missing),
            $path
        ));
    }
}
