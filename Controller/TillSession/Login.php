<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Controller\TillSession;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Zero1\OpenPos\Helper\Session as OpenPosSessionHelper;
use Magento\Framework\View\Result\Page;

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
     * @return Page
     */
    public function execute(): Page
    {
        $this->openPosSessionHelper->destroySession();
        
        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set('OpenPOS Login');

        return $page;
    }
}
