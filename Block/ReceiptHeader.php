<?php

namespace Zero1\Pos\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Zero1\Pos\Helper\Data as PosHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Sales\Model\Order;

class ReceiptHeader extends Template
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
     * @param Context $context
     * @param PosHelper $posHelper
     * @param CheckoutSession $checkoutSession
     * @param PricingHelper $pricingHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        PosHelper $posHelper,
        CheckoutSession $checkoutSession,
        PricingHelper $pricingHelper,
        array $data = []
    ) {
        $this->posHelper = $posHelper;
        $this->checkoutSession = $checkoutSession;
        $this->pricingHelper = $pricingHelper;
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
