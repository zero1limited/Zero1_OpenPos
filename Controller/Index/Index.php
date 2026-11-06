<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Zero1\OpenPos\Model\TillSessionManagement;

/**
 * Till home page. The POS website's home page ("/") is routed here, see Zero1\OpenPos\Model\TillHomepage.
 */
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
     * @var TillSessionManagement
     */
    protected $tillSessionManagement;

    /**
     * @param PageFactory $pageFactory
     * @param ForwardFactory $forwardFactory
     * @param TillSessionManagement $tillSessionManagement
     */
    public function __construct(
        PageFactory $pageFactory,
        ForwardFactory $forwardFactory,
        TillSessionManagement $tillSessionManagement
    ) {
        $this->pageFactory = $pageFactory;
        $this->forwardFactory = $forwardFactory;
        $this->tillSessionManagement = $tillSessionManagement;
    }

    /**
     * @return Page|Forward
     */
    public function execute()
    {
        // Ensure no access to this controller with no till session, e.g. on a non-POS store
        if (!$this->tillSessionManagement->isTillSessionActive()) {
            $forward = $this->forwardFactory->create();
            return $forward->forward('noroute');
        }

        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set(__('OpenPOS'));
        return $page;
    }
}
