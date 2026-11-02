<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Model;

use Magento\Framework\Module\Manager as ModuleManager;

class InventoryCompatibility
{
    const MAGENTO_INVENTORY_MODULE = 'Magento_InventorySales';
    const OPENPOS_INVENTORY_MODULE = 'Zero1_OpenPosMsi';
    const OPENPOS_INVENTORY_PACKAGE = 'zero1/open-pos-msi';

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        ModuleManager $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Check if Magento Inventory (MSI) is enabled without the OpenPOS MSI package.
     * OpenPOS on its own only supports Magento's legacy stock handling, so without the package
     * till sales never deduct stock under MSI.
     *
     * @return bool
     */
    public function isMsiPackageMissing(): bool
    {
        return $this->moduleManager->isEnabled(self::MAGENTO_INVENTORY_MODULE)
            && !$this->moduleManager->isEnabled(self::OPENPOS_INVENTORY_MODULE);
    }
}
