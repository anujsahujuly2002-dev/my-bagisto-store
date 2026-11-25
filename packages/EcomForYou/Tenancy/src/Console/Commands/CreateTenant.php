<?php

namespace EcomForYou\Tenancy\Console\Commands;

use DateTimeZone;
use EcomForYou\Core\Helpers\SupportedCurrencies;
use EcomForYou\Core\Helpers\SupportedLocales;
use EcomForYou\Tenancy\Jobs\ProvisionTenant;
use EcomForYou\Tenancy\Provisioning\TenantProvisioner;
use EcomForYou\Tenancy\Repositories\TenantRepository;
use EcomForYou\Tenancy\Rules\Subdomain;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

use function Laravel\Prompts\password;

class CreateTenant extends Command
{
    /**
     * The length of a password generated for an owner who was given none.
     */
    public const GENERATED_PASSWORD_LENGTH = 16;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:create
        {name : The store name}
        {subdomain : The subdomain the store is served from}
        {email : The email of the store owner, who becomes its first admin}
        {--admin-name= : The first admin\'s name, `Admin` when left out}
        {--password= : The first admin\'s password; asked for, or generated without interaction, when left out}
        {--locale= : The store\'s default locale, the application locale when left out}
        {--currency= : The store\'s default currency, the application currency when left out}
        {--timezone= : The store\'s timezone, the application timezone when left out}
        {--queue : Provision on the queue instead of waiting for it here}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a tenant and provision its store: database, schema, basic data, first admin and folders';

    /**
     * Validate the details, record the tenant and provision its store, here or on the queue.
     */
    public function handle(TenantRepository $tenantRepository, TenantProvisioner $tenantProvisioner): int
    {
        $details = [
            'name' => $this->argument('name'),
            'subdomain' => strtolower(trim($this->argument('subdomain'))),
            'email' => trim($this->argument('email')),
            'admin_name' => $this->option('admin-name'),
            'locale' => $this->option('locale') ?? config('app.locale'),
            'currency' => $this->option('currency') ?? config('app.currency'),
            'timezone' => $this->option('timezone') ?? config('app.timezone'),
        ];

        $validator = Validator::make($details, $this->rules());

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        [$password, $generated] = $this->password();

        if (strlen($password) < 6) {
            $this->components->error('The password must be at least 6 characters.');

            return self::FAILURE;
        }

        $tenant = $tenantRepository->create([
            'name' => $details['name'],
            'subdomain' => $details['subdomain'],
            'owner_email' => $details['email'],
            'data' => [
                'locale' => $details['locale'],
                'currency' => $details['currency'],
                'timezone' => $details['timezone'],
            ],
        ]);

        $owner = array_filter([
            'name' => $details['admin_name'],
            'email' => $details['email'],
            'password' => $password,
            'locale' => $details['locale'],
            'currency' => $details['currency'],
            'timezone' => $details['timezone'],
        ], 'filled');

        if ($this->option('queue')) {
            ProvisionTenant::dispatch($tenant, $owner);

            $this->components->info("Tenant #{$tenant->id} [{$tenant->subdomain}] is queued for provisioning.");

            $this->printCredentials($tenant->url(), $details['email'], $generated ? $password : null);

            return self::SUCCESS;
        }

        $this->components->info("Provisioning tenant #{$tenant->id} [{$tenant->subdomain}], which takes a minute or two.");

        try {
            $tenantProvisioner->provision($tenant, $owner);
        } catch (Throwable $exception) {
            $this->components->error('Provisioning failed and was cleaned up: '.$exception->getMessage());

            $this->components->warn("The tenant is kept as failed; remove it with `tenants:delete {$tenant->subdomain}` before trying again.");

            return self::FAILURE;
        }

        $this->components->info('The store is ready.');

        $this->printCredentials($tenant->url(), $details['email'], $generated ? $password : null);

        return self::SUCCESS;
    }

    /**
     * The rules the store details must meet.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'subdomain' => ['required', new Subdomain],
            'email' => ['required', 'email', 'max:255'],
            'admin_name' => ['nullable', 'string', 'max:255'],
            'locale' => ['required', Rule::in(SupportedLocales::codes())],
            'currency' => ['required', Rule::in(SupportedCurrencies::codes())],
            'timezone' => ['required', Rule::in(DateTimeZone::listIdentifiers())],
        ];
    }

    /**
     * The first admin's password, from the option, a prompt, or generated when nobody can be asked, and whether
     * it was generated.
     *
     * @return array{0: string, 1: bool}
     */
    protected function password(): array
    {
        if (filled($password = $this->option('password'))) {
            return [$password, false];
        }

        if ($this->input->isInteractive()) {
            return [password(label: 'Password for the first admin', required: true), false];
        }

        return [Str::password(self::GENERATED_PASSWORD_LENGTH, symbols: false), true];
    }

    /**
     * Print where the store's admin panel is and who signs in, with the password only when it was generated here.
     */
    protected function printCredentials(string $url, string $email, ?string $generatedPassword): void
    {
        $this->components->twoColumnDetail('Storefront', $url);

        $this->components->twoColumnDetail('Admin panel', $url.'/'.trim((string) config('app.admin_url'), '/'));

        $this->components->twoColumnDetail('Admin email', $email);

        if ($generatedPassword) {
            $this->components->twoColumnDetail('Generated password', $generatedPassword);
        }
    }
}
