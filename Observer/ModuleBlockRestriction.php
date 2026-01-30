<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\ModuleIntegration;
use Zero1\OpenPos\Model\TillSessionManagement;

class ModuleBlockRestriction implements ObserverInterface
{
    /**
     * @var OpenPosConfiguration
     */
    protected $openPosConfiguration;

    /**
     * @var ModuleIntegration
     */
    protected $moduleIntegration;

    /**
     * @var TillSessionManagement
     */
    protected $tillSessionManagement;

    /**
     * @param OpenPosConfiguration $openPosConfiguration
     * @param ModuleIntegration $moduleIntegration
     * @param TillSessionManagement $tillSessionManagement
     */
    public function __construct(
        OpenPosConfiguration $openPosConfiguration,
        ModuleIntegration $moduleIntegration,
        TillSessionManagement $tillSessionManagement
    ) {
        $this->openPosConfiguration = $openPosConfiguration;
        $this->moduleIntegration = $moduleIntegration;
        $this->tillSessionManagement = $tillSessionManagement;
    }

    /**
     * @return void
     */
    public function execute(Observer $observer): void
    {
        // Check if module is enabled
        if(!$this->openPosConfiguration->isEnabled()) {
            return;
        }

        // Check we are currently on POS store
        if(!$this->tillSessionManagement->currentlyOnPosStore()) {
            return;
        }

        $allowedModules = $this->moduleIntegration->getAllowedModules();

        $layout = $observer->getLayout();
        $allBlocks = $layout->getAllBlocks();

        foreach ($allBlocks as $block) {
            $moduleName = $this->getModuleFromBlock($block);
            if ($moduleName && !in_array($moduleName, $allowedModules)) {
                $layout->unsetElement($block->getNameInLayout());
            }
        }
    }

    /**
     * Return a module name from a block
     * 
     * @return string|null
     */
    protected function getModuleFromBlock($block): ?string
    {
        $module = $block->getModuleName();
        if(!$module) {
            $template = $block->getTemplate();
            if ($template && strpos($template, '::') !== false) {
                $module = explode('::', $template)[0];
            }
        }

        return $module;
    }
}
