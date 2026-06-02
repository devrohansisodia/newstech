<?php

namespace NewsTech\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use NewsTech\Core\Database\Factories\SystemSettingFactory;

class SystemSetting extends Model
{
    /** @use HasFactory<SystemSettingFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    protected static function newFactory(): SystemSettingFactory
    {
        return SystemSettingFactory::new();
    }
}
