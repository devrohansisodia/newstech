<?php

namespace NewsTech\Installer\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Installer\Support\DefaultSettingsInstaller;
use NewsTech\Installer\Support\DemoAssetPublisher;
use NewsTech\Installer\Support\DemoContentInstaller;
use NewsTech\Installer\Support\EnvironmentFileManager;

class NewsTechInstallCommand extends Command
{
    protected $signature = 'newstech:install
        {--force : Allow destructive fresh install behavior in non-interactive mode}
        {--with-demo-content : Install the full NewsTech demo newsroom dataset}
        {--without-demo-content : Skip demo content installation}
        {--admin-email= : Admin email for the default admin user}
        {--admin-password= : Admin password for the default admin user}';

    protected $description = 'Prepare NewsTech with migrations, settings, an admin account, storage, and optional demo content.';

    public function __construct(
        protected DefaultSettingsInstaller $defaultSettingsInstaller,
        protected DemoAssetPublisher $demoAssetPublisher,
        protected DemoContentInstaller $demoContentInstaller,
        protected EnvironmentFileManager $environmentFileManager,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if ($this->option('with-demo-content') && $this->option('without-demo-content')) {
            $this->components->error('Use either --with-demo-content or --without-demo-content, not both.');

            return self::FAILURE;
        }

        if (! $this->confirmInstallation()) {
            $this->components->warn('Installation cancelled.');

            return self::FAILURE;
        }

        $this->environmentFileManager->ensureEnvironmentFileExists();

        if (! $this->configureApplicationEnvironment()) {
            return self::FAILURE;
        }

        $this->ensureApplicationKey();

        $seedDemoContent = $this->resolveDemoContentPreference();
        $adminName = 'NewsTech Administrator';
        $adminEmail = $this->resolveAdminEmail();
        $adminPassword = $this->resolveAdminPassword();

        if (! filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $this->components->error('The provided admin email is not a valid email address.');

            return self::FAILURE;
        }

        if ($adminPassword === '') {
            $this->components->error('The admin password cannot be empty.');

            return self::FAILURE;
        }

        $this->components->info('Resetting and migrating the database...');

        if ($this->performFreshInstallationReset() !== self::SUCCESS) {
            $this->components->error('Database reset failed during migrate:fresh.');

            return self::FAILURE;
        }

        $this->components->info('Checking public storage link...');
        $storageLinkStatus = $this->ensurePublicStorageLink();

        $adminUser = null;
        $this->components->info('Ensuring default admin user...');
        ['user' => $adminUser, 'status' => $adminStatus] = $this->ensureAdminUser(
            $adminName,
            $adminEmail,
            $adminPassword
        );

        $this->components->info('Publishing installer demo assets...');
        $publishedAssets = $this->demoAssetPublisher->publish(true);
        $branding = [
            'logo_path' => $publishedAssets['assets']['branding.logo'] ?? null,
            'footer_logo_path' => $publishedAssets['assets']['branding.footer_logo'] ?? null,
        ];

        $this->components->info('Seeding default site settings...');
        $settingsSummary = $this->defaultSettingsInstaller->seed(true, $seedDemoContent, $branding);

        $demoSummary = [
            'categories' => 0,
            'tags' => 0,
            'authors' => 0,
            'articles' => 0,
            'pages' => 0,
            'menu_groups' => 0,
            'menu_items' => 0,
            'readers' => 0,
            'bookmarks' => 0,
            'history_rows' => 0,
            'comments' => 0,
            'subscribers' => 0,
            'advertisements' => 0,
        ];

        if ($seedDemoContent) {
            $this->components->info('Installing demo newsroom content...');
            $demoSummary = $this->demoContentInstaller->seed(true, $publishedAssets['assets'], $adminUser);
        }

        $frontendUrl = url('/');
        $adminUrl = route(config('newstech-admin.auth.login_route'));
        $assetStatus = $this->assetManifestStatus();

        $this->newLine();
        $this->components->info('Installation summary');
        $this->components->twoColumnDetail('Frontend URL', $frontendUrl);
        $this->components->twoColumnDetail('Admin URL', $adminUrl);
        $this->components->twoColumnDetail('Admin Email', $adminUser?->email ?? $adminEmail);
        $this->components->twoColumnDetail('Admin Password', $adminPassword);
        $this->components->twoColumnDetail('Admin User', $adminStatus);
        $this->components->twoColumnDetail('Database Reset', 'Ran migrate:fresh');
        $this->components->twoColumnDetail('Storage Link', $storageLinkStatus);
        $this->components->twoColumnDetail('Demo Content', $seedDemoContent ? 'Installed' : 'Skipped');
        $this->components->twoColumnDetail('Asset Manifests', $assetStatus);
        $this->components->twoColumnDetail('Settings Seeded', (string) $settingsSummary['persisted']);

        if ($seedDemoContent) {
            $this->components->twoColumnDetail('Demo Articles', (string) $demoSummary['articles']);
            $this->components->twoColumnDetail('Demo Pages', (string) $demoSummary['pages']);
            $this->components->twoColumnDetail('Demo Assets Copied', (string) $publishedAssets['count']);
        }

        if ($assetStatus !== 'Ready') {
            $this->newLine();
            $this->components->warn('Frontend/admin asset manifests are missing. Run npm install and npm run build before sharing the demo.');
        }

        $this->newLine();
        $this->components->warn('Change the default admin password before using NewsTech outside a local development environment.');
        $this->newLine();
        $this->components->info('NewsTech installer completed successfully.');

        return self::SUCCESS;
    }

