<?php

declare(strict_types=1);

namespace Yeremi\RouteMapper\Test\Resolver;

use PHPUnit\Framework\TestCase;
use Yeremi\RouteMapper\Attribute\ApiRoute;
use Yeremi\RouteMapper\Resolver\RouteResolver;

class RouteResolverTest extends TestCase
{
    public function testResolve(): void
    {
        $resolver = new RouteResolver();
        $apiRoute = new ApiRoute('/users/{id}');
        $resolved = $resolver->resolve($apiRoute, ['id' => 42]);

        $this->assertSame('/users/42', $resolved);
    }
}
