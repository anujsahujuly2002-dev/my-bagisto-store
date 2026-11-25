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

    /**
     * The statuses of a tenant whose database has been built, the ones a command run over every store reaches.
     *
     * @return list<string>
     */
    public static function withDatabase(): array
    {
        return [
            self::ACTIVE,
            self::SUSPENDED,
        ];
    }

    /**
     * The statuses a tenant can be provisioned from: never built, or built and cleaned up after a failure.
     *
     * @return list<string>
     */
    public static function provisionable(): array
    {
        return [
            self::PENDING_VERIFICATION,
            self::FAILED,
        ];
    }
}
