<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin\GraphQl;

use Zero1\OpenPos\Model\ApiAccessPolicy;
use Magento\Framework\GraphQl\Query\QueryProcessor;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;

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
     * Deny GraphQL access to the POS store without an active till session.
     * Hooked here rather than as a GraphQL request validator: core runs those validators
     * once before the Store header has been applied, so the store isn't known yet.
     *
     * @param QueryProcessor $subject
     * @return void
     * @throws GraphQlAuthorizationException
     */
    public function beforeProcess(QueryProcessor $subject): void
    {
        if(!$this->apiAccessPolicy->isAllowed()) {
            throw new GraphQlAuthorizationException(__('The current user isn\'t authorized to access this store.'));
        }
    }
}
