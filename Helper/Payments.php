<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Helper;

/**
 * Work in progress
 */

class Payments
{
    protected $methods = [];

    public function __construct(array $methods = [])
    {
        $this->methods = $methods;
    }

    public function getAll(): array
    {
        return $this->methods;
    }

    // public function getByCode(string $code): ?array
    // {
    //     return $this->methods[$code] ?? null;
    // }

    //     public function canUseForLayaway(string $code): bool
    //     {
    //         return !empty($this->methods[$code]['can_use_for_layaway']);
    //     }

    //     public function canUseForSplit(string $code): bool
    //     {
    //         return !empty($this->methods[$code]['can_use_for_split']);
    //     }
}