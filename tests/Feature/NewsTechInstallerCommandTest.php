<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Advertisement\Models\Advertisement;
use NewsTech\Article\Models\Article;
use NewsTech\Author\Models\Author;
use NewsTech\Bookmark\Models\Bookmark;
use NewsTech\Bookmark\Models\ReaderArticleHistory;
use NewsTech\Category\Models\Category;
use NewsTech\Comment\Models\Comment;
use NewsTech\Core\Models\SystemSetting;
use NewsTech\Media\Models\Media;
use NewsTech\Menu\Models\MenuGroup;
use NewsTech\Menu\Models\MenuItem;
use NewsTech\Newsletter\Models\NewsletterSubscriber;
use NewsTech\Page\Models\Page;
use NewsTech\Reader\Models\Reader;
use NewsTech\Tag\Models\Tag;
use Tests\TestCase;

class NewsTechInstallerCommandTest extends TestCase
{
    use RefreshDatabase;

    protected string $environmentBackup = '';

    protected string $environmentPath = '';

    protected bool $environmentFileExisted = false;

    /**
     * @var array<string, mixed>
     */
    protected array $configurationBackup = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->environmentPath = $this->app->environmentFilePath();
        $this->environmentFileExisted = File::exists($this->environmentPath);
        $this->environmentBackup = $this->environmentFileExisted
            ? File::get($this->environmentPath)
            : '';

