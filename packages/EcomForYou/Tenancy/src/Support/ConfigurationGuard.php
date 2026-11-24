<?php

namespace EcomForYou\Tenancy\Support;

use EcomForYou\Tenancy\Exceptions\TenancyMisconfiguredException;

class ConfigurationGuard
{
    /**
     * Refuse to run tenancy on a configuration that would let one store reach another.
     */
    public function check(): void
    {
        if (! config('tenancy.enabled')) {
            return;
        }

        if (filled(config('session.domain'))) {
            throw new TenancyMisconfiguredException('SESSION_DOMAIN must stay unset while tenancy is on: a cookie scoped to a shared domain carries one store\'s session to every other store.');
        }

        if (blank(config('tenancy.platform_domain'))) {
            throw new TenancyMisconfiguredException('TENANCY_PLATFORM_DOMAIN must be set while tenancy is on.');
        }

        $connection = config('tenancy.database.connection');

        $template = config('tenancy.database.template') ?: config('database.default');

        if (
            $template === $connection
            || ! is_array(config("database.connections.{$template}"))
        ) {
            throw new TenancyMisconfiguredException("The tenant connection template [{$template}] must be a configured connection other than [{$connection}].");
        }

        if (! is_array(config('database.connections.'.config('tenancy.landlord_connection')))) {
            throw new TenancyMisconfiguredException('The landlord connection is not configured.');
        }
    }
}
