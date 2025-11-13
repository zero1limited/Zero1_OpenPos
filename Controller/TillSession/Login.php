<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Controller\TillSession;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Zero1\OpenPos\Model\Session as OpenPosSession;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Controller\Result\Forward;

class Login implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var ForwardFactory
     */
    protected $forwardFactory;

    /**
     * @var OpenPosHelper
     */
    protected $openPosHelper;

    /**
     * @var OpenPosSession
     */
    protected $openPosSession;

    /**
     * @param PageFactory $pageFactory
     * @param ForwardFactory $forwardFactory
     * @param OpenPosHelper $openPosHelper
     * @param OpenPosSession $openPosSession
     */
    public function __construct(
        PageFactory $pageFactory,
        ForwardFactory $forwardFactory,
        OpenPosHelper $openPosHelper,
        OpenPosSession $openPosSession
    ) {
        $this->pageFactory = $pageFactory;
        $this->forwardFactory = $forwardFactory;
        $this->openPosHelper = $openPosHelper;
        $this->openPosSession = $openPosSession;
    }

    /**
     * @return Page|Forward
     */
    public function execute()
    {
        if(!$this->openPosHelper->currentlyOnPosStore()) {
            $forward = $this->forwardFactory->create();
            return $forward->forward('noroute');
        }

        $this->openPosSession->destroySession();
        
        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set('OpenPOS Login');

        return $page;
    }
}
