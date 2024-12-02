<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ModuleIntegrationMode implements OptionSourceInterface
{
    /**
     * Return an array of module integration modes.
     * 
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'all', 'label' => __('All')],
            ['value' => 'specific', 'label' => __('Specific modules')],
            ['value' => 'none', 'label' => __('None')]
        ];
    }
}
