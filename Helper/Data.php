<?php
namespace Zero1\Pos\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    const CONFIG_PATH_GENERAL_ENABLE = 'zero1_pos/general/enable';
    const CONFIG_PATH_GENERAL_POS_STORE = 'zero1_pos/general/pos_store';
    const CONFIG_PATH_GENERAL_REDIRECT_STORE = 'zero1_pos/general/redirect_store';
    const CONFIG_PATH_GENERAL_WALKIN_CUSTOMER_EMAIL = 'zero1_pos/general/walkin_customer_email';

    const CONFIG_PATH_CUSTOMISATION_RECEIPT_HEADER = 'zero1_pos/customisation/receipt_header';
    const CONFIG_PATH_CUSTOMISATION_RECEIPT_FOOTER = 'zero1_pos/customisation/receipt_footer';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_ENABLE);
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
     * @return string
     */
    public function getWalkinCustomerEmail()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_WALKIN_CUSTOMER_EMAIL);
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
}
