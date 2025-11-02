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
}