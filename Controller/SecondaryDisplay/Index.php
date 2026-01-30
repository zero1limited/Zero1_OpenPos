<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Controller\SecondaryDisplay;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\TillSessionManagement;
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
     * @var OpenPosConfiguration
     */
    protected $openPosConfiguration;

    /**
     * @var TillSessionManagement
     */
    protected $tillSessionManagement;

    /**
     * @param PageFactory $pageFactory
     * @param ForwardFactory $forwardFactory
     * @param OpenPosConfiguration $openPosConfiguration
     * @param TillSessionManagement $tillSessionManagement
     */
    public function __construct(
        PageFactory $pageFactory,
        ForwardFactory $forwardFactory,
        OpenPosConfiguration $openPosConfiguration,
        TillSessionManagement $tillSessionManagement
    ) {
        $this->pageFactory = $pageFactory;
        $this->forwardFactory = $forwardFactory;
        $this->openPosConfiguration = $openPosConfiguration;
        $this->tillSessionManagement = $tillSessionManagement;
    }

    /**
     * @return Page|Forward
     */
    public function execute()
    {
        // This is in development.
        $forward = $this->forwardFactory->create();
        return $forward->forward('noroute');

        if(!$this->tillSessionManagement->currentlyOnPosStore()) {
            $forward = $this->forwardFactory->create();
            return $forward->forward('noroute');
        }

        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set('OpenPOS Secondary Display');

        return $page;
    }
}
