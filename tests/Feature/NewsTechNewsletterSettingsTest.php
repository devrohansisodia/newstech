<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Admin\Models\AdminUser;
use Tests\TestCase;

class NewsTechNewsletterSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_newsletter_settings_card_and_group_can_be_saved(): void
    {
        $adminUser = AdminUser::factory()->create();

        $indexResponse = $this->actingAs($adminUser, 'admin')
            ->get(route('admin.newstech.settings.index'));

        $indexResponse->assertOk();
        $indexResponse->assertSee('Newsletter Settings');

        $this->actingAs($adminUser, 'admin')
            ->put(route('admin.newstech.settings.update', ['group' => 'newsletter']), [
                'enabled' => '1',
                'double_opt_in' => '0',
                'allow_resubscribe' => '1',
                'sender_name' => 'NewsTech Desk',
                'sender_email' => 'desk@example.com',
                'footer_unsubscribe_text' => 'Custom unsubscribe footer.',
            ])
            ->assertRedirect(route('admin.newstech.settings.show', ['group' => 'newsletter']));

        $this->assertDatabaseHas('system_settings', [
            'key' => 'newsletter.sender_email',
            'value' => 'desk@example.com',
        ]);
    }
}
