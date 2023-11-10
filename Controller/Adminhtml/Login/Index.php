<?php

namespace Zero1\Pos\Controller\Adminhtml\Login;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     *
     * THIS CLASS IS FOR DEBUGING ROUTES ETC
     *
     * // TODO: remove.
     */

	/**
	 * @var PageFactory
	 */
	protected $resultPageFactory;

	/**
	 * @param Context $context
	 * @param PageFactory $resultPageFactory
	 */
	public function __construct(
		Context $context,
		PageFactory $resultPageFactory
	)
	{
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
	}

	public function execute()
	{
        die('here');

		return $resultPage;
	}
}
