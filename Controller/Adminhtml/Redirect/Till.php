<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Controller\Adminhtml\Redirect;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Zero1\OpenPos\Model\UrlProvider;
use Magento\Framework\Controller\Result\RedirectFactory;

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
     * @param Context $context
     * @param UrlProvider $urlProvider
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Context $context,
        UrlProvider $urlProvider,
        RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context);
        $this->urlProvider = $urlProvider;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Redirect user to OpenPOS.
     * 
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            $targetUrl = $this->urlProvider->getTillUrl();

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
}