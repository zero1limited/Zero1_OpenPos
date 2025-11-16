<?php

namespace Zero1\OpenPos\Model\PaymentMethod;

use Magento\Framework\App\ObjectManager;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\TillSessionManagement;

class Layaways extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * TODO: This method extends a deprecated class.
     * Use the 'Payment Provider Gateway': https://developer.adobe.com/commerce/php/development/payments-integrations/payment-gateway/
     */

    const PAYMENT_METHOD_CODE = 'openpos_layaways';

    /**
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_CODE;

    /**
     * @var OpenPosConfiguration
     */
    protected $openPosConfiguration;

    /**
     * @var TillSessionManagement
     */
    protected $tillSessionManagement;

    /**
     * @var DirectoryHelper
     */
    protected $directory;

    /**
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param OpenPosConfiguration $openPosConfiguration
     * @param TillSessionManagement $tillSessionManagement
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param DirectoryHelper $directory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        OpenPosConfiguration $openPosConfiguration,
        TillSessionManagement $tillSessionManagement,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        DirectoryHelper $directory = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection
        );

        $this->openPosConfiguration = $openPosConfiguration;
        $this->tillSessionManagement = $tillSessionManagement;
        $this->directory = $directory ?: ObjectManager::getInstance()->get(DirectoryHelper::class);
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        // Check if POS module is enabled
        if(!$this->openPosConfiguration->isEnabled()) {
            return false;
        }

        // Check if we are on POS store
        if(!$this->tillSessionManagement->currentlyOnPosStore()) {
            return false;
        }

        return parent::isAvailable($quote);
    }

    /**
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float $amount
     * @return $this
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        $order->setIsInProcess(true);
        $order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT)->setStatus('pending_payment');

        return $this;
    }
}
