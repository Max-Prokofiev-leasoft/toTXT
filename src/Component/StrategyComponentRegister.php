<?php

namespace GingerPayments\Payments\Component;

use GingerPayments\Payments\Interfaces\StrategyInterface\BaseStrategy;

/**
 * Class StrategyComponentRegister
 *
 * This class is responsible for registering and retrieving strategy components.
 **/
class StrategyComponentRegister
{
    protected static array $components = [];

    public static function register(string $key, object $component): void
    {
        self::$components[$key] = $component;
    }

    /**
     * @template T of BaseStrategy
     * @param class-string<T> $key
     * @return T|null
     */
    public static function get(string $key)
    {
        return self::$components[$key] ?? null;
    }
}