<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\UrlProvider;
use Zero1\OpenPos\Model\OrderManagement;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class Notice extends Template
{
    /**
     * @var OpenPosConfiguration
     */
    protected $openPosConfiguration;

    /**
     * @var UrlProvider
     */
    protected $urlProvider;

    /**
     * @var OrderManagement
     */
    protected $orderManagement;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param Context $context
     * @param OpenPosConfiguration $openPosConfiguration
     * @param UrlProvider $urlProvider
     * @param OrderManagement $orderManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        OpenPosConfiguration $openPosConfiguration,
        UrlProvider $urlProvider,
        OrderManagement $orderManagement,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->openPosConfiguration = $openPosConfiguration;
        $this->urlProvider = $urlProvider;
        $this->orderManagement = $orderManagement;
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

        return $this->orderManagement->isPosOrder($order);
    }

    /**
     * Return the URL to view the order in OpenPOS.
     * 
     * @return string
     */
    public function getOrderViewUrl(): string
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($orderId);

        return $this->urlProvider->getOrderViewUrl($order);
    }
}