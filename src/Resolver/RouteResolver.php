<?php

declare(strict_types=1);

namespace Yeremi\RouteMapper\Resolver;

use Yeremi\RouteMapper\Attribute\ApiRoute;

class RouteResolver
{
    /**
     * @param ApiRoute $apiRoute
     * @param array $parameters
     *
     * @return string
     */
    public function resolve(ApiRoute $apiRoute, array $parameters): string
    {
        $route = $apiRoute->getPath();
        foreach ($parameters as $key => $value) {
            $route = str_replace("{" . $key . "}", (string)$value, $route);
        }

        return $route;
    }
}
