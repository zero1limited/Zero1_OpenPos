<?php
declare(strict_types=1);

namespace Zero1\OpenPos\ViewModel;

use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\TillSessionManagement;

/**
 * Till home page welcome panel, see view/frontend/templates/home/welcome.phtml
 */
class Homepage implements ArgumentInterface
{
    /**
     * @var OpenPosConfiguration
     */
    protected $openPosConfiguration;

    /**
     * @var TillSessionManagement
     */
    protected $tillSessionManagement;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var LocaleResolver
     */
    protected $localeResolver;

    /**
     * @param OpenPosConfiguration $openPosConfiguration
     * @param TillSessionManagement $tillSessionManagement
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $timezone
     * @param LocaleResolver $localeResolver
     */
    public function __construct(
        OpenPosConfiguration $openPosConfiguration,
        TillSessionManagement $tillSessionManagement,
        StoreManagerInterface $storeManager,
        TimezoneInterface $timezone,
        LocaleResolver $localeResolver
    ) {
        $this->openPosConfiguration = $openPosConfiguration;
        $this->tillSessionManagement = $tillSessionManagement;
        $this->storeManager = $storeManager;
        $this->timezone = $timezone;
        $this->localeResolver = $localeResolver;
    }

    /**
     * First name of the admin user logged in to the till
     *
     * @return string|null
     */
    public function getTillUserFirstName(): ?string
    {
        $adminUser = $this->tillSessionManagement->getAdminUserFromTillSession();
        return $adminUser ? ((string)$adminUser->getFirstname() ?: null) : null;
    }

    /**
     * Store name from Stores > Configuration > General > Store Information, falling back to the store name
     *
     * @return string
     */
    public function getStoreName(): string
    {
        return (string)$this->storeManager->getStore()->getFrontendName();
    }

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        $message = trim((string)$this->openPosConfiguration->getHomepageMessage());
        return $message !== '' ? $message : null;
    }

    /**
     * Store timezone, so the till shows the store's time whatever the device's timezone is set to
     *
     * @return string
     */
    public function getTimezone(): string
    {
        return (string)$this->timezone->getConfigTimezone(ScopeInterface::SCOPE_STORE);
    }

    /**
     * Store locale as a BCP 47 tag for Intl.DateTimeFormat, e.g. en-GB
     *
     * @return string
     */
    public function getLocale(): string
    {
        return str_replace('_', '-', (string)$this->localeResolver->getLocale());
    }

    /**
     * Current time and date in the store's timezone and locale, as shown before the clock script runs
     *
     * @return array{time: string, date: string, hour: int}
     */
    public function getNow(): array
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone($this->getTimezone()));
        $locale = (string)$this->localeResolver->getLocale();

        return [
            'time' => $this->format($now, $locale, 'HH:mm'),
            'date' => $this->format($now, $locale, 'EEEE d MMMM y'),
            'hour' => (int)$now->format('G'),
        ];
    }

    /**
     * @param \DateTimeImmutable $dateTime
     * @param string $locale
     * @param string $pattern ICU date pattern
     * @return string
     */
    protected function format(\DateTimeImmutable $dateTime, string $locale, string $pattern): string
    {
        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            $dateTime->getTimezone(),
            null,
            $pattern
        );
        return (string)$formatter->format($dateTime);
    }
}
