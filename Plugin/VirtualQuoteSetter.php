<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin;

use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\TillSessionManagement;
use Magento\Quote\Model\Quote;

class VirtualQuoteSetter
{
    /**
     * @var OpenPosConfiguration;
     */
    protected $openPosConfiguration;

    /**
     * @var TillSessionManagement
     */
    protected $tillSessionManagement;

    /**
     * @param OpenPosConfiguration $openPosConfiguration
     * @param TillSessionManagement $tillSessionManagement
     */
    public function __construct(
        OpenPosConfiguration $openPosConfiguration,
        TillSessionManagement $tillSessionManagement
    ) {
        $this->openPosConfiguration = $openPosConfiguration;
        $this->tillSessionManagement = $tillSessionManagement;
    }

    /**
     * @param Quote $quote
     * @param bool $result
     * @return bool
     */
    public function afterIsVirtual(Quote $quote, bool $result): bool
    {
        if($this->tillSessionManagement->currentlyOnPosStore() || $quote->getStoreId() === $this->openPosConfiguration->getPosStoreId()) {
            return true;
        }

        return $result;
    }
}
