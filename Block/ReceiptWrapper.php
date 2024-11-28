<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Zero1\OpenPos\Helper\Data as PosHelper;

class ReceiptWrapper extends Template
{
    /**
     * @var PosHelper
     */
    protected $posHelper;

    /**
     * @param Context $context
     * @param PosHelper $posHelper
     */
    public function __construct(
        Context $context,
        PosHelper $posHelper,
        array $data = []
    ) {
        $this->posHelper = $posHelper;
        parent::__construct($context, $data);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml(): string
    {
        if(!$this->posHelper->isEnabled() || !$this->posHelper->currentlyOnPosStore()) {
            return '';
        }

        return parent::_toHtml();
    }
}
