<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin;

use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\OrderManagement;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Zero1\OpenPos\Model\PaymentMethod\Layaways;

class PaymentCreate
{
    /**
     * @var OpenPosConfiguration;
     */
    protected $openPosConfiguration;

    /**
     * @var OrderManagement
     */
    protected $orderManagement;

    /**
     * @param OpenPosConfiguration $openPosConfiguration
     * @param OrderManagement $orderManagement
     */
    public function __construct(
        OpenPosConfiguration $openPosConfiguration,
        OrderManagement $orderManagement
    ) {
        $this->openPosConfiguration = $openPosConfiguration;
        $this->orderManagement = $orderManagement;
    }

    /**
     * Create OpenPOS payment for orders that are not layaways.
     * 
     * @param OrderManagementInterface $orderManagement
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterPlace(OrderManagementInterface $orderManagement, OrderInterface $order): OrderInterface
    {
        // Orders placed on the POS store without a till (Magento admin, API) take no OpenPOS payment.
        if(!$this->orderManagement->isTillSale($order)) {
            return $order;
        }

        if($order->getPayment()->getMethod() === Layaways::PAYMENT_METHOD_CODE) {
            return $order;
        }

        // The order has already been saved, so don't fail the request: that would leave the
        // till's basket active and a retry would place a duplicate order.
        try {
            $this->orderManagement->makePayment($order, $order->getGrandTotal(), $order->getPayment()->getMethod(), $order->getPayment()->getMethod());
        } catch(\Throwable $e) {
            $this->orderManagement->reportProcessingFailure(
                $order,
                (string)__('OpenPOS could not record the payment for this order. Please check the order.'),
                $e
            );
        }

        return $order;
    }
}
