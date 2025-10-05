<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Block\Order\View;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Zero1\OpenPos\Helper\Order as OpenPosOrderHelper;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

/**
 * WORK IN PROGRESS
 */

class Payments extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var OpenPosOrderHelper
     */
    protected $openPosOrderHelper;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param OpenPosOrderHelper $openposOrderHelper
     * @param PriceHelper $priceHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        OpenPosOrderHelper $openPosOrderHelper,
        PriceHelper $priceHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->openPosOrderHelper = $openPosOrderHelper;
        $this->priceHelper = $priceHelper;

        parent::__construct($context, $data);
    }

    public function getPayments(): array
    {
        $order = $this->getOrder();
        $payments = $this->openPosOrderHelper->getPaymentsForOrder($order);

        foreach($payments as $payment) {
            $payments[] = [
                'id' => $payment->getId(),
                'admin_user' => $payment->getAdminUser(),
                'amount' => $payment->getBasePaymentAmount(),
                'tax_amount' => $payment->getBaseTaxAmount(),
                'payment_method' => $payment->getPaymentMethod(),
                'created_at' => $payment->getCreatedAt()
            ];
        }

        return $payments;
    }

    public function isOrderPaid()
    {
        $order = $this->getOrder();

        return $this->openPosOrderHelper->isOrderPaid($order);
    }

    public function formatPrice($amount): string
    {
        return $this->priceHelper->currency($amount, true, false);
    }

    public function getOrderId()
    {
        return $this->getOrder()->getId();
    }

    protected function getOrder()
    {
        return $this->registry->registry('current_order');
    }

    public function canMakePayment(): bool
    {
        $order = $this->getOrder();

        if($order->getStatus() === 'pending' && $order->getPayment()->getMethod() === 'openpos_layaways') {
            return !$this->isOrderPaid();
        }

        return false;
    }
}
