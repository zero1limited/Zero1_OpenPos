<?php

namespace Zero1\OpenPos\Controller\TillSession;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Zero1\OpenPos\Helper\Session as OpenPosSessionHelper;

class Login implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var OpenPosSessionHelper
     */
    protected $openPosSessionHelper;

    /**
     * @param PageFactory $pageFactory
     * @param OpenPosSessionHelper $openPosSessionHelper
     */
    public function __construct(
        PageFactory $pageFactory,
        OpenPosSessionHelper $openPosSessionHelper
    ) {
        $this->pageFactory = $pageFactory;
        $this->openPosSessionHelper = $openPosSessionHelper;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->openPosSessionHelper->destroySession();
        
        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set('OpenPOS Login');

        return $page;
    }
}
