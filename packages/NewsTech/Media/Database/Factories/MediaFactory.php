<?php

namespace NewsTech\Media\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use NewsTech\Media\Models\Media;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        $originalName = $this->faker->slug().'.jpg';
        $filename = Str::random(12).'.jpg';
        $path = 'newstech/media/'.$filename;

        return [
            'disk' => 'public',
            'path' => $path,
            'filename' => $filename,
            'original_name' => $originalName,
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => 120_000,
            'alt_text' => $this->faker->sentence(4),
            'caption' => $this->faker->sentence(),
            'uploaded_by' => null,
        ];
    }
}
