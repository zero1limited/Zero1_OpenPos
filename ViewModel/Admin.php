<?php
declare(strict_types=1);

namespace Zero1\OpenPos\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Zero1\OpenPos\Model\UrlProvider;

class Admin implements ArgumentInterface
{
    /**
     * @var UrlProvider
     */
    protected $urlProvider;

    /**
     * @param UrlProvider $urlProvider
     */
    public function __construct(
        UrlProvider $urlProvider
    ) {
        $this->urlProvider = $urlProvider;
    }

    /**
     * Retrieve URL for the Magento admin.
     *
     * @return string
     */
    public function getAdminUrl(): string
    {
        return $this->urlProvider->getAdminUrl();
    }
}