    protected function confirmInstallation(): bool
    {
        if (! $this->input->isInteractive()) {
            if (! $this->option('force')) {
                $this->components->error('Use --force together with --no-interaction to allow the installer to reset existing application tables/data.');

                return false;
            }

            return true;
        }

        if (app()->environment('production') && ! $this->option('force')) {
            return $this->confirm(
                'Continue with NewsTech installation? This will reset existing application tables/data before installing NewsTech. You are running this in production.',
                false
            );
        }

        return $this->confirm(
            'Continue with NewsTech installation? This will reset existing application tables/data before installing NewsTech.',
            true
        );
    }

    protected function configureApplicationEnvironment(): bool
    {
        $applicationUrl = $this->resolveApplicationUrl();
        $databaseConfiguration = $this->currentDatabaseConfiguration();

        $this->persistInstallerEnvironment($applicationUrl, $databaseConfiguration);
        $this->applyRuntimeConfiguration($applicationUrl, $databaseConfiguration);

        if (! $this->verifyDatabaseConnection()) {
            if (! $this->input->isInteractive()) {
                $this->components->error(
                    'Database connection failed. Please update your .env database credentials or run php artisan newstech:install interactively.'
                );

                return false;
            }

            while (true) {
                $this->components->warn('Database connection failed. Please review the database details and try again.');
                $databaseConfiguration = $this->promptForDatabaseConfiguration($databaseConfiguration);

                $this->persistInstallerEnvironment($applicationUrl, $databaseConfiguration);
                $this->applyRuntimeConfiguration($applicationUrl, $databaseConfiguration);

                if ($this->verifyDatabaseConnection()) {
                    break;
                }

                if (! $this->confirm('Would you like to re-enter the database configuration?', true)) {
                    $this->components->warn('Installation cancelled.');

                    return false;
                }
            }
        }

        $appKeyPresent = filled(config('app.key'));

        $this->components->twoColumnDetail('Environment', app()->environment());
        $this->components->twoColumnDetail('App URL', (string) config('app.url'));
        $this->components->twoColumnDetail('Database', (string) config('database.default'));
        $this->components->twoColumnDetail('Database Host', (string) config('database.connections.'.config('database.default').'.host', ''));
        $this->components->twoColumnDetail('Database Name', (string) config('database.connections.'.config('database.default').'.database', ''));
        $this->components->twoColumnDetail('Filesystem Disk', (string) config('filesystems.default'));
        $this->components->twoColumnDetail('App Key', $appKeyPresent ? 'Configured' : 'Missing');

        if (! $appKeyPresent) {
            $this->components->warn('APP_KEY is missing. Generate one before using NewsTech in a real environment.');
        }

        if (config('filesystems.default') !== 'public') {
            $this->components->warn('FILESYSTEM_DISK is not set to public. NewsTech media paths are optimized for the public disk.');
        }

        return true;
    }

