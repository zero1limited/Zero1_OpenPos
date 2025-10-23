<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Magento\Sales\Api\OrderRepositoryInterface;

class Notice extends Template
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
     * @param Context $context
     * @param OpenPosHelper $openPosHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        OpenPosHelper $openPosHelper,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->openPosHelper = $openPosHelper;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $data);
    }

    /**
     * Only show template contents on OpenPOS orders.
     * 
     * @return bool
     */
    public function shouldShow(): bool
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($orderId);

        return $this->openPosHelper->isPosOrder($order);
    }

    /**
     * Return the URL to view the order in OpenPOS.
     * 
     * @return string
     */
    public function getOrderViewUrl(): string
    {
        // TODO this add admin path so will 404
        $orderId = (int)$this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($orderId);

        return $this->openPosHelper->getOrderViewUrl($order);
    }
}