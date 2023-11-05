<?php
namespace Zero1\HyvaPos\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\Message\ManagerInterface;

class RestrictAccessObserver implements ObserverInterface
{
    protected $adminSession;
    protected $redirect;
    protected $actionFlag;
    protected $messageManager;

    public function __construct(
        AdminSession $adminSession,
        RedirectInterface $redirect,
        ActionFlag $actionFlag,
        ManagerInterface $messageManager
    ) {
        $this->adminSession = $adminSession;
        $this->redirect = $redirect;
        $this->actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
    }

    public function execute(Observer $observer)
    {
        if (!$this->adminSession->isLoggedIn()) {
            $controller = $observer->getControllerAction();
            $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
            $this->messageManager->addError('You must have an active admin session to access this page.');
            $this->redirect->redirect($controller->getResponse(), 'adminhtml');
        }
    }
}
