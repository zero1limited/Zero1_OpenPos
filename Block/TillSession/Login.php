<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Block\TillSession;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;

class Login extends Template
{
    /**
     * @var OpenPosConfiguration
     */
    protected $openPosConfiguration;

    /**
     * @param Context $context
     * @param OpenPosConfiguration $openPosConfiguration
     * @param array $data
     */
    public function __construct(
        Context $context,
        OpenPosConfiguration $openPosConfiguration,
        array $data = []
    ) {
        $this->openPosConfiguration = $openPosConfiguration;

        parent::__construct($context, $data);
    }

    /**
     * Retrieve TFA enabled status from configuration
     *
     * @return bool
     */
    public function isTfaEnabled(): bool
    {
        return $this->openPosConfiguration->isTfaEnabled();
    }
}
