<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;

class Configuration
{
    const CONFIG_PATH_GENERAL_ENABLE = 'openpos/general/enable';
    const CONFIG_PATH_GENERAL_TFA_ENABLE = 'openpos/general/tfa_enable';
    const CONFIG_PATH_GENERAL_POS_STORE = 'openpos/general/pos_store';
    const CONFIG_PATH_GENERAL_SESSION_LIFETIME = 'openpos/general/session_lifetime';
    const CONFIG_PATH_GENERAL_BYPASS_STOCK = 'openpos/general/bypass_stock';
    const CONFIG_PATH_GENERAL_BARCODE_ATTRIBUTE = 'openpos/general/barcode_attribute';
    const CONFIG_PATH_GENERAL_TILL_USERS = 'openpos/general/till_users';

    const CONFIG_PATH_CUSTOMISATION_RECEIPT_HEADER = 'openpos/customisation/receipt_header';
    const CONFIG_PATH_CUSTOMISATION_RECEIPT_FOOTER = 'openpos/customisation/receipt_footer';
    const CONFIG_PATH_CUSTOMISATION_PRICE_EDITOR_BARCODE = 'openpos/customisation/price_editor_barcode';
    const CONFIG_PATH_CUSTOMISATION_CUSTOM_PRODUCT_BARCODE = 'openpos/customisation/custom_product_barcode';
    const CONFIG_PATH_CUSTOMISATION_BARCODE_SCANNER_EXIT_CHARACTER = 'openpos/customisation/barcode_scanner_exit_character';

    const CONFIG_PATH_ADVANCED_MODULE_INTEGRATION_MODE = 'openpos/advanced/module_integration_mode';
    const CONFIG_PATH_ADVANCED_MODULE_INTEGRATION_MODULES = 'openpos/advanced/module_integration_modules';
    const CONFIG_PATH_ADVANCED_FORCE_STORE_BILLING_ADDRESS = 'openpos/advanced/force_store_billing_address';
    const CONFIG_PATH_ADVANCED_EMULATE_SHIPPING_ADDRESS = 'openpos/advanced/emulate_shipping_address';

    const CONFIG_PATH_INTERNAL_IS_CONFIGURED = 'openpos/internal/is_configured';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * For OpenPOS extension use.
     * 
     * @param string $path
     * @return mixed
     */
    public function getConfigValue(string $path)
    {
        return $this->scopeConfig->getValue('zero1_pos/'.$path);
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_ENABLE);
    }

    /**
     * @return bool
     */
    public function isTfaEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_TFA_ENABLE);
    }

    /**
     * @return int|null
     */
    public function getPosStoreId(): ?int
    {
        $storeId = $this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_POS_STORE);
        if($storeId) {
            return (int)$storeId;
        }
        return null;
    }

    /**
     * @return int
     */
    public function getSessionLifetime(): int
    {
        return (int)$this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_SESSION_LIFETIME);
    }

    /**
     * @return bool
     */
    public function bypassStock(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_BYPASS_STOCK);
    }

    /**
     * @return string|null
     */
    public function getBarcodeAttribute(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_BARCODE_ATTRIBUTE);
    }

    /**
     * @return array
     */
    public function getTillUsers(): array
    {
        return explode(",", (string)$this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_TILL_USERS));
    }

    /**
     * @return string|null
     */
    public function getReceiptHeader(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_CUSTOMISATION_RECEIPT_HEADER);
    }

    /**
     * @return string|null
     */
    public function getReceiptFooter(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_CUSTOMISATION_RECEIPT_FOOTER);
    }

    /**
     * @return string|null
     */
    public function getPriceEditorBarcode(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_CUSTOMISATION_PRICE_EDITOR_BARCODE);
    }

    /**
     * @return string|null
     */
    public function getCustomProductBarcode(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_CUSTOMISATION_CUSTOM_PRODUCT_BARCODE);
    }

    /**
     * @return string|null
     */
    public function getBarcodeScannerExitCharacter(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_CUSTOMISATION_BARCODE_SCANNER_EXIT_CHARACTER);
    }

    /**
     * @return string|null
     */
    public function getModuleIntegrationMode(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_ADVANCED_MODULE_INTEGRATION_MODE);
    }

    /**
     * @return array
     */
    public function getModuleIntegrationModules(): array
    {
        return explode(",", (string)$this->scopeConfig->getValue(self::CONFIG_PATH_ADVANCED_MODULE_INTEGRATION_MODULES));
    }

    /**
     * @return bool
     */
    public function getForceStoreBillingAddress(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::CONFIG_PATH_ADVANCED_FORCE_STORE_BILLING_ADDRESS);
    }

    /**
     * @return bool
     */
    public function getEmulateShippingAddress(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::CONFIG_PATH_ADVANCED_EMULATE_SHIPPING_ADDRESS);
    }

    /**
     * @return bool
     */
    public function getIsConfigured(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::CONFIG_PATH_INTERNAL_IS_CONFIGURED);
    }

    /**
     * @return StoreInterface|null
     */
    public function getPosStore(): ?StoreInterface
    {
        try {
            $storeId = $this->getPosStoreId();
            return $this->storeManager->getStore($storeId);
        } catch(\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }
}