    protected function resolveApplicationUrl(): string
    {
        return (string) config('app.url', 'http://127.0.0.1:8000');
    }

    /**
     * @return array{connection:string,host:string,port:string,database:string,username:string,password:string}
     */
    protected function currentDatabaseConfiguration(): array
    {
        $defaultConnection = (string) config('database.default', 'mysql');
        $defaultConnectionConfig = config('database.connections.'.$defaultConnection, []);

        return [
            'connection' => (string) ($defaultConnectionConfig['driver'] ?? $defaultConnection ?: 'mysql'),
            'host' => (string) ($defaultConnectionConfig['host'] ?? '127.0.0.1'),
            'port' => (string) ($defaultConnectionConfig['port'] ?? '3306'),
            'database' => (string) ($defaultConnectionConfig['database'] ?? ''),
            'username' => (string) ($defaultConnectionConfig['username'] ?? ''),
            'password' => (string) ($defaultConnectionConfig['password'] ?? ''),
        ];
    }

    /**
     * @param  array{connection:string,host:string,port:string,database:string,username:string,password:string}  $resolvedConfiguration
     * @return array{connection:string,host:string,port:string,database:string,username:string,password:string}
     */
    protected function promptForDatabaseConfiguration(array $resolvedConfiguration): array
    {
        if (! $this->input->isInteractive()) {
            return $resolvedConfiguration;
        }

        /** @var string $host */
        $host = $this->ask('Database host', $resolvedConfiguration['host']);
        /** @var string $port */
        $port = $this->ask('Database port', $resolvedConfiguration['port']);
        /** @var string $database */
        $database = $this->ask('Database name', $resolvedConfiguration['database']);
        /** @var string $username */
        $username = $this->ask('Database username', $resolvedConfiguration['username']);
        /** @var string $password */
        $password = $this->secret('Database password') ?? $resolvedConfiguration['password'];

        return [
            'connection' => $resolvedConfiguration['connection'],
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password,
        ];
    }

    /**
     * @param  array{connection:string,host:string,port:string,database:string,username:string,password:string}  $databaseConfiguration
     */
    protected function persistInstallerEnvironment(string $applicationUrl, array $databaseConfiguration): void
    {
        $this->environmentFileManager->update([
            'APP_URL' => $applicationUrl,
            'DB_CONNECTION' => $databaseConfiguration['connection'],
            'DB_HOST' => $databaseConfiguration['host'],
            'DB_PORT' => $databaseConfiguration['port'],
            'DB_DATABASE' => $databaseConfiguration['database'],
            'DB_USERNAME' => $databaseConfiguration['username'],
            'DB_PASSWORD' => $databaseConfiguration['password'],
            'FILESYSTEM_DISK' => 'public',
        ]);
    }

    /**
     * @param  array{connection:string,host:string,port:string,database:string,username:string,password:string}  $databaseConfiguration
     */
    protected function applyRuntimeConfiguration(string $applicationUrl, array $databaseConfiguration): void
    {
        $currentDefaultConnection = (string) config('database.default');
        $currentDatabaseName = (string) config('database.connections.'.$currentDefaultConnection.'.database', '');

        config([
            'app.url' => $applicationUrl,
            'database.default' => $databaseConfiguration['connection'],
            'database.connections.'.$databaseConfiguration['connection'].'.driver' => $databaseConfiguration['connection'],
            'database.connections.'.$databaseConfiguration['connection'].'.host' => $databaseConfiguration['host'],
            'database.connections.'.$databaseConfiguration['connection'].'.port' => $databaseConfiguration['port'],
            'database.connections.'.$databaseConfiguration['connection'].'.database' => $databaseConfiguration['database'],
            'database.connections.'.$databaseConfiguration['connection'].'.username' => $databaseConfiguration['username'],
            'database.connections.'.$databaseConfiguration['connection'].'.password' => $databaseConfiguration['password'],
            'filesystems.default' => 'public',
        ]);

        if (
            app()->environment('testing')
            && $currentDefaultConnection === 'sqlite'
            && $currentDatabaseName === ':memory:'
            && $databaseConfiguration['connection'] === 'sqlite'
            && $databaseConfiguration['database'] === ':memory:'
        ) {
            return;
        }

        DB::purge($databaseConfiguration['connection']);
    }

