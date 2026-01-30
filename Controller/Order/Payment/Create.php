<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Controller\Order\Payment;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\TillSessionManagement;
use Magento\Sales\Api\OrderRepositoryInterface;
use Zero1\OpenPos\Model\OrderManagement;
use Zero1\OpenPos\Model\UrlProvider;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Controller\Result\Forward;

class Create implements HttpGetActionInterface
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
     * @var OrderManagement
     */
    protected $orderManagement;

    /**
     * @var UrlProvider
     */
    protected $urlProvider;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @param RequestInterface $request
     * @param PageFactory $pageFactory
     * @param ForwardFactory $forwardFactory
     * @param OpenPosConfiguration $openPosConfiguration
     * @param TillSessionManagement $tillSessionManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderManagement $orderManagement
     * @param UrlProvider $urlProvider
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        RequestInterface $request,
        PageFactory $pageFactory,
        ForwardFactory $forwardFactory,
        OpenPosConfiguration $openPosConfiguration,
        TillSessionManagement $tillSessionManagement,
        OrderRepositoryInterface $orderRepository,
        OrderManagement $orderManagement,
        UrlProvider $urlProvider,
        MessageManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->pageFactory = $pageFactory;
        $this->forwardFactory = $forwardFactory;
        $this->openPosConfiguration = $openPosConfiguration;
        $this->tillSessionManagement = $tillSessionManagement;
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
        $this->urlProvider = $urlProvider;
        $this->messageManager = $messageManager;
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
        $order = $this->orderRepository->get($orderId);

        if(!$this->orderManagement->canMakePayment($order)) {
            $this->messageManager->addErrorMessage(__('You cannot add a payment to this order.'));

            $forward = $this->forwardFactory->create();
            return $forward->forward($this->urlProvider->getOrderViewUrl($order));
        }

        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set('Create Payment');
        return $page;
    }
}
