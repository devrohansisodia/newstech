<?php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Core\Models\Concerns\HasSlugHelper;
use NewsTech\Core\Models\Concerns\HasSortableOrder;
use NewsTech\Core\Models\Concerns\HasStatusLabels;
use NewsTech\Core\Repositories\BaseRepository;
use Tests\TestCase;

class NewsTechCoreRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_base_repository_can_query_and_paginate_a_model(): void
    {
        AdminUser::factory()->count(3)->create();

        $repository = $this->makeAdminUserRepository();

        $this->assertSame(3, $repository->query()->count());
        $this->assertCount(2, $repository->paginate(2)->items());
    }

    public function test_base_repository_can_create_update_and_delete_records(): void
    {
        $repository = $this->makeAdminUserRepository();

        $adminUser = $repository->create([
            'name' => 'Desk Editor',
            'email' => 'desk@newstech.test',
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->assertModelExists($adminUser);
        $this->assertSame('Desk Editor', $repository->findOrFail($adminUser->getKey())->name);

        $updatedAdminUser = $repository->update($adminUser, [
            'name' => 'Senior Desk Editor',
        ]);

        $this->assertSame('Senior Desk Editor', $updatedAdminUser->name);

        $this->assertTrue($repository->delete($updatedAdminUser));
        $this->assertModelMissing($updatedAdminUser);
    }

    public function test_status_label_trait_returns_configured_and_fallback_labels(): void
    {
        $model = new class extends Model
        {
            use HasStatusLabels;

            protected $guarded = [];

            protected const STATUS_LABELS = [
                'draft' => 'Draft Review',
            ];
        };

        $model->setAttribute('status', 'draft');
        $this->assertSame('Draft Review', $model->getStatusLabel());

        $model->setAttribute('status', 'scheduled_publish');
        $this->assertSame('Scheduled Publish', $model->getStatusLabel());
    }

    public function test_slug_helper_trait_can_fill_slug_from_a_source_attribute(): void
    {
        $model = new class extends Model
        {
            use HasSlugHelper;

            protected $guarded = [];
        };

        $model->setAttribute('title', 'Weekend Market Rally Extends');
        $model->fillSlugFrom('title');

        $this->assertSame('weekend-market-rally-extends', $model->getAttribute('slug'));
    }

    public function test_sortable_order_trait_supports_ordering_and_next_order_resolution(): void
    {
        AdminUser::factory()->count(3)->create();

        $sortableAdminUser = new class extends AdminUser
        {
            use HasSortableOrder;

            protected $table = 'admin_users';
        };

        $orderedIds = $sortableAdminUser::query()->ordered('id', 'desc')->pluck('id')->all();

        $expectedIds = AdminUser::query()->orderByDesc('id')->pluck('id')->all();
        $expectedNextId = (int) AdminUser::query()->max('id') + 1;

        $this->assertSame($expectedIds, $orderedIds);
        $this->assertSame($expectedNextId, $sortableAdminUser::resolveNextSortOrder(AdminUser::query(), 'id'));
    }

    /**
     * @return BaseRepository<AdminUser>
     */
    protected function makeAdminUserRepository(): BaseRepository
    {
        return new class(app()) extends BaseRepository
        {
            protected function modelClass(): string
            {
                return AdminUser::class;
            }
        };
    }
}
