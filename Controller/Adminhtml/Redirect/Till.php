<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Controller\Adminhtml\Redirect;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Magento\Framework\Controller\Result\RedirectFactory;

class Till extends Action
{
    public const ADMIN_RESOURCE = 'Zero1_OpenPos::pos';

    /**
     * @var OpenPosHelper
     */
    protected $openPosHelper;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @param Context $context
     * @param OpenPosHelper $openPosHelper
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Context $context,
        OpenPosHelper $openPosHelper,
        RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context);
        $this->openPosHelper = $openPosHelper;
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
            $targetUrl = $this->openPosHelper->getTillUrl();

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