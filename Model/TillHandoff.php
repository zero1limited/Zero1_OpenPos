<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Model;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;

class TillHandoff
{
    const PAYLOAD_ADMIN_ID = 'admin_id';
    const PAYLOAD_TIMESTAMP = 'time_stamp';

    /**
     * How long a handoff token stays valid (seconds).
     * A token only ever has to survive a single redirect, and the short life
     * is the only thing limiting replay, so keep this tight.
     */
    const TOKEN_LIFETIME = 10;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * @param EncryptorInterface $encryptor
     * @param SerializerInterface $serializer
     * @param DateTime $dateTime
     * @param UserFactory $userFactory
     */
    public function __construct(
        EncryptorInterface $encryptor,
        SerializerInterface $serializer,
        DateTime $dateTime,
        UserFactory $userFactory
    ) {
        $this->encryptor = $encryptor;
        $this->serializer = $serializer;
        $this->dateTime = $dateTime;
        $this->userFactory = $userFactory;
    }

    /**
     * Create a token identifying an already authenticated admin user.
     * Encrypted with the Magento crypt key, so it cannot be forged off-site.
     *
     * @param User $adminUser
     * @return string
     */
    public function generate(User $adminUser): string
    {
        return $this->encryptor->encrypt($this->serializer->serialize([
            self::PAYLOAD_ADMIN_ID => (int)$adminUser->getId(),
            self::PAYLOAD_TIMESTAMP => $this->dateTime->timestamp()
        ]));
    }

    /**
     * Return the admin user a token was issued for.
     * Says nothing about whether that user may operate a till - the caller
     * still has to go through TillSessionManagement::startTillSession().
     *
     * @param string $token
     * @return User
     */
    public function resolveAdminUser(string $token): User
    {
        $payload = $this->decode($token);

        $adminUser = $this->userFactory->create();
        $adminUser->load((int)$payload[self::PAYLOAD_ADMIN_ID]);

        if(!$adminUser->getId()) {
            throw $this->invalidToken();
        }

        return $adminUser;
    }

    /**
     * Decrypt and validate a token, returning its payload.
     *
     * @param string $token
     * @return array
     */
    protected function decode(string $token): array
    {
        if($token === '') {
            throw $this->invalidToken();
        }

        $decrypted = $this->encryptor->decrypt($token);
        if($decrypted === '') {
            throw $this->invalidToken();
        }

        try {
            $payload = $this->serializer->unserialize($decrypted);
        } catch(\InvalidArgumentException $e) {
            throw $this->invalidToken();
        }

        if(!is_array($payload) || !isset($payload[self::PAYLOAD_ADMIN_ID], $payload[self::PAYLOAD_TIMESTAMP])) {
            throw $this->invalidToken();
        }

        $tokenAge = $this->dateTime->timestamp() - (int)$payload[self::PAYLOAD_TIMESTAMP];
        if($tokenAge < 0 || $tokenAge >= self::TOKEN_LIFETIME) {
            throw $this->invalidToken();
        }

        return $payload;
    }

    /**
     * Every failure gives the same message - the till user can't act on the detail.
     *
     * @return LocalizedException
     */
    protected function invalidToken(): LocalizedException
    {
        return new LocalizedException(__('This till login link is no longer valid. Please log in below.'));
    }
}
