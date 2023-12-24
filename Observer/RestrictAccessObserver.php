<?php
namespace Zero1\OpenPos\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ActionFlag;
use Zero1\OpenPos\Helper\Data as PosHelper;

class RestrictAccessObserver implements ObserverInterface
{
    const LOGIN_ACTION_NAME = 'loginascustomer_login_index';

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var ActionFlag
     */
    protected $actionFlag;

    /**
     * @var PosHelper
     */
    protected $posHelper;

    /**
     * @param CustomerSession $customerSession
     * @param StoreManagerInterface $storeManager
     * @param RedirectInterface $redirect
     * @param ActionFlag $actionFlag
     * @param PosHelper $posHelper
     */
    public function __construct(
        Session $customerSession,
        StoreManagerInterface $storeManager,
        RedirectInterface $redirect,
        ActionFlag $actionFlag,
        PosHelper $posHelper
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->redirect = $redirect;
        $this->actionFlag = $actionFlag;
        $this->posHelper = $posHelper;
    }

    public function execute(Observer $observer)
    {
        // Check if module is enabled
        if(!$this->posHelper->isEnabled()) {
            return;
        }

        // Check a POS store is set, and check if we are currently on it
        if($this->posHelper->getPosStore() && !$this->posHelper->currentlyOnPosStore()) {
            return;
        }

        // Check we aren't currently logging in from admin
        if($observer->getRequest()->getFullActionName() === self::LOGIN_ACTION_NAME) {
            return;
        }

        // Check we aren't logged in
        if (!$this->customerSession->isLoggedIn()) {
            $controller = $observer->getControllerAction();
            $redirectStore = $this->posHelper->getRedirectStore();

            // If there isn't a valid store to redirect to, throw an exception.
            if(!$redirectStore) {
                throw new \Exception('Customer session is required for POS system.');
            }

            $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);

            // TODO: make this better, I think this is sometimes why the login from MAP doesnt work
            // Not all controllers we can call getResponse on
            // Quick fix for now, this does leave a hole in security
            // Callum
            if(is_callable([$controller, 'getResponse'])) {
                $this->redirect->redirect($controller->getResponse(), $redirectStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB));
            } else {
                return;
            }
        }
    }
}
