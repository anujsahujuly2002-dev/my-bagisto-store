<?php

namespace EcomForYou\Tenancy\Enums;

class TenantStatus
{
    /**
     * Signed up, waiting for the owner to verify their email; no database exists yet.
     */
    public const PENDING_VERIFICATION = 'pending_verification';

    /**
     * The database and first admin are being created.
     */
    public const PROVISIONING = 'provisioning';

    /**
     * Open for business.
     */
    public const ACTIVE = 'active';

    /**
     * Closed by the platform, with its data kept.
     */
    public const SUSPENDED = 'suspended';

    /**
     * Provisioning failed and was cleaned up.
     */
    public const FAILED = 'failed';

    /**
     * Every valid status.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::PENDING_VERIFICATION,
            self::PROVISIONING,
            self::ACTIVE,
            self::SUSPENDED,
            self::FAILED,
        ];
    }
}
