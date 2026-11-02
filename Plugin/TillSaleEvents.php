<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin;

use Zero1\OpenPos\Model\OrderManagement;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\Data\OrderInterface;

class TillSaleEvents
{
    const EVENT_TILL_SALE_PLACED = 'openpos_till_sale_placed';

    /**
     * @var OrderManagement
     */
    protected $orderManagement;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @param OrderManagement $orderManagement
     * @param EventManager $eventManager
     */
    public function __construct(
        OrderManagement $orderManagement,
        EventManager $eventManager
    ) {
        $this->orderManagement = $orderManagement;
        $this->eventManager = $eventManager;
    }

    /**
     * Dispatch openpos_till_sale_placed once a till sale has been placed and OpenPOS has processed it
     * (invoiced and payment recorded where applicable), so extensions can act on till sales only.
     *
     * @param OrderManagementInterface $orderManagement
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterPlace(OrderManagementInterface $orderManagement, OrderInterface $order): OrderInterface
    {
        if(!$this->orderManagement->isTillSale($order)) {
            return $order;
        }

        // The order has already been saved, so don't fail the request: that would leave the
        // till's basket active and a retry would place a duplicate order.
        try {
            $this->eventManager->dispatch(self::EVENT_TILL_SALE_PLACED, ['order' => $order]);
        } catch(\Throwable $e) {
            $this->orderManagement->reportProcessingFailure(
                $order,
                (string)__('OpenPOS could not finish processing this sale (e.g. updating stock). Please check the order.'),
                $e
            );
        }

        return $order;
    }
}
