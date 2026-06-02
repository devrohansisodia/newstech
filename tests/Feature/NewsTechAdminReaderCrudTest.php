<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Reader\Models\Reader;
use Tests\TestCase;

class NewsTechAdminReaderCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_readers(): void
    {
        $adminUser = AdminUser::factory()->create();
        Reader::factory()->create([
            'name' => 'Reader Listing',
            'email' => 'listing@example.com',
        ]);

        $this->actingAs($adminUser, 'admin')
            ->get(route('admin.newstech.readers.index'))
            ->assertOk()
            ->assertSee('Readers')
            ->assertSee('Reader Listing')
            ->assertSee('listing@example.com');
    }

    public function test_admin_can_create_reader(): void
    {
        $adminUser = AdminUser::factory()->create();

        $response = $this->actingAs($adminUser, 'admin')
            ->post(route('admin.newstech.readers.store'), [
                'name' => 'Created Reader',
                'email' => 'created@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'is_active' => '1',
                'website' => 'https://example.com',
                'bio' => 'Created by admin.',
            ]);

        $response->assertRedirect(route('admin.newstech.readers.index'));
        $this->assertDatabaseHas('readers', [
            'name' => 'Created Reader',
            'email' => 'created@example.com',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_edit_reader(): void
    {
        $adminUser = AdminUser::factory()->create();
        $reader = Reader::factory()->create([
            'email' => 'before@example.com',
        ]);

        $response = $this->actingAs($adminUser, 'admin')
            ->put(route('admin.newstech.readers.update', $reader), [
                'name' => 'Updated Reader',
                'email' => 'after@example.com',
                'password' => '',
                'password_confirmation' => '',
                'is_active' => '1',
                'website' => 'https://reader.example.com',
                'bio' => 'Updated by admin.',
            ]);

        $response->assertRedirect(route('admin.newstech.readers.edit', $reader));
        $this->assertDatabaseHas('readers', [
            'id' => $reader->getKey(),
            'name' => 'Updated Reader',
            'email' => 'after@example.com',
            'website' => 'https://reader.example.com',
        ]);
    }

    public function test_admin_can_deactivate_reader_and_inactive_reader_cannot_login(): void
    {
        $adminUser = AdminUser::factory()->create();
        $reader = Reader::factory()->create([
            'email' => 'reader@example.com',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $this->actingAs($adminUser, 'admin')
            ->put(route('admin.newstech.readers.update', $reader), [
                'name' => $reader->name,
                'email' => $reader->email,
                'password' => '',
                'password_confirmation' => '',
                'is_active' => '0',
                'website' => '',
                'bio' => '',
            ])
            ->assertRedirect(route('admin.newstech.readers.edit', $reader));

        $this->post(route('newstech.readers.login.store'), [
            'email' => $reader->email,
            'password' => 'password123',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_can_delete_reader(): void
    {
        $adminUser = AdminUser::factory()->create();
        $reader = Reader::factory()->create();

        $this->actingAs($adminUser, 'admin')
            ->delete(route('admin.newstech.readers.destroy', $reader))
            ->assertRedirect(route('admin.newstech.readers.index'));

        $this->assertSoftDeleted('readers', [
            'id' => $reader->getKey(),
        ]);
    }

    public function test_admin_sidebar_contains_readers_menu_link(): void
    {
        $adminUser = AdminUser::factory()->create();

        $this->actingAs($adminUser, 'admin')
            ->get(route('admin.newstech.dashboard'))
            ->assertOk()
            ->assertSee('Readers')
            ->assertSee(route('admin.newstech.readers.index'), false);
    }
}
