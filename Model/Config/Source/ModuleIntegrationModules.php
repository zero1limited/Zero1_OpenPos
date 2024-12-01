<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Zero1\OpenPos\Helper\ModuleIntegration;
use Magento\Framework\Module\ModuleListInterface;

class ModuleIntegrationModules implements OptionSourceInterface
{
    /**
     * @var ModuleIntegration
     */
    protected $moduleIntegration;

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @param ModuleIntegration $moduleIntegration
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        ModuleIntegration $moduleIntegration,
        ModuleListInterface $moduleList
    ) {
        $this->moduleIntegration = $moduleIntegration;
        $this->moduleList = $moduleList;
    }

    /**
     * Return an array of Magento modules for OpenPOS module integration configuation.
     * 
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        $modules = $this->moduleList->getNames();
        $coreWhitelistModules = $this->moduleIntegration->getCoreWhitelistModules();

        foreach ($modules as $moduleName) {
            if(!in_array($moduleName, $coreWhitelistModules)) {
                $options[] = [
                    'value' => $moduleName,
                    'label' => $moduleName
                ];
            }
        }

        return $options;
    }
}
