<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\News;
use App\Models\Tag;

class NewsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       News::factory()->count(3)->create();
       
       $tags = Tag::all();

       News::all()->each(function ($news) use ($tags) {
           $news->tags()->attach(
               $tags->random(rand(1, 3))->pluck('id')->toArray()
           );
       });
    }
}
