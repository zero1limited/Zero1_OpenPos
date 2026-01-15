<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Block\Order\View;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Zero1\OpenPos\Model\OrderManagement;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\Data\OrderInterface;

class Payments extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var OrderManagement
     */
    protected $orderManagement;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param OrderManagement $orderManagement
     * @param PriceHelper $priceHelper
     * @param TimezoneInterface $timezone
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        OrderManagement $orderManagement,
        PriceHelper $priceHelper,
        TimezoneInterface $timezone,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->orderManagement = $orderManagement;
        $this->priceHelper = $priceHelper;
        $this->timezone = $timezone;

        parent::__construct($context, $data);
    }

    /**
     * Get an array of payments for the current order
     * 
     * @return array
     */
    public function getPayments(): array
    {
        $renderPayments = [];
        
        $order = $this->getOrder();
        $payments = $this->orderManagement->getPaymentsForOrder($order);

        foreach($payments as $payment) {
            $renderPayments[] = [
                'id' => $payment->getId(),
                'admin_user' => $payment->getAdminUser(),
                'amount' => $payment->getBasePaymentAmount(),
                'tax_amount' => $payment->getBaseTaxAmount(),
                'payment_method' => $payment->getPaymentMethod(),
                'created_at' => $this->timezone->formatDateTime($payment->getCreatedAt())
            ];
        }

        return $renderPayments;
    }

    /**
     * Retrieves the ID of the current order.
     *
     * @return int
     */
    public function getOrderId(): int
    {
        return (int)$this->getOrder()->getId();
    }

    /**
     * Retrieves the current order from the registry.
     *
     * @return OrderInterface
     */
    protected function getOrder(): OrderInterface
    {
        return $this->registry->registry('current_order');
    }

    /**
     * Formats a given amount into the store's currency string.
     *
     * @param float $amount
     * @return string
     */
    public function formatPrice($amount): string
    {
        return $this->priceHelper->currency($amount, true, false);
    }

    /**
     * Check if a payment can be made on an order.
     * Order has the be status 'pending' and use the layaway payment method.
     * 
     * @return bool
     */
    public function canMakePayment(): bool
    {
        $order = $this->getOrder();
        return $this->orderManagement->canMakePayment($order);
    }

    /**
     * Check if an order is fully paid.
     *
     * @return boolean
     */
    public function isOrderPaid()
    {
        $order = $this->getOrder();
        return $this->orderManagement->isOrderPaid($order);
    }
}
