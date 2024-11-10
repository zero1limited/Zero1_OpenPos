<?php

namespace Zero1\OpenPos\Model\ResourceModel;

class TillSession extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Init
     */
    protected function _construct() // phpcs:ignore PSR2.Methods.MethodDeclaration
    {
        $this->_init('openpos_till_session', 'session_id');
    }
}
