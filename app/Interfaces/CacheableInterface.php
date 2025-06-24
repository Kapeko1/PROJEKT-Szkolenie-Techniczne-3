<?php

declare(strict_types=1);

namespace App\Interfaces;

interface CacheableInterface
{
    public function getCacheKey(): string;

    public function getCacheTags(): array;
}
