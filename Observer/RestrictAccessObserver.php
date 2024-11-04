<?php
namespace Zero1\OpenPos\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ActionFlag;
use Zero1\OpenPos\Helper\Data as PosHelper;
use Zero1\OpenPos\Helper\Session as OpenPosSessionHelper;

class RestrictAccessObserver implements ObserverInterface
{
    const CONTROLLER_ACTION_WHITELIST = ['openpos_tillsession_login', 'openpos_tillsession_loginpost', 'openpos_secondarydisplay_index'];

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
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var ActionFlag
     */
    protected $actionFlag;

    /**
     * @var PosHelper
     */
    protected $posHelper;

    protected $openPosSessionHelper;

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
        ResponseInterface $response,
        ActionFlag $actionFlag,
        PosHelper $posHelper,
        OpenPosSessionHelper $openPosSessionHelper
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->redirect = $redirect;
        $this->response = $response;
        $this->actionFlag = $actionFlag;
        $this->posHelper = $posHelper;
        $this->openPosSessionHelper = $openPosSessionHelper;
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

        // Check we aren't currently logging in
        if(in_array($observer->getRequest()->getFullActionName(), self::CONTROLLER_ACTION_WHITELIST)) {
            return;
        }


        // die('sss: '.$this->openPosSessionHelper->getTillSessionId());

        // Check till session exists
        if($this->openPosSessionHelper->getTillSession() !== null) {
            return;
        }

        // Redirect
        $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
        $this->redirect->redirect($this->response, 'openpos/tillsession/login');      
    }
}
