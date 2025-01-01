<?php

declare(strict_types=1);

namespace Yeremi\RouteMapper\Test\Attribute;

use PHPUnit\Framework\TestCase;
use Yeremi\RouteMapper\Attribute\ApiRoute;

class ApiRouteTest extends TestCase
{
    public function testGetPath(): void
    {
        $route = new ApiRoute('/users/{id}');
        $this->assertSame('/users/{id}', $route->getPath());
    }
}
