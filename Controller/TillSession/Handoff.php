<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Controller\TillSession;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Zero1\OpenPos\Model\TillHandoff;
use Zero1\OpenPos\Model\TillSessionManagement;

class Handoff implements HttpGetActionInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ForwardFactory
     */
    protected $forwardFactory;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var TillHandoff
     */
    protected $tillHandoff;

    /**
     * @var TillSessionManagement
     */
    protected $tillSessionManagement;

    /**
     * @param RequestInterface $request
     * @param ForwardFactory $forwardFactory
     * @param RedirectFactory $redirectFactory
     * @param MessageManager $messageManager
     * @param TillHandoff $tillHandoff
     * @param TillSessionManagement $tillSessionManagement
     */
    public function __construct(
        RequestInterface $request,
        ForwardFactory $forwardFactory,
        RedirectFactory $redirectFactory,
        MessageManager $messageManager,
        TillHandoff $tillHandoff,
        TillSessionManagement $tillSessionManagement
    ) {
        $this->request = $request;
        $this->forwardFactory = $forwardFactory;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->tillHandoff = $tillHandoff;
        $this->tillSessionManagement = $tillSessionManagement;
    }

    /**
     * Start a till session for an admin user already authenticated in the
     * Magento admin, identified by a handoff token.
     *
     * @return Redirect|Forward
     */
    public function execute()
    {
        if(!$this->tillSessionManagement->currentlyOnPosStore()) {
            $forward = $this->forwardFactory->create();
            return $forward->forward('noroute');
        }

        $resultRedirect = $this->redirectFactory->create();

        try {
            // Check token is valid
            $adminUser = $this->tillHandoff->resolveAdminUser((string)$this->request->getParam('token'));

            // Start a new till session if above is okay
            $this->tillSessionManagement->destroySession();
            $this->tillSessionManagement->startTillSession($adminUser);
        } catch(LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath('openpos/tillsession/login');
            return $resultRedirect;
        }

        $resultRedirect->setPath('/');
        return $resultRedirect;
    }
}
