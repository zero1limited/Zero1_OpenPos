<?php

namespace Zero1\Pos\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_PATH_GENERAL_ENABLE = 'zero1_pos/general/enable';
    const CONFIG_PATH_GENERAL_POS_STORE = 'zero1_pos/general/pos_store';
    const CONFIG_PATH_GENERAL_REDIRECT_STORE = 'zero1_pos/general/redirect_store';

    const CONFIG_PATH_CUSTOMISATION_RECEIPT_HEADER = 'zero1_pos/customisation/receipt_header';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig
    ){
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
    public function getPosStore()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_POS_STORE);
    }

    /**
     * @return int
     */
    public function getRedirectStore()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_REDIRECT_STORE);
    }

    /**
     * @return string
     */
    public function getReceiptHeader()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_CUSTOMISATION_RECEIPT_HEADER);
    }
}
