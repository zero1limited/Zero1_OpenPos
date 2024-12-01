<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Zero1\OpenPos\Helper\ModuleIntegration as ModuleIntegration;

class ModuleBlockRestriction implements ObserverInterface
{
    /**
     * @var OpenPosHelper
     */
    protected $openPosHelper;

    /**
     * @var ModuleIntegration
     */
    protected $moduleIntegration;

    /**
     * @param OpenPosHelper $openPosHelper
     * @param ModuleIntegration $moduleIntegration
     */
    public function __construct(
        OpenPosHelper $openPosHelper,
        ModuleIntegration $moduleIntegration
    ) {
        $this->openPosHelper = $openPosHelper;
        $this->moduleIntegration = $moduleIntegration;
    }

    /**
     * @return void
     */
    public function execute(Observer $observer): void
    {
        // Check if module is enabled
        if(!$this->openPosHelper->isEnabled()) {
            return;
        }

        // Check a POS store is set, and check if we are currently on it
        if($this->openPosHelper->getPosStore() && !$this->openPosHelper->currentlyOnPosStore()) {
            return;
        }

        $allowedModules = $this->moduleIntegration->getAllowedModules();

        $layout = $observer->getLayout();
        $allBlocks = $layout->getAllBlocks();

        foreach ($allBlocks as $block) {
            $moduleName = $this->getModuleFromBlock($block);
            if (!in_array($moduleName, $allowedModules)) {
                $layout->unsetElement($block->getNameInLayout());
            }
        }
    }

    /**
     * Return a module name from a block
     * Using $block->getModuleName() doesn't seem to reliable
     * 
     * @return string|null
     */
    protected function getModuleFromBlock($block): ?string
    {
        $module = $block->getModule();
        if(!$module) {
            $template = $block->getTemplate();
            if ($template && strpos($template, '::') !== false) {
                $module = explode('::', $template)[0];
            }
        }

        return $module;
    }
}
