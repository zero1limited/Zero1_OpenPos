<?php
declare(strict_types=1);

namespace Zero1\OpenPos\ViewModel;

use Composer\InstalledVersions;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * "Powered by OpenPOS" line on the till home page, see view/frontend/templates/home/powered-by.phtml
 */
class PoweredBy implements ArgumentInterface
{
    const PACKAGE_NAME = 'zero1/open-pos';
    const PROJECT_URL = 'https://www.zero1.co.uk/blog/post/stories/openpos';

    /**
     * Installed OpenPOS version, null when not installed with Composer (e.g. in app/code)
     *
     * @return string|null
     */
    public function getVersion(): ?string
    {
        if (!class_exists(InstalledVersions::class) || !InstalledVersions::isInstalled(self::PACKAGE_NAME)) {
            return null;
        }

        return InstalledVersions::getPrettyVersion(self::PACKAGE_NAME) ?: null;
    }

    /**
     * @return string
     */
    public function getProjectUrl(): string
    {
        return self::PROJECT_URL;
    }
}
