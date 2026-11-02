<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin\Webapi;

use Zero1\OpenPos\Model\ApiAccessPolicy;
use Magento\Framework\Webapi\Authorization;

class RestrictPosStoreAccess
{
    /**
     * @var ApiAccessPolicy
     */
    protected $apiAccessPolicy;

    /**
     * @param ApiAccessPolicy $apiAccessPolicy
     */
    public function __construct(
        ApiAccessPolicy $apiAccessPolicy
    ) {
        $this->apiAccessPolicy = $apiAccessPolicy;
    }

    /**
     * Deny REST and SOAP access to the POS store without an active till session.
     * This is the check core runs for every synchronous and asynchronous REST request
     * and every SOAP request, so a denied caller gets core's standard 401 response.
     *
     * @param Authorization $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsAllowed(Authorization $subject, bool $result): bool
    {
        if(!$result) {
            return false;
        }

        return $this->apiAccessPolicy->isAllowed();
    }
}
