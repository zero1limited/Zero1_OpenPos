<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Sales\Api\Data\OrderInterface;

class Data extends AbstractHelper
{
    const CONFIG_PATH_GENERAL_ENABLE = 'openpos/general/enable';
    const CONFIG_PATH_GENERAL_TFA_ENABLE = 'openpos/general/tfa_enable';
    const CONFIG_PATH_GENERAL_POS_STORE = 'openpos/general/pos_store';
    const CONFIG_PATH_GENERAL_SESSION_LIFETIME = 'openpos/general/session_lifetime';
    const CONFIG_PATH_GENERAL_BYPASS_STOCK = 'openpos/general/bypass_stock';
    const CONFIG_PATH_GENERAL_BARCODE_ATTRIBUTE = 'openpos/general/barcode_attribute';
    const CONFIG_PATH_GENERAL_TILL_USERS = 'openpos/general/till_users';
    const CONFIG_PATH_GENERAL_MODULE_INTEGRATION_MODE = 'openpos/general/module_integration_mode';
    const CONFIG_PATH_GENERAL_MODULE_INTEGRATION_MODULES = 'openpos/general/module_integration_modules';

    const CONFIG_PATH_CUSTOMISATION_RECEIPT_HEADER = 'openpos/customisation/receipt_header';
    const CONFIG_PATH_CUSTOMISATION_RECEIPT_FOOTER = 'openpos/customisation/receipt_footer';
    const CONFIG_PATH_CUSTOMISATION_PRICE_EDITOR_BARCODE = 'openpos/customisation/price_editor_barcode';
    const CONFIG_PATH_CUSTOMISATION_CUSTOM_PRODUCT_BARCODE = 'openpos/customisation/custom_product_barcode';
    const CONFIG_PATH_CUSTOMISATION_BARCODE_SCANNER_EXIT_CHARACTER = 'openpos/customisation/barcode_scanner_exit_character';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerFactory;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterfaceFactory $customerFactory
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerFactory,
        EncryptorInterface $encryptor
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->encryptor = $encryptor;
        parent::__construct($context);
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
     * Check if we should bypass stock checks.
     *
     * @return bool
     */
    public function bypassStock(): bool
    {
        if(!$this->currentlyOnPosStore()) {
            return false;
        }

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
    public function getModuleIntegrationMode(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_MODULE_INTEGRATION_MODE);
    }

    /**
     * @return array
     */
    public function getModuleIntegrationModules(): array
    {
        return explode(",", (string)$this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_MODULE_INTEGRATION_MODULES));
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

    /**
     * Check if we are currently on the POS store.
     *
     * @return bool
     */
    public function currentlyOnPosStore(): bool
    {
        return $this->storeManager->getStore()->getId() == $this->getPosStoreId();
    }

    /**
     * Check if an order is a POS order.
     * 
     * @param OrderInterface $order
     * @return bool
     */
    public function isPosOrder(OrderInterface $order): bool
    {
        if($order->getStoreId() == $this->getPosStoreId() && strpos($order->getPayment()->getMethodInstance()->getCode(), 'pos') !== false) {
            return true;
        }

        return false;
    }
}
