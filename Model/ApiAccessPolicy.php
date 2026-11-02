<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Model;

use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\TillSessionManagement;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Store\Model\StoreManagerInterface;

class ApiAccessPolicy
{
    /**
     * Callers Magento has already authenticated and checked against the ACL,
     * e.g. back-office integrations updating POS store view data.
     */
    const TRUSTED_USER_TYPES = [
        UserContextInterface::USER_TYPE_INTEGRATION,
        UserContextInterface::USER_TYPE_ADMIN
    ];

    /**
     * @var OpenPosConfiguration
     */
    protected $openPosConfiguration;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @var TillSessionManagement
     */
    protected $tillSessionManagement;

    /**
     * @param OpenPosConfiguration $openPosConfiguration
     * @param StoreManagerInterface $storeManager
     * @param UserContextInterface $userContext
     * @param TillSessionManagement $tillSessionManagement
     */
    public function __construct(
        OpenPosConfiguration $openPosConfiguration,
        StoreManagerInterface $storeManager,
        UserContextInterface $userContext,
        TillSessionManagement $tillSessionManagement
    ) {
        $this->openPosConfiguration = $openPosConfiguration;
        $this->storeManager = $storeManager;
        $this->userContext = $userContext;
        $this->tillSessionManagement = $tillSessionManagement;
    }

    /**
     * Check if the current API request (REST, SOAP or GraphQL) may act on the POS store.
     * Applies the same rule RestrictAccessObserver applies to the frontend: the POS store
     * is closed to everyone except an active till.
     *
     * @return bool
     */
    public function isAllowed(): bool
    {
        // Compared directly rather than via TillSessionManagement::currentlyOnPosStore(), so API
        // requests for other stores never load the till session classes, which start a PHP session.
        $posStoreId = $this->openPosConfiguration->getPosStoreId();
        if(!$this->openPosConfiguration->isEnabled()
            || $posStoreId === null
            || (int)$this->storeManager->getStore()->getId() !== $posStoreId
        ) {
            return true;
        }

        if(in_array((int)$this->userContext->getUserType(), self::TRUSTED_USER_TYPES, true)) {
            return true;
        }

        return $this->tillSessionManagement->isTillSessionActive();
    }
}
