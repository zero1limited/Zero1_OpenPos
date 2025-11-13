<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Controller\Order\Payment;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Zero1\OpenPos\Model\Session as OpenPosSession;
use Magento\Sales\Api\OrderRepositoryInterface;
use Zero1\OpenPos\Helper\Order as OpenPosOrderHelper;
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
     * @var OpenPosHelper
     */
    protected $openPosHelper;

    /**
     * @var OpenPosSession
     */
    protected $openPosSession;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OpenPosOrderHelper
     */
    protected $openPosOrderHelper;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @param RequestInterface $request
     * @param PageFactory $pageFactory
     * @param ForwardFactory $forwardFactory
     * @param OpenPosHelper $openPosHelper
     * @param OpenPosSession $openPosSession
     * @param OrderRepositoryInterface $orderRepository
     * @param OpenPosOrderHelper $openPosOrderHelper
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        RequestInterface $request,
        PageFactory $pageFactory,
        ForwardFactory $forwardFactory,
        OpenPosHelper $openPosHelper,
        OpenPosSession $openPosSession,
        OrderRepositoryInterface $orderRepository,
        OpenPosOrderHelper $openPosOrderHelper,
        MessageManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->pageFactory = $pageFactory;
        $this->forwardFactory = $forwardFactory;
        $this->openPosHelper = $openPosHelper;
        $this->openPosSession = $openPosSession;
        $this->orderRepository = $orderRepository;
        $this->openPosOrderHelper = $openPosOrderHelper;
        $this->messageManager = $messageManager;
    }

    /**
     * @return Page|Forward
     */
    public function execute()
    {
        // Ensure no access to this controller with no till session
        if(!$this->openPosHelper->currentlyOnPosStore() || !$this->openPosSession->isTillSessionActive()) {
            $forward = $this->forwardFactory->create();
            return $forward->forward('noroute');
        }

        $orderId = (int)$this->request->getParam('id');
        $order = $this->orderRepository->get($orderId);

        if(!$this->openPosOrderHelper->canMakePayment($order)) {
            $this->messageManager->addErrorMessage(__('You cannot add a payment to this order.'));

            $forward = $this->forwardFactory->create();
            return $forward->forward($this->openPosHelper->getOrderViewUrl($order));
        }

        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set('Create Payment');
        return $page;
    }
}
