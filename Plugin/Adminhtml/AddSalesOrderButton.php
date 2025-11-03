<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin\Adminhtml;

use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Block\Adminhtml\Order\View;

class AddSalesOrderButton
{
    /**
     * @var OpenPosHelper
     */
    protected $openPosHelper;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param OpenPosHelper $openPosHelper
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        OpenPosHelper $openPosHelper,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->openPosHelper = $openPosHelper;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Add button to sales order view for viewing the order within OpenPOS.
     * Fail silently.
     *
     * @param View $subject
     * @return void
     */
    public function beforeSetLayout(View $subject): void
    {
        try {
            $orderId = $subject->getOrderId();
            $order = $this->orderRepository->get($orderId);

            if($this->openPosHelper->isPosOrder($order)) {
                $subject->addButton(
                    'view_in_openpos',
                    [
                        'label' => __('View in OpenPOS'),
                        'class' => __('primary'),
                        'id' => 'order-view-view-in-openpos',
                        'onclick' => 'window.open(\'' . $this->openPosHelper->getOrderViewUrl($order) . '\', \'_blank\')'
                    ]
                );
            }
        } catch(\Exception $e) {}
    }
}
