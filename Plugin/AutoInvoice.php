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
        if($this->orderManagement->isPosOrder($order)) {
            $this->orderManagement->invoiceOrder($order);
        }

        return $order;
    }
}
