<?php

namespace NewsTech\Page\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use NewsTech\Core\Models\Concerns\HasSlugHelper;
use NewsTech\Page\Database\Factories\PageFactory;

class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    use HasSlugHelper;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'status',
        'meta_title',
        'meta_description',
        'focus_keyword',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function statusLabel(): string
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    public function statusTone(): string
    {
        return $this->status ? 'success' : 'neutral';
    }

    protected static function newFactory(): PageFactory
    {
        return PageFactory::new();
    }
}
