<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Model\System\Message;

use Magento\Framework\Notification\MessageInterface;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\InventoryCompatibility;

class InventoryModuleMissing implements MessageInterface
{
    /**
     * @var OpenPosConfiguration
     */
    protected $openPosConfiguration;

    /**
     * @var InventoryCompatibility
     */
    protected $inventoryCompatibility;

    /**
     * @param OpenPosConfiguration $openPosConfiguration
     * @param InventoryCompatibility $inventoryCompatibility
     */
    public function __construct(
        OpenPosConfiguration $openPosConfiguration,
        InventoryCompatibility $inventoryCompatibility
    ) {
        $this->openPosConfiguration = $openPosConfiguration;
        $this->inventoryCompatibility = $inventoryCompatibility;
    }

    /**
     * @inheritDoc
     */
    public function getIdentity()
    {
        return 'zero1_openpos_inventory_module_missing';
    }

    /**
     * @inheritDoc
     */
    public function isDisplayed()
    {
        return $this->openPosConfiguration->isEnabled() && $this->inventoryCompatibility->isMsiPackageMissing();
    }

    /**
     * @inheritDoc
     */
    public function getText()
    {
        return __(
            'OpenPOS: Magento Inventory (MSI) is enabled, but %1 is not installed. Till sales may not update stock levels correctly.',
            InventoryCompatibility::OPENPOS_INVENTORY_PACKAGE
        );
    }

    /**
     * @inheritDoc
     */
    public function getSeverity()
    {
        return self::SEVERITY_MAJOR;
    }
}
