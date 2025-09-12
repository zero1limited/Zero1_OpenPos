<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Controller\Order;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
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

class Success implements HttpGetActionInterface
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
        OpenPosHelper $openPosHelper,
        OpenPosSessionHelper $openPosSessionHelper,
        OrderRepositoryInterface $orderRepository,
        Registry $registry,
        CheckoutSession $checkoutSession
    ) {
        $this->request = $request;
        $this->pageFactory = $pageFactory;
        $this->forwardFactory = $forwardFactory;
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
            $resultForward = $this->forwardFactory->create();
            return $resultForward->forward('noroute');
        }
        $order = $this->orderRepository->get($orderId);
        $this->registry->register('current_order', $order);
        $this->checkoutSession->setLastOrderId($order->getEntityId())->setLastRealOrderId($order->getIncrementId());
        
        $page = $this->pageFactory->create();
        return $page;
    }
}
