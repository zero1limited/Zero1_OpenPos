<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ActionFlag;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\TillSessionManagement;
use Magento\Framework\View\DesignInterface;

class RestrictAccessObserver implements ObserverInterface
{
    const CONTROLLER_ACTION_WHITELIST = ['openpos_tillsession_login', 'openpos_tillsession_loginpost'];

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
     * @var OpenPosConfiguration;
     */
    protected $openPosConfiguration;

    /**
     * @var TillSessionManagement
     */
    protected $tillSessionManagement;

    /**
     * @var DesignInterface
     */
    protected $design;

    /**
     * @param RedirectInterface $redirect
     * @param ActionFlag $actionFlag
     * @param OpenPosConfiguration $openPosConfiguration
     * @param TillSessionManagement $tillSessionManagement
     * @param DesignInterface $design
     */
    public function __construct(
        RedirectInterface $redirect,
        ResponseInterface $response,
        ActionFlag $actionFlag,
        OpenPosConfiguration $openPosConfiguration,
        TillSessionManagement $tillSessionManagement,
        DesignInterface $design
    ) {
        $this->redirect = $redirect;
        $this->response = $response;
        $this->actionFlag = $actionFlag;
        $this->openPosConfiguration = $openPosConfiguration;
        $this->tillSessionManagement = $tillSessionManagement;
        $this->design = $design;
    }

    /**
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $this->detectThemeUsageOnNonPosStore();

        // Check if module is enabled
        if(!$this->openPosConfiguration->isEnabled()) {
            return;
        }

        // Check a POS store is set, and check if we are currently on it
        if($this->openPosConfiguration->getPosStore() && !$this->tillSessionManagement->currentlyOnPosStore()) {
            return;
        }

        // Check we aren't currently logging in
        if(in_array($observer->getRequest()->getFullActionName(), self::CONTROLLER_ACTION_WHITELIST)) {
            return;
        }

        // Check till session exists
        if($this->tillSessionManagement->isTillSessionActive() === true) {
            return;
        }

        // Redirect
        $this->tillSessionManagement->destroySession();
        $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
        $this->redirect->redirect($this->response, 'openpos/tillsession/login');      
    }

    /**
     * Ensure an OpenPOS based theme is not being ran on a non-POS store.
     * If so, kill the process to protect against unauthorised access.
     * This check will be performed even if the module is disabled within the OpenPOS configuration.
     * 
     * @return void
     */
    protected function detectThemeUsageOnNonPosStore(): void
    {
        // Currently on a POS store, authentication in place.
        if($this->tillSessionManagement->currentlyOnPosStore()) {
            return;
        }

        $theme = $this->design->getDesignTheme();
        if(strpos($theme->getThemePath(), 'openpos') !== false) {
            die(__('OpenPOS theme in use on a non-POS store. Please check OpenPOS configuration.')); // TODO: improve
        }

        foreach($this->design->getDesignTheme()->getInheritedThemes() as $inheritedTheme) {
            if(strpos($inheritedTheme->getThemePath(), 'openpos') !== false) {
                die(__('OpenPOS theme in use on a non-POS store. Please check OpenPOS configuration.')); // TODO: improve
            }
        }
    }
}
