<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class BarcodeScannerExitCharacter implements OptionSourceInterface
{
    /**
     * Return an array of possible exit characters.
     * 
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'tab', 'label' => __('Tab (default)')],
            ['value' => 'enter', 'label' => __('Enter')],
            ['value' => 'space', 'label' => __('Space')]
        ];
    }
}
