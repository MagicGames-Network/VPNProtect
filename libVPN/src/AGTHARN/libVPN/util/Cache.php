<?php

declare(strict_types=1);

namespace AGTHARN\libVPN\util;

class Cache
{
    protected static array $results = [];

    public static function add(string $ip, bool $result, int $cacheLimit = 50): void
    {
        if (count(self::$results) > $cacheLimit) {
            array_shift(self::$results);
        }
        self::$results[$ip] = $result;
    }

    public static function remove(string $ip): void
    {
        unset(self::$results[$ip]);
    }

    public static function get(string $ip): ?bool
    {
        return self::$results[$ip] ?? null;
    }
}
