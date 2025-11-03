<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Controller\Order;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Zero1\OpenPos\Helper\Session as OpenPosSessionHelper;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Controller\Result\Forward;

class Index implements HttpGetActionInterface
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
     * @var OpenPosSessionHelper
     */
    protected $openPosSessionHelper;

    /**
     * @param PageFactory $pageFactory
     * @param ForwardFactory $forwardFactory
     * @param OpenPosHelper $openPosHelper
     * @param OpenPosSessionHelper $openPosSessionHelper
     */
    public function __construct(
        PageFactory $pageFactory,
        ForwardFactory $forwardFactory,
        OpenPosHelper $openPosHelper,
        OpenPosSessionHelper $openPosSessionHelper
    ) {
        $this->pageFactory = $pageFactory;
        $this->forwardFactory = $forwardFactory;
        $this->openPosHelper = $openPosHelper;
        $this->openPosSessionHelper = $openPosSessionHelper;
    }

    /**
     * @return Page|Forward
     */
    public function execute()
    {
        // Ensure no access to this controller with no till session
        if(!$this->openPosHelper->currentlyOnPosStore() || !$this->openPosSessionHelper->isTillSessionActive()) {
            $forward = $this->forwardFactory->create();
            return $forward->forward('noroute');
        }

        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set(__('Orders'));
        return $page;
    }
}
