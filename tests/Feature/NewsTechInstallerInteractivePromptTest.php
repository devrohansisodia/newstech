<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class NewsTechInstallerInteractivePromptTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_interactive_installer_allows_clean_abort_after_database_retry_failure(): void
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.driver' => 'sqlite',
            'database.connections.sqlite.database' => '/missing-directory/newstech.sqlite',
            'database.connections.sqlite.host' => '',
            'database.connections.sqlite.port' => '',
            'database.connections.sqlite.username' => '',
            'database.connections.sqlite.password' => '',
        ]);

        $this->artisan('newstech:install')
            ->expectsConfirmation('Continue with NewsTech installation? This will reset existing application tables/data before installing NewsTech.', 'yes')
            ->expectsQuestion('Database host', '')
            ->expectsQuestion('Database port', '')
            ->expectsQuestion('Database name', '/missing-directory/newstech.sqlite')
            ->expectsQuestion('Database username', '')
            ->expectsQuestion('Database password', '')
            ->expectsOutputToContain('Database connection failed. Please review the database details and try again.')
            ->expectsConfirmation('Would you like to re-enter the database configuration?', 'no')
            ->expectsOutputToContain('Installation cancelled.')
            ->assertExitCode(1);
    }
}
