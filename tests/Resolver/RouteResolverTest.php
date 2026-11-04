<?php

declare(strict_types=1);

namespace Yeremi\RouteMapper\Test\Resolver;

use PHPUnit\Framework\TestCase;
use Yeremi\RouteMapper\Attribute\ApiRoute;
use Yeremi\RouteMapper\Exception\UnresolvedRouteParameterException;
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

    public function testResolveMultipleParametersAndQueryString(): void
    {
        $resolver = new RouteResolver();
        $apiRoute = new ApiRoute('/cities/{cityId}/events/{eventId}?theaters={theaters}');
        $resolved = $resolver->resolve($apiRoute, [
            'cityId' => 1,
            'eventId' => 'abc',
            'theaters' => '7,8',
        ]);

        $this->assertSame('/cities/1/events/abc?theaters=7,8', $resolved);
    }

    public function testResolveIgnoresExtraParameters(): void
    {
        $resolver = new RouteResolver();
        $resolved = $resolver->resolve(new ApiRoute('/users'), ['unused' => 'x']);

        $this->assertSame('/users', $resolved);
    }

    public function testResolveThrowsWhenParameterIsMissing(): void
    {
        $resolver = new RouteResolver();
        $apiRoute = new ApiRoute('/cities/{cityId}/events/{eventId}');

        $this->expectException(UnresolvedRouteParameterException::class);
        $this->expectExceptionMessage(
            'Missing value for route parameter(s) cityId in "/cities/{cityId}/events/{eventId}".'
        );

        $resolver->resolve($apiRoute, ['eventId' => 'abc']);
    }

    public function testResolveThrowsListingEveryMissingParameter(): void
    {
        $resolver = new RouteResolver();
        $apiRoute = new ApiRoute('/a/{first}/b/{second}/c/{first}');

        $this->expectException(UnresolvedRouteParameterException::class);
        $this->expectExceptionMessage('Missing value for route parameter(s) first, second');

        $resolver->resolve($apiRoute, []);
    }
}
