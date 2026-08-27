<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Controller\Adminhtml\Redirect;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Zero1\OpenPos\Model\UrlProvider;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\TillHandoff;

class Till extends Action
{
    public const ADMIN_RESOURCE = 'Zero1_OpenPos::pos';

    /**
     * @var UrlProvider
     */
    protected $urlProvider;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var AuthSession
     */
    protected $authSession;

    /**
     * @var OpenPosConfiguration
     */
    protected $openPosConfiguration;

    /**
     * @var TillHandoff
     */
    protected $tillHandoff;

    /**
     * @param Context $context
     * @param UrlProvider $urlProvider
     * @param RedirectFactory $resultRedirectFactory
     * @param AuthSession $authSession
     * @param OpenPosConfiguration $openPosConfiguration
     * @param TillHandoff $tillHandoff
     */
    public function __construct(
        Context $context,
        UrlProvider $urlProvider,
        RedirectFactory $resultRedirectFactory,
        AuthSession $authSession,
        OpenPosConfiguration $openPosConfiguration,
        TillHandoff $tillHandoff
    ) {
        parent::__construct($context);
        $this->urlProvider = $urlProvider;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->authSession = $authSession;
        $this->openPosConfiguration = $openPosConfiguration;
        $this->tillHandoff = $tillHandoff;
    }

    /**
     * Redirect user to OpenPOS.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            $targetUrl = $this->getTargetUrl();

            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setUrl($targetUrl);

            return $resultRedirect;

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Failed to redirect to OpenPOS: ') . $e->getMessage());

            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('admin/dashboard/index');
        }
    }

    /**
     * Log the current admin user straight in where they are permitted to use a
     * till, otherwise send them to the till login form to sign in as someone else.
     *
     * @return string
     */
    protected function getTargetUrl(): string
    {
        $adminUser = $this->authSession->getUser();

        if($adminUser && in_array($adminUser->getId(), $this->openPosConfiguration->getTillUsers())) {
            return $this->urlProvider->getTillHandoffUrl($this->tillHandoff->generate($adminUser));
        }

        return $this->urlProvider->getTillUrl();
    }
}