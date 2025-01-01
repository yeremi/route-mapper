<?php

declare(strict_types=1);

namespace Yeremi\RouteMapper\Test\Registry;

use PHPUnit\Framework\TestCase;
use Yeremi\RouteMapper\Attribute\ApiRoute;
use Yeremi\RouteMapper\Registry\RouteRegistry;

class RouteRegistryTest extends TestCase
{
    public function testRegisterAndGetRoute(): void
    {
        $registry = new RouteRegistry();
        $repository = new class () {
            #[ApiRoute('/users/{id}')]
            public function getUser()
            {
            }
        };

        $registry->registerRoutes($repository);

        $route = $registry->getRoute(get_class($repository), 'getUser');
        $this->assertInstanceOf(ApiRoute::class, $route);
        $this->assertSame('/users/{id}', $route->getPath());
    }
}
