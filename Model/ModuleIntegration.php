<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Model;

use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Magento\Framework\Module\ModuleListInterface;

class ModuleIntegration
{
    const MODULE_INTEGRATION_MODE_ALL = 'all';
    const MODULE_INTEGRATION_MODE_SPECIFIC = 'specific';
    const MODULE_INTEGRATION_MODE_NONE = 'none';

    /**
     * Installed OpenPOS modules / modules required for OpenPOS to function
     * 
     * @var array
     */
    protected $openPosModules;

    /**
     * @var OpenPosConfiguration
     */
    protected $openPosConfiguration;

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @param OpenPosConfiguration $openPosConfiguration
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        OpenPosConfiguration $openPosConfiguration,
        ModuleListInterface $moduleList,
        array $openPosModules = []
    ) {
        $this->openPosConfiguration = $openPosConfiguration;
        $this->moduleList = $moduleList;
        $this->openPosModules = $openPosModules;
    }

    /**
     * Return an array of modules either required for OpenPOS to function, or that have been designed for OpenPOS
     * 
     * @return array
     */
    public function getCoreWhitelistModules(): array
    {
        $allModules = $this->moduleList->getNames();

        foreach ($allModules as $moduleName) {
            // Add core Magento modules
            if(strpos($moduleName, 'Magento_') !== false) {
                $coreMagentoModules[] = $moduleName;
            }
        }

        return array_merge($coreMagentoModules, $this->openPosModules);
    }

    /**
     * Return an array of modules allowed to display blocks on the OpenPOS frontend
     * 
     * @return array
     */
    public function getAllowedModules(): array
    {
        $mode = $this->openPosConfiguration->getModuleIntegrationMode();

        if($mode === self::MODULE_INTEGRATION_MODE_ALL) {
            return $this->moduleList->getNames();
        }

        if($mode === self::MODULE_INTEGRATION_MODE_SPECIFIC) {
            $whitelisedModules = $this->openPosConfiguration->getModuleIntegrationModules();
            return array_merge($whitelisedModules, $this->getCoreWhitelistModules());
        }

        if($mode === self::MODULE_INTEGRATION_MODE_NONE) {
            return $this->getCoreWhitelistModules();
        }

        return $this->moduleList->getNames();
    }
}
