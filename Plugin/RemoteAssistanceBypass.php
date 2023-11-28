<?php

namespace Zero1\Pos\Plugin;

use Magento\LoginAsCustomerAssistance\Model\IsAssistanceEnabled;

class RemoteAssistanceBypass
{
    /**
     * 
     * THIS IS TEMPORARY
     * // TODO: add another action to customer edit page, just for logging into POS, where this setting can be ignored anyways
     * 
     */


    /**
     * @param IsAssistanceEnabled $isAssistanceEnabled
     * @param bool $result
     */
    public function afterExecute($isAssistanceEnabled, $result)
    {
        return true;
    }
}