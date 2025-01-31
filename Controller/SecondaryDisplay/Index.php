<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Controller\SecondaryDisplay;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Zero1\OpenPos\Helper\Data as OpenPosHelper;
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
     * @param PageFactory $pageFactory
     * @param ForwardFactory $forwardFactory
     * @param OpenPosHelper $openPosHelper
     */
    public function __construct(
        PageFactory $pageFactory,
        ForwardFactory $forwardFactory,
        OpenPosHelper $openPosHelper
    ) {
        $this->pageFactory = $pageFactory;
        $this->forwardFactory = $forwardFactory;
        $this->openPosHelper = $openPosHelper;
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

        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set('OpenPOS Secondary Display');

        return $page;
    }
}
