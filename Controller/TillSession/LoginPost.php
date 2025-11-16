<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Controller\TillSession;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Backend\Model\Auth;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\TillSessionManagement;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Phrase;

class LoginPost extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * @var Auth
     */
    protected $auth;

    /**
     * @var OpenPosConfiguration
     */
    protected $openPosConfiguration;

    /**
     * @var TillSessionManagement
     */
    protected $tillSessionManagement;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @param Context $context
     * @param Auth $auth
     * @param OpenPosConfiguration $openPosConfiguration
     * @param TillSessionManagement $tillSessionManagement
     * @param CustomerSession $customerSession
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        Context $context,
        Auth $auth,
        OpenPosConfiguration $openPosConfiguration,
        TillSessionManagement $tillSessionManagement,
        CustomerSession $customerSession,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->auth = $auth;
        $this->openPosConfiguration = $openPosConfiguration;
        $this->tillSessionManagement = $tillSessionManagement;
        $this->customerSession = $customerSession;
        $this->dataObjectFactory = $dataObjectFactory;
        parent::__construct($context);
    }

    /**
     * Login to OpenPOS using provided admin credentials
     * 
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if(!$this->tillSessionManagement->currentlyOnPosStore()) {
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }

        if($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    // Attempt admin login with passed credentials
                    try {
                        $this->auth->login($login['username'], $login['password']);
                    } catch (\Exception $e) {
                        // Any exception here I'm treating as failed login
                        // Avoid revealing password through exception
                        $this->messageManager->addErrorMessage(
                            __('Incorrect username or password.')
                        );
                        throw new \Exception();
                    }

                    if(!$this->auth->isLoggedIn()) {
                        $this->messageManager->addErrorMessage(
                            __('Incorrect username or password.')
                        );
                        throw new \Exception();
                    }

                    // Check 2FA
                    if($this->openPosConfiguration->isTfaEnabled()) {
                        // Have to use ObjectManager here, core 2FA has no enabled / disabled config
                        // so some users choose to disable the extension entirely...
                        // TODO: add support for more providers - maybe find a better solution to below
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        try {
                            $google = $objectManager->get('Magento\TwoFactorAuth\Model\Provider\Engine\Google');
                        } catch(\Throwable $e) {
                            $this->messageManager->addErrorMessage(
                                __('2FA cannot be used as the core Magento 2FA module is not enabled.')
                            );
                            throw new \Exception();
                        }

                        $user = $this->auth->getUser();
                        $request = $this->dataObjectFactory->create(['data' => [
                            'tfa_code' => $login['tfa_code']
                        ]]);
                        if (!$google->verify($user, $request)) {
                            $this->messageManager->addErrorMessage(
                                __('Incorrect 2FA code.')
                            );
                            throw new \Exception();
                        }
                    }

                    // Admin credentials correct
                    $adminUser = $this->auth->getUser();
                    $this->auth->logout();

                    try {
                        $this->tillSessionManagement->startTillSession($adminUser);
                    } catch(\Exception $e) {
                        $this->customerSession->logout();
                        $this->messageManager->addErrorMessage(
                            __($e->getMessage())
                        );
                        throw new \Exception();
                    }

                    $resultRedirect->setUrl($this->_redirect->success('/'));
                    return $resultRedirect;
                } catch (\Exception $e) {
                    $resultRedirect->setUrl($this->_redirect->error('openpos/tillsession/login'));
                    return $resultRedirect;
                }
            } else {
                $this->messageManager->addErrorMessage(__('A username and a password are required to login to this till.'));
                $resultRedirect->setUrl($this->_redirect->error('openpos/tillsession/login'));
                return $resultRedirect;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('openpos/tillsession/login');

        return new InvalidRequestException(
            $resultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return null;
    }
}
