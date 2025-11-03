<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Helper;

/**
 * This class is a early implementation of a OpenPOS specific payment methods.
 * It will move completely in the future as we implement online methods.
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