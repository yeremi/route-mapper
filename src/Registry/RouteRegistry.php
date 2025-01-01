<?php

declare(strict_types=1);

namespace Yeremi\RouteMapper\Registry;

use ReflectionClass;
use Yeremi\RouteMapper\Attribute\ApiRoute;

class RouteRegistry
{
    /**
     * @var array<string, array<string, ApiRoute>>
     */
    private array $routes = [];

    public function registerRoutes(object $repository): void
    {
        $reflectionClass = new ReflectionClass($repository);

        foreach ($reflectionClass->getMethods() as $method) {
            $attributes = $method->getAttributes(ApiRoute::class);
            foreach ($attributes as $attribute) {
                /** @var ApiRoute $route */
                $route = $attribute->newInstance();
                $this->routes[$reflectionClass->getName()][$method->getName()] = $route;
            }
        }
    }

    public function getRoute(string $className, string $method): ?ApiRoute
    {
        return $this->routes[$className][$method] ?? null;
    }
}
