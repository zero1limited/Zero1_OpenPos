<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin\Adminhtml;

use Zero1\OpenPos\Model\OrderManagement;
use Magento\Sales\Api\OrderRepositoryInterface;
use Zero1\OpenPos\Model\UrlProvider;
use Magento\Sales\Block\Adminhtml\Order\View;

class AddSalesOrderButton
{
    /**
     * @var OrderManagement
     */
    protected $orderManagement;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var UrlProvider
     */
    protected $urlProvider;

    /**
     * @param OrderManagement $orderManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param UrlProvider $urlProvider
     */
    public function __construct(
        OrderManagement $orderManagement,
        OrderRepositoryInterface $orderRepository,
        UrlProvider $urlProvider
    ) {
        $this->orderManagement = $orderManagement;
        $this->orderRepository = $orderRepository;
        $this->urlProvider = $urlProvider;
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

            if($this->orderManagement->isPosOrder($order)) {
                $subject->addButton(
                    'view_in_openpos',
                    [
                        'label' => __('View in OpenPOS'),
                        'class' => __('primary'),
                        'id' => 'order-view-view-in-openpos',
                        'onclick' => 'window.open(\'' . $this->urlProvider->getOrderViewUrl($order) . '\', \'_blank\')'
                    ]
                );
            }
        } catch(\Exception $e) {}
    }
}
