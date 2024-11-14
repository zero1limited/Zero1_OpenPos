<?php

namespace Zero1\OpenPos\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Encryption\EncryptorInterface;

class Data extends AbstractHelper
{
    const CONFIG_PATH_GENERAL_ENABLE = 'zero1_pos/general/enable';
    const CONFIG_PATH_GENERAL_TFA_ENABLE = 'zero1_pos/general/tfa_enable';
    const CONFIG_PATH_GENERAL_POS_STORE = 'zero1_pos/general/pos_store';
    const CONFIG_PATH_GENERAL_REDIRECT_STORE = 'zero1_pos/general/redirect_store';
    const CONFIG_PATH_GENERAL_BYPASS_STOCK = 'zero1_pos/general/bypass_stock';
    const CONFIG_PATH_GENERAL_BARCODE_ATTRIBUTE = 'zero1_pos/general/barcode_attribute';
    const CONFIG_PATH_GENERAL_TILL_USERS = 'zero1_pos/general/till_users';

    const CONFIG_PATH_CUSTOMISATION_RECEIPT_HEADER = 'zero1_pos/customisation/receipt_header';
    const CONFIG_PATH_CUSTOMISATION_RECEIPT_FOOTER = 'zero1_pos/customisation/receipt_footer';
    const CONFIG_PATH_CUSTOMISATION_RECEIPT_FOOTER_QR_LINK = 'zero1_pos/customisation/receipt_footer_qr_link';
    const CONFIG_PATH_CUSTOMISATION_PRICE_EDITOR_BARCODE = 'zero1_pos/customisation/price_editor_barcode';
    const CONFIG_PATH_CUSTOMISATION_CUSTOM_PRODUCT_BARCODE = 'zero1_pos/customisation/custom_product_barcode';

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
     * @return mixed
     */
    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue('zero1_pos/'.$path);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_ENABLE);
    }

    /**
     * @return bool
     */
    public function isTfaEnabled()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_TFA_ENABLE);
    }

    /**
     * @return int
     */
    public function getPosStoreId()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_POS_STORE);
    }

    /**
     * @return int
     */
    public function getRedirectStoreId()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_REDIRECT_STORE);
    }

    /**
     * Check if we should bypass stock checks.
     *
     * @return bool
     */
    public function bypassStock()
    {
        if(!$this->currentlyOnPosStore()) {
            return false;
        }

        return $this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_BYPASS_STOCK);
    }

    /**
     * @return string
     */
    public function getBarcodeAttribute()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_BARCODE_ATTRIBUTE);
    }

    /**
     * @return array
     */
    public function getTillUsers()
    {
        return explode(",", (string)$this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_TILL_USERS));
    }

    /**
     * @return string
     */
    public function getReceiptHeader()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_CUSTOMISATION_RECEIPT_HEADER);
    }

    /**
     * @return string
     */
    public function getReceiptFooter()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_CUSTOMISATION_RECEIPT_FOOTER);
    }

    /**
     * @return string
     */
    public function getReceiptFooterQrLink()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_CUSTOMISATION_RECEIPT_FOOTER_QR_LINK);
    }

    /**
     * @return string
     */
    public function getPriceEditorBarcode()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_CUSTOMISATION_PRICE_EDITOR_BARCODE);
    }

    /**
     * @return string
     */
    public function getCustomProductBarcode()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_CUSTOMISATION_CUSTOM_PRODUCT_BARCODE);
    }

    /**
     * @return mixed
     */
    public function getPosStore()
    {
        try {
            $storeId = $this->getPosStoreId();
            return $this->storeManager->getStore($storeId);
        } catch(\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @return mixed
     */
    public function getRedirectStore()
    {
        try {
            $storeId = $this->getRedirectStoreId();
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
    public function currentlyOnPosStore()
    {
        return $this->storeManager->getStore()->getId() == $this->getPosStoreId();
    }

    /**
     * Check if an order is a POS order.
     * 
     * @param \Magento\Sales\Api\Data\OrderInterface
     * @return bool
     */
    public function isPosOrder($order)
    {
        if($order->getStoreId() == $this->getPosStoreId() && strpos($order->getPayment()->getMethodInstance()->getCode(), 'pos') !== false) {
            return true;
        }

        return false;
    }
}
