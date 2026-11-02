<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin;

use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\OrderManagement;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\Data\OrderInterface;

class AutoInvoice
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
     * @param OrderManagementInterface $orderManagement
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterPlace(OrderManagementInterface $orderManagement, OrderInterface $order): OrderInterface
    {
        // Orders placed on the POS store without a till (Magento admin, API) are left to Magento's own invoicing.
        if(!$this->orderManagement->isTillSale($order)) {
            return $order;
        }

        // The order has already been saved, so don't fail the request: that would leave the
        // till's basket active and a retry would place a duplicate order.
        try {
            $this->orderManagement->invoiceOrder($order);
        } catch(\Throwable $e) {
            $this->orderManagement->reportProcessingFailure(
                $order,
                (string)__('OpenPOS could not invoice this order automatically. Please check the order and invoice it manually.'),
                $e
            );
        }

        return $order;
    }
}
