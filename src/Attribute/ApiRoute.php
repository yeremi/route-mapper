<?php

declare(strict_types=1);

namespace Yeremi\RouteMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class ApiRoute
{
    public function __construct(
        private string $path
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
