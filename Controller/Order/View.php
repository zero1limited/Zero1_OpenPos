<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Controller\Order;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Zero1\OpenPos\Helper\Session as OpenPosSessionHelper;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Controller\Result\Forward;

/**
 * Work in progress
 */

class View implements HttpGetActionInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var ForwardFactory
     */
    protected $forwardFactory;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var OpenPosHelper
     */
    protected $openPosHelper;

    /**
     * @var OpenPosSessionHelper
     */
    protected $openPosSessionHelper;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @param RequestInterface $request
     * @param PageFactory $pageFactory
     * @param ForwardFactory $forwardFactory
     * @param RedirectInterface $redirect
     * @param OpenPosHelper $openPosHelper
     * @param OpenPosSessionHelper $openPosSessionHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param Registry $registry
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        RequestInterface $request,
        PageFactory $pageFactory,
        ForwardFactory $forwardFactory,
        RedirectInterface $redirect,
        OpenPosHelper $openPosHelper,
        OpenPosSessionHelper $openPosSessionHelper,
        OrderRepositoryInterface $orderRepository,
        Registry $registry,
        CheckoutSession $checkoutSession
    ) {
        $this->request = $request;
        $this->pageFactory = $pageFactory;
        $this->forwardFactory = $forwardFactory;
        $this->redirect = $redirect;
        $this->openPosHelper = $openPosHelper;
        $this->openPosSessionHelper = $openPosSessionHelper;
        $this->orderRepository = $orderRepository;
        $this->registry = $registry;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return Page|Forward
     */
    public function execute()
    {
        // Ensure no access to this controller with no till session
        if(!$this->openPosHelper->currentlyOnPosStore() || !$this->openPosSessionHelper->isTillSessionActive()) {
            $forward = $this->forwardFactory->create();
            return $forward->forward('noroute');
        }

        $orderId = (int)$this->request->getParam('id');
        if (!$orderId) {
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->forwardFactory->create();
            return $resultForward->forward('noroute');
        }
        $order = $this->loadOrder($orderId);

        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set(__('OpenPOS Order: %1', $order->getIncrementId()));

        // Add back button URL on actions block
        // @todo: fix this logic, not working as expected with no referrer
        // I have removed the block for now.
        // $block = $page->getLayout()->getBlock('openpos.order.view.actions');
        // if ($block) {
        //     $block->setRefererUrl($this->redirect->getRefererUrl());
        // }
        
        return $page;
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function loadOrder(int $orderId)
    {
        $order = $this->orderRepository->get($orderId);
        $this->registry->register('current_order', $order);

        $this->checkoutSession->setLastOrderId($order->getEntityId())->setLastRealOrderId($order->getIncrementId());

        return $order;
    }
}