    protected function verifyDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable $exception) {
            if (! $this->input->isInteractive()) {
                $this->components->warn('Database connection error: '.$exception->getMessage());
            }

            return false;
        }
    }

    protected function ensureApplicationKey(): void
    {
        if (filled(config('app.key'))) {
            return;
        }

        $applicationKey = 'base64:'.base64_encode(Str::random(32));

        $this->environmentFileManager->update([
            'APP_KEY' => $applicationKey,
        ]);

        config([
            'app.key' => $applicationKey,
        ]);

        $this->components->info('Generated a new application key for this installation.');
    }

    protected function performFreshInstallationReset(): int
    {
        $databaseConfiguration = $this->currentDatabaseConfiguration();

        if ($this->shouldPreserveInMemoryTestingConnection($databaseConfiguration)) {
            $this->truncateSqliteTestingTables();

            return self::SUCCESS;
        }

        return $this->call('migrate:fresh', ['--force' => true]);
    }

    protected function resolveAdminEmail(): string
    {
        $email = (string) ($this->option('admin-email') ?? '');

        if ($this->optionWasProvided('admin-email')) {
            return $email;
        }

        return 'admin@newstech.test';
    }

    protected function resolveAdminPassword(): string
    {
        $password = (string) ($this->option('admin-password') ?? '');

        if ($this->optionWasProvided('admin-password')) {
            return $password;
        }

        return 'password';
    }

    protected function resolveDemoContentPreference(): bool
    {
        if ($this->option('with-demo-content')) {
            return true;
        }

        if ($this->option('without-demo-content')) {
            return false;
        }

        if ($this->input->isInteractive()) {
            return $this->confirm('Install demo newsroom content?', false);
        }

        return false;
    }

    /**
     * @param  array{connection:string,host:string,port:string,database:string,username:string,password:string}  $databaseConfiguration
     */
    protected function shouldPreserveInMemoryTestingConnection(array $databaseConfiguration): bool
    {
        return app()->environment('testing')
            && $databaseConfiguration['connection'] === 'sqlite'
            && $databaseConfiguration['database'] === ':memory:';
    }

    protected function optionWasProvided(string $option): bool
    {
        return $this->input->hasParameterOption('--'.$option);
    }

    /**
     * @return array{user:AdminUser,status:string}
     */
    protected function ensureAdminUser(string $name, string $email, string $password): array
    {
        $user = AdminUser::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        return [
            'user' => $user,
            'status' => 'Created default user',
        ];
    }

    protected function ensurePublicStorageLink(): string
    {
        $publicStoragePath = public_path('storage');

        if (is_link($publicStoragePath) || File::exists($publicStoragePath)) {
            return 'Ready';
        }

        $exitCode = $this->callSilently('storage:link');

        if ($exitCode === self::SUCCESS && (is_link($publicStoragePath) || File::exists($publicStoragePath))) {
            return 'Created';
        }

        return 'Missing - run php artisan storage:link';
    }

    protected function truncateSqliteTestingTables(): void
    {
        $tables = collect(DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"))
            ->map(fn (object $table): string => $table->name)
            ->reverse()
            ->all();

        DB::statement('PRAGMA foreign_keys = OFF');

        foreach ($tables as $table) {
            DB::table($table)->delete();
        }

        DB::statement('DELETE FROM sqlite_sequence');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    protected function assetManifestStatus(): string
    {
        $requiredManifests = [
            public_path('build/manifest.json'),
            public_path('build-admin/manifest.json'),
            public_path('build-frontend/manifest.json'),
        ];

        foreach ($requiredManifests as $manifest) {
            if (! File::exists($manifest)) {
                return 'Missing';
            }
        }

        return 'Ready';
    }
}
