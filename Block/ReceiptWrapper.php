<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Zero1\OpenPos\Helper\Data as PosHelper;
use Magento\Checkout\Model\Session as CheckoutSession;

class ReceiptWrapper extends Template
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
     * @param Context $context
     * @param PosHelper $posHelper
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Context $context,
        PosHelper $posHelper,
        CheckoutSession $checkoutSession,
        array $data = []
    ) {
        $this->posHelper = $posHelper;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml(): string
    {
        if(!$this->posHelper->isEnabled() || !$this->posHelper->currentlyOnPosStore()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Return OpenPOS order view URL
     * 
     * @return string
     */
    public function getOrderViewUrl(): string
    {
        $order = $this->checkoutSession->getLastRealOrder();
        return $this->posHelper->getOrderViewUrl($order);
    }
}
