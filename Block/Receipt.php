<?php

namespace Zero1\OpenPos\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Zero1\OpenPos\Helper\Data as PosHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Sales\Model\Order;
use Magento\Theme\Block\Html\Header\Logo as LogoBlock;
use chillerlan\QRCode\QRCode;

class Receipt extends Template
{
    /**
     * @var PosHelper
     */
    protected $posHelper;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var PricingHelper
     */
    protected $pricingHelper;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var LogoBlock
     */
    protected $logoBlock;

    /**
     * @var QRCode
     */
    protected $qrCode;

    /**
     * @param Context $context
     * @param PosHelper $posHelper
     * @param CheckoutSession $checkoutSession
     * @param PricingHelper $pricingHelper
     * @param LogoBlock $logoBlock
     * @param QRCode $qrCode
     * @param array $data
     */
    public function __construct(
        Context $context,
        PosHelper $posHelper,
        CheckoutSession $checkoutSession,
        PricingHelper $pricingHelper,
        LogoBlock $logoBlock,
        QRCode $qrCode,
        array $data = []
    ) {
        $this->posHelper = $posHelper;
        $this->checkoutSession = $checkoutSession;
        $this->pricingHelper = $pricingHelper;
        $this->logoBlock = $logoBlock;
        $this->qrCode = $qrCode;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getReceiptHeaderContents()
    {
        return $this->posHelper->getReceiptHeader();
    }

    /**
     * @return string
     */
    public function getReceiptFooterContents()
    {
        return $this->posHelper->getReceiptFooter();
    }

    /**
     * @return string
     */
    public function getReceiptFooterQrLinkImgSrc()
    {
        if(!$this->posHelper->getReceiptFooterQrLink()) {
            return '';
        }

        return $this->qrCode->render($this->posHelper->getReceiptFooterQrLink().'?orderId='.$this->getOrderIncrementId());
    }

    /**
     * Get logo URL
     *
     * @return string
     */
    public function getLogoUrl()
    {
        return $this->logoBlock->getLogoSrc();
    }

    /**
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();
    }

    /**
     * @return string
     */
    public function getOrderDate()
    {
        return $this->getOrder()->getCreatedAt();
    }

    /**
     * @return string
     */
    public function getOrderPayment()
    {
        $payment = $this->getOrder()->getPayment()->getMethod();

        switch ($payment) {
            case 'zero1_pos_pay_card':
                return "Credit/Debit Card";
                break;
            case 'zero1_pos_pay_cash':
                return "Cash";
                break;
            default:
                return "";
                break;
        }
  
    }

    /**
     * @return float|string
     */
    public function getOrderGrandTotal()
    {
        return $this->pricingHelper->currency($this->getOrder()->getGrandTotal(), true, false);
    }

    /**
     * @return array
     */
    public function getOrderItems()
    {
        return $this->getOrder()->getAllVisibleItems();
    }

    /**
     * @return Order
     */
    protected function getOrder()
    {
        if(!$this->order) {
            $this->order = $this->checkoutSession->getLastRealOrder();
        }

        return $this->order;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if(!$this->posHelper->isEnabled() || !$this->posHelper->currentlyOnPosStore()) {
            return '';
        }

        return parent::_toHtml();
    }
}