        $this->configurationBackup = [
            'app.url' => config('app.url'),
            'app.key' => config('app.key'),
            'database.default' => config('database.default'),
            'database.connections' => config('database.connections'),
            'filesystems.default' => config('filesystems.default'),
        ];
    }

    protected function tearDown(): void
    {
        config($this->configurationBackup);
        parent::tearDown();

        if ($this->environmentFileExisted) {
            File::put($this->environmentPath, $this->environmentBackup);
        } elseif (File::exists($this->environmentPath)) {
            File::delete($this->environmentPath);
        }
    }

    public function test_installer_command_exists(): void
    {
        $this->artisan('newstech:install --help')
            ->expectsOutputToContain('Prepare NewsTech with migrations, settings, an admin account, storage, and optional demo content.')
            ->assertExitCode(0);
    }

    public function test_installer_can_create_default_admin_user_with_provided_options(): void
    {
        $this->artisan('newstech:install --without-demo-content --admin-email=owner@example.com --admin-password=secret123 --no-interaction --force')
            ->expectsOutputToContain('Installation summary')
            ->expectsOutputToContain('Created default user')
            ->assertExitCode(0);

        $adminUser = AdminUser::query()->where('email', 'owner@example.com')->first();

        $this->assertNotNull($adminUser);
        $this->assertTrue(Hash::check('secret123', $adminUser->password));
    }

    public function test_installer_supports_the_interactive_bagisto_style_flow(): void
    {
        $this->artisan('newstech:install')
            ->expectsConfirmation('Continue with NewsTech installation? This will reset existing application tables/data before installing NewsTech.', 'yes')
            ->expectsConfirmation('Install demo newsroom content?', 'no')
            ->expectsOutputToContain('Installation summary')
            ->assertExitCode(0);

        $this->assertDatabaseHas('admin_users', [
            'email' => 'admin@newstech.test',
            'name' => 'NewsTech Administrator',
        ]);
    }

    public function test_installer_overrides_default_admin_credentials_when_options_are_provided(): void
    {
        $this->artisan('newstech:install --without-demo-content --force --admin-email=owner@example.com --admin-password=new-secret --no-interaction')
            ->assertExitCode(0);

        $adminUser = AdminUser::query()->where('email', 'owner@example.com')->first();

        $this->assertNotNull($adminUser);
        $this->assertTrue(Hash::check('new-secret', $adminUser->password));
    }

    public function test_installer_seeds_default_settings_without_demo_content(): void
    {
        $this->artisan('newstech:install --without-demo-content --no-interaction --force')
            ->assertExitCode(0);

        $this->assertDatabaseHas('system_settings', [
            'key' => 'website.identity.site_name',
            'value' => 'NewsTech',
        ]);

        $this->assertDatabaseHas('system_settings', [
            'key' => 'website.homepage.layout',
            'value' => 'full_width',
        ]);
    }

    public function test_installer_can_run_without_demo_content(): void
    {
        $this->artisan('newstech:install --without-demo-content --no-interaction --force')
            ->assertExitCode(0);

        $this->assertSame(0, Category::query()->count());
        $this->assertSame(0, Tag::query()->count());
        $this->assertSame(0, Author::query()->count());
        $this->assertSame(0, Article::query()->count());
        $this->assertSame(0, Page::query()->count());
        $this->assertSame(0, MenuGroup::query()->count());
        $this->assertSame(0, MenuItem::query()->count());
    }

    public function test_installer_can_run_with_demo_content(): void
    {
        $this->artisan('newstech:install --with-demo-content --no-interaction --force')
            ->expectsOutputToContain('Demo Articles')
            ->assertExitCode(0);

        $this->assertSame(10, Category::query()->count());
        $this->assertSame(20, Tag::query()->count());
        $this->assertSame(6, Author::query()->count());
        $this->assertSame(20, Article::query()->count());
        $this->assertSame(6, Page::query()->count());
        $this->assertSame(3, MenuGroup::query()->count());
        $this->assertSame(17, MenuItem::query()->count());
        $this->assertSame(1, Reader::query()->count());
        $this->assertSame(4, Bookmark::query()->count());
        $this->assertSame(4, ReaderArticleHistory::query()->count());
        $this->assertSame(6, Comment::query()->count());
        $this->assertSame(3, NewsletterSubscriber::query()->count());
        $this->assertSame(1, Advertisement::query()->count());
        $this->assertGreaterThanOrEqual(19, Media::query()->count());

        $this->assertDatabaseHas('system_settings', [
            'key' => 'website.homepage.layout',
            'value' => 'two_column_70_30',
        ]);
        $this->assertDatabaseHas('system_settings', [
            'key' => 'website.identity.logo_path',
            'value' => 'newstech/demo/branding/logo.svg',
        ]);
        $this->assertDatabaseHas('system_settings', [
            'key' => 'website.identity.footer_logo_path',
            'value' => 'newstech/demo/branding/footer_logo.svg',
        ]);
    }

    public function test_demo_content_articles_include_local_featured_images_and_inline_images(): void
    {
        $this->artisan('newstech:install --with-demo-content --no-interaction --force')
            ->assertExitCode(0);

        $article = Article::query()->where('slug', 'cabinet-coalition-opens-summer-session-with-spending-pledge')->first();

        $this->assertNotNull($article);
        $this->assertSame('newstech/demo/categories/politics_cover.svg', $article->featured_image);
        $this->assertStringContainsString('/storage/newstech/demo/categories/politics_cover.svg', $article->content);
        $this->assertStringContainsString('<figure><img', $article->content);
        $this->assertStringContainsString('alt="Politics coverage illustration"', $article->content);
    }

    public function test_demo_assets_are_copied_to_public_storage_and_registered_as_media(): void
    {
        $this->artisan('newstech:install --with-demo-content --no-interaction --force')
            ->assertExitCode(0);

        $this->assertTrue(Storage::disk('public')->exists('newstech/demo/branding/logo.svg'));
        $this->assertTrue(Storage::disk('public')->exists('newstech/demo/categories/politics_cover.svg'));
        $this->assertTrue(Storage::disk('public')->exists('newstech/demo/authors/riya_sen.svg'));

        $this->assertDatabaseHas('media', [
            'disk' => 'public',
            'path' => 'newstech/demo/categories/politics_cover.svg',
        ]);
    }

    public function test_installer_resets_existing_data_on_repeated_run(): void
    {
        $command = 'newstech:install --with-demo-content --admin-email=owner@example.com --admin-password=secret123 --no-interaction --force';

        $this->artisan($command)->assertExitCode(0);

        Category::factory()->create([
            'name' => 'Temporary Category',
            'slug' => 'temporary-category',
        ]);

        $this->artisan($command)->assertExitCode(0);

        $this->assertDatabaseMissing('categories', [
            'slug' => 'temporary-category',
        ]);
        $this->assertSame(10, Category::query()->count());
    }

    public function test_installer_rejects_invalid_admin_email(): void
    {
        $this->artisan('newstech:install --without-demo-content --admin-email=invalid-email --admin-password=secret123 --no-interaction --force')
            ->expectsOutputToContain('not a valid email address')
            ->assertExitCode(1);
    }

    public function test_installer_rejects_empty_admin_password(): void
    {
        $this->artisan('newstech:install --without-demo-content --admin-email=owner@example.com --admin-password= --no-interaction --force')
            ->expectsOutputToContain('password cannot be empty')
            ->assertExitCode(1);
    }

    public function test_installer_created_admin_can_access_categories_route(): void
    {
        $this->artisan('newstech:install --without-demo-content --admin-email=owner@example.com --admin-password=secret123 --no-interaction --force')
            ->assertExitCode(0);

        $response = $this->post(route('admin.newstech.login.store'), [
            'email' => 'owner@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.newstech.dashboard'));
        $this->assertAuthenticated('admin');

        $categoriesResponse = $this->get('/admin/categories');

        $categoriesResponse->assertOk();
    }

    public function test_demo_homepage_branding_and_sidebar_settings_are_seeded(): void
    {
        $this->artisan('newstech:install --with-demo-content --no-interaction --force')
            ->assertExitCode(0);

        $homepage = $this->get(route('newstech.home'));

        $homepage->assertOk();
        $homepage->assertSee('data-homepage-layout="two_column_70_30"', false);
        $homepage->assertSee('Inside NewsTech');
        $homepage->assertSee('A ready-to-review sidebar with editorial notes');
        $homepage->assertSee('/storage/newstech/demo/branding/logo.svg', false);
        $homepage->assertSee('/storage/newstech/demo/branding/footer_logo.svg', false);

        $this->assertSame('Editorial Policy', SystemSetting::query()->where('key', 'website.homepage.sidebar_link_label')->value('value'));
    }

    public function test_interactive_installer_updates_environment_values(): void
    {
        $this->artisan('newstech:install')
            ->expectsConfirmation('Continue with NewsTech installation? This will reset existing application tables/data before installing NewsTech.', 'yes')
            ->expectsConfirmation('Install demo newsroom content?', 'no')
            ->assertExitCode(0);

        $environmentContents = File::get($this->app->environmentFilePath());

        $this->assertStringContainsString('DB_CONNECTION=sqlite', $environmentContents);
        $this->assertStringContainsString('DB_DATABASE=:memory:', $environmentContents);
        $this->assertStringContainsString('FILESYSTEM_DISK=public', $environmentContents);
        $this->assertTrue(filled(config('app.key')));
    }

    public function test_non_interactive_installer_shows_friendly_database_failure_message(): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.driver' => 'mysql',
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.port' => '3306',
            'database.connections.mysql.database' => 'missing_database',
            'database.connections.mysql.username' => 'missing_user',
            'database.connections.mysql.password' => 'missing_password',
        ]);

        $this->artisan('newstech:install --without-demo-content --no-interaction --force')
            ->expectsOutputToContain('Database connection failed. Please update your .env database credentials or run php artisan newstech:install interactively.')
            ->assertExitCode(1);
    }

    public function test_installer_creates_default_admin_user_automatically(): void
    {
        $this->artisan('newstech:install --without-demo-content --no-interaction --force')
            ->assertExitCode(0);

        $adminUser = AdminUser::query()->where('email', 'admin@newstech.test')->first();

        $this->assertNotNull($adminUser);
        $this->assertSame('NewsTech Administrator', $adminUser->name);
        $this->assertTrue(Hash::check('password', $adminUser->password));
    }

    public function test_non_interactive_install_requires_force_before_fresh_reset(): void
    {
        $this->artisan('newstech:install --without-demo-content --no-interaction')
            ->expectsOutputToContain('Use --force together with --no-interaction to allow the installer to reset existing application tables/data.')
            ->assertExitCode(1);
    }

    public function test_composer_post_create_project_scripts_do_not_run_migrations(): void
    {
        $composerConfiguration = json_decode(File::get(base_path('composer.json')), true, 512, JSON_THROW_ON_ERROR);
        $postCreateScripts = $composerConfiguration['scripts']['post-create-project-cmd'] ?? [];

        $this->assertNotEmpty($postCreateScripts);
        $this->assertFalse(collect($postCreateScripts)->contains(fn (string $script): bool => str_contains($script, 'migrate')));
    }
}
