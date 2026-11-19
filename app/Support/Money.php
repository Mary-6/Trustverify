<?php

namespace App\Support;

class Money
{
    public static function normalize(float|int|string $amount): string
    {
        return bcadd((string) $amount, '0', 2);
    }

    public static function add(float|int|string $a, float|int|string $b): string
    {
        return bcadd(self::normalize($a), self::normalize($b), 2);
    }

    public static function sub(float|int|string $a, float|int|string $b): string
    {
        return bcsub(self::normalize($a), self::normalize($b), 2);
    }

    public static function mul(float|int|string $a, float|int|string $b): string
    {
        return bcmul(self::normalize($a), self::normalize($b), 2);
    }

    public static function compare(float|int|string $a, float|int|string $b): int
    {
        return bccomp(self::normalize($a), self::normalize($b), 2);
    }

    public static function isPositive(float|int|string $amount): bool
    {
        return self::compare($amount, '0') > 0;
    }
}
