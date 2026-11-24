<?php

namespace EcomForYou\Tenancy\Rules;

use Closure;
use EcomForYou\Tenancy\Repositories\TenantRepository;
use Illuminate\Contracts\Validation\ValidationRule;

class Subdomain implements ValidationRule
{
    /**
     * 3 to 63 lowercase letters, numbers and dashes, not starting or ending with a dash.
     */
    public const PATTERN = '/^(?!-)[a-z0-9-]{3,63}(?<!-)$/';

    /**
     * Create the rule, ignoring the given tenant when checking the subdomain is free, as a rename must.
     */
    public function __construct(protected ?int $ignoreTenantId = null) {}

    /**
     * Fail a subdomain that is malformed, reserved or already taken.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            ! is_string($value)
            || ! preg_match(self::PATTERN, $value)
        ) {
            $fail('tenancy::app.validation.subdomain.format')->translate();

            return;
        }

        if (in_array($value, config('tenancy.reserved_subdomains', []), true)) {
            $fail('tenancy::app.validation.subdomain.reserved')->translate();

            return;
        }

        if (app(TenantRepository::class)->subdomainTaken($value, $this->ignoreTenantId)) {
            $fail('tenancy::app.validation.subdomain.taken')->translate();
        }
    }
}
