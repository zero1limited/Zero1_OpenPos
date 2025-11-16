<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Controller\Order;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\TillSessionManagement;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Controller\Result\Forward;

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
     * @var OpenPosConfiguration
     */
    protected $openPosConfiguration;

    /**
     * @var TillSessionManagement
     */
    protected $tillSessionManagement;

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
     * @param OpenPosConfiguration $openPosConfiguration
     * @param TillSessionManagement $tillSessionManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param Registry $registry
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        RequestInterface $request,
        PageFactory $pageFactory,
        ForwardFactory $forwardFactory,
        OpenPosConfiguration $openPosConfiguration,
        TillSessionManagement $tillSessionManagement,
        OrderRepositoryInterface $orderRepository,
        Registry $registry,
        CheckoutSession $checkoutSession
    ) {
        $this->request = $request;
        $this->pageFactory = $pageFactory;
        $this->forwardFactory = $forwardFactory;
        $this->openPosConfiguration = $openPosConfiguration;
        $this->tillSessionManagement = $tillSessionManagement;
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
        if(!$this->tillSessionManagement->isTillSessionActive()) {
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
