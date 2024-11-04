<?php
namespace Zero1\OpenPos\Model\ResourceModel\TillSession;

/**
 * Class Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Init
     */
    protected function _construct() // phpcs:ignore PSR2.Methods.MethodDeclaration
    {
        $this->_init(
            \Zero1\OpenPos\Model\TillSession::class,
            \Zero1\OpenPos\Model\ResourceModel\TillSession::class
        );
    }
}
