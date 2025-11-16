<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Theme\Block\Html\Header\Logo as LogoBlock;
use Magento\Sales\Model\Order;

class ReceiptPrint extends Template
{
    /**
     * @var OpenPosConfiguration;
     */
    protected $openPosConfiguration;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var PricingHelper
     */
    protected $pricingHelper;

    /**
     * @var LogoBlock
     */
    protected $logoBlock;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @param Context $context
     * @param OpenPosConfiguration $openPosConfiguration
     * @param CheckoutSession $checkoutSession
     * @param PricingHelper $pricingHelper
     * @param LogoBlock $logoBlock
     * @param array $data
     */
    public function __construct(
        Context $context,
        OpenPosConfiguration $openPosConfiguration,
        CheckoutSession $checkoutSession,
        PricingHelper $pricingHelper,
        LogoBlock $logoBlock,
        array $data = []
    ) {
        $this->openPosConfiguration = $openPosConfiguration;
        $this->checkoutSession = $checkoutSession;
        $this->pricingHelper = $pricingHelper;
        $this->logoBlock = $logoBlock;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getReceiptHeaderContents(): string
    {
        return $this->openPosConfiguration->getReceiptHeader() ?? '';
    }

    /**
     * @return string
     */
    public function getReceiptFooterContents(): string
    {
        return $this->openPosConfiguration->getReceiptFooter() ?? '';
    }

    /**
     * Get logo URL
     *
     * @return string
     */
    public function getLogoUrl(): string
    {
        return $this->logoBlock->getLogoSrc();
    }

    /**
     * @return string
     */
    public function getOrderIncrementId(): string
    {
        return $this->getOrder()->getIncrementId();
    }

    /**
     * @return string
     */
    public function getOrderDate(): string
    {
        return $this->getOrder()->getCreatedAt();
    }

    /**
     * @return string
     */
    public function getOrderPayment(): string
    {
        $payment = $this->getOrder()->getPayment()->getMethod();

        switch ($payment) {
            case 'openpos_pay_card':
                return "Credit/Debit Card";
                break;
            case 'openpos_pay_cash':
                return "Cash";
                break;
            default:
                return $payment;
                break;
        }
  
    }

    /**
     * @return string
     */
    public function getOrderGrandTotal(): string
    {
        return $this->pricingHelper->currency($this->getOrder()->getGrandTotal(), true, false);
    }

    /**
     * @return array
     */
    public function getOrderItems(): array
    {
        return $this->getOrder()->getAllVisibleItems();
    }

    /**
     * @return Order
     */
    protected function getOrder(): Order
    {
        if(!$this->order) {
            $this->order = $this->checkoutSession->getLastRealOrder();
        }

        return $this->order;
    }
}
