<?php

declare(strict_types=1);

namespace Yeremi\RouteMapper\Test\Resolver;

use PHPUnit\Framework\TestCase;
use Yeremi\RouteMapper\Attribute\ApiRoute;
use Yeremi\RouteMapper\Exception\InvalidRouteParameterException;
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

    public function testResolveDoesNotTreatPlaceholderInValueAsMissing(): void
    {
        $resolver = new RouteResolver();
        $resolved = $resolver->resolve(new ApiRoute('/films/{id}'), ['id' => '{x}']);

        $this->assertSame('/films/%7Bx%7D', $resolved);
    }

    public function testResolveDoesNotExpandOtherPlaceholderInValue(): void
    {
        $resolver = new RouteResolver();
        $resolved = $resolver->resolve(new ApiRoute('/events/{id}/partnership/{partnership}'), [
            'id' => '{partnership}',
            'partnership' => 'abc',
        ]);

        $this->assertSame('/events/%7Bpartnership%7D/partnership/abc', $resolved);
    }

    public function testResolveEncodesUnsafeValues(): void
    {
        // A loop instead of a data provider: composer allows PHPUnit 9 to 11,
        // and no provider syntax works on all of them without errors or deprecations.
        $cases = [
            'path traversal' => ['../templates/soon/1', '/events/..%2Ftemplates%2Fsoon%2F1/x'],
            'slash' => ['a/b', '/events/a%2Fb/x'],
            'query' => ['1?admin=1', '/events/1%3Fadmin%3D1/x'],
            'fragment' => ['1#top', '/events/1%23top/x'],
            'percent' => ['100%', '/events/100%25/x'],
            'space and plus' => ['a b+c', '/events/a%20b%2Bc/x'],
            'non-ascii' => ['São Paulo', '/events/S%C3%A3o%20Paulo/x'],
        ];

        $resolver = new RouteResolver();
        foreach ($cases as $name => [$value, $expected]) {
            $this->assertSame(
                $expected,
                $resolver->resolve(new ApiRoute('/events/{id}/x'), ['id' => $value]),
                $name
            );
        }
    }

    public function testResolveEncodesQueryValueDelimiters(): void
    {
        $resolver = new RouteResolver();
        $resolved = $resolver->resolve(new ApiRoute('/events?theaters={theaters}'), ['theaters' => '7&admin=1']);

        $this->assertSame('/events?theaters=7%26admin%3D1', $resolved);
    }

    public function testResolveThrowsWhenValueIsDotDotSegment(): void
    {
        $resolver = new RouteResolver();

        $this->expectException(InvalidRouteParameterException::class);
        $this->expectExceptionMessage(
            'Route parameter(s) id resolve to the dot segment ".." in "/events/{id}/partnership/{p}".'
        );

        $resolver->resolve(new ApiRoute('/events/{id}/partnership/{p}'), ['id' => '..', 'p' => 'x']);
    }

    public function testResolveThrowsWhenValueIsDotSegment(): void
    {
        $resolver = new RouteResolver();

        $this->expectException(InvalidRouteParameterException::class);
        $this->expectExceptionMessage('dot segment "."');

        $resolver->resolve(new ApiRoute('/events/{id}'), ['id' => '.']);
    }

    public function testResolveThrowsWhenCombinedValuesFormDotSegment(): void
    {
        $resolver = new RouteResolver();

        $this->expectException(InvalidRouteParameterException::class);
        $this->expectExceptionMessage('Route parameter(s) x, y resolve to the dot segment ".."');

        $resolver->resolve(new ApiRoute('/a/{x}{y}/b'), ['x' => '.', 'y' => '.']);
    }

    public function testResolveThrowsWhenValueCompletesDotSegment(): void
    {
        $resolver = new RouteResolver();

        $this->expectException(InvalidRouteParameterException::class);

        $resolver->resolve(new ApiRoute('/a/{x}./b'), ['x' => '']);
    }

    public function testResolveThrowsForNonIdentifierPlaceholder(): void
    {
        $resolver = new RouteResolver();

        $this->expectException(InvalidRouteParameterException::class);

        $resolver->resolve(new ApiRoute('/a/{0}/b'), ['..']);
    }

    public function testResolveAllowsDotsInsideLongerValues(): void
    {
        $resolver = new RouteResolver();
        $apiRoute = new ApiRoute('/events/{id}');

        foreach (['1.5', 'a..b', '...', '.hidden', 'file.'] as $value) {
            $this->assertSame('/events/' . $value, $resolver->resolve($apiRoute, ['id' => $value]), $value);
        }
    }

    public function testResolveAllowsDotValuesInQueryAndFragment(): void
    {
        $resolver = new RouteResolver();
        $resolved = $resolver->resolve(new ApiRoute('/events?id={id}#{f}'), ['id' => '..', 'f' => '.']);

        $this->assertSame('/events?id=..#.', $resolved);
    }

    public function testResolveKeepsLiteralDotSegmentsFromTemplate(): void
    {
        $resolver = new RouteResolver();
        $resolved = $resolver->resolve(new ApiRoute('/a/../b/{id}'), ['id' => '1']);

        $this->assertSame('/a/../b/1', $resolved);
    }

    public function testResolveKeepsSafeSubDelimiters(): void
    {
        $resolver = new RouteResolver();
        $resolved = $resolver->resolve(new ApiRoute('/items/{id}'), ['id' => "a,b:c@d!e\$f'g(h)i*j"]);

        $this->assertSame("/items/a,b:c@d!e\$f'g(h)i*j", $resolved);
    }
}
