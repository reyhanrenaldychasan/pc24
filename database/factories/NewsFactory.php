<?php

namespace Database\Factories;

use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NewsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = News::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title = $this->faker->sentences(3, true);
        return [
            'title'             => $title,
            'slug'              => Str::slug($title, '-'),
            'content'           => $this->faker->paragraphs(3, true),
            'image'             => $this->faker->imageUrl('cats'),
            'thumbnail_image'   => $this->faker->imageUrl(400, 300, 'cats'),
            'created_by'        => $this->faker->name,
            'published'         => rand(0, 1),
        ];
    }
}
