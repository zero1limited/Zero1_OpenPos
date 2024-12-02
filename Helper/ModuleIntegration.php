<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Magento\Framework\Module\ModuleListInterface;

class ModuleIntegration extends AbstractHelper
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
     * @var OpenPosHelper
     */
    protected $openPosHelper;

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @param Context $context
     * @param PosHelper $openPosHelper
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        Context $context,
        OpenPosHelper $openPosHelper,
        ModuleListInterface $moduleList,
        array $openPosModules = []
    ) {
        $this->openPosHelper = $openPosHelper;
        $this->moduleList = $moduleList;
        $this->openPosModules = $openPosModules;
        
        parent::__construct($context);
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
        $mode = $this->openPosHelper->getModuleIntegrationMode();

        if($mode === self::MODULE_INTEGRATION_MODE_ALL) {
            return $this->moduleList->getNames();
        }

        if($mode === self::MODULE_INTEGRATION_MODE_SPECIFIC) {
            $whitelisedModules = $this->openPosHelper->getModuleIntegrationModules();
            return array_merge($whitelisedModules, $this->getCoreWhitelistModules());
        }

        if($mode === self::MODULE_INTEGRATION_MODE_NONE) {
            return $this->getCoreWhitelistModules();
        }

        return $this->moduleList->getNames();
    }
}
