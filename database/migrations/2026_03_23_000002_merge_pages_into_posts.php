<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add post_type and menu_order columns to posts table
        Schema::table('posts', function (Blueprint $table) {
            $table->string('post_type', 20)->default('post')->after('status');
            $table->integer('menu_order')->default(0)->after('post_type');
            $table->index('post_type');
        });

        // Step 2: Copy all pages into posts table
        if (Schema::hasTable('pages')) {
            $pages = DB::table('pages')->get();

            foreach ($pages as $page) {
                DB::table('posts')->insert([
                    'wordpress_id' => $page->wordpress_id,
                    'user_id' => $page->user_id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'content' => $page->content,
                    'excerpt' => null,
                    'status' => 'published',
                    'post_type' => 'page',
                    'menu_order' => $page->menu_order,
                    'published_at' => $page->created_at,
                    'created_at' => $page->created_at,
                    'updated_at' => $page->updated_at,
                ]);
            }

            // Step 3: Update polymorphic relationships (taggables, categorizables, featured_images)
            // Get a mapping of old page IDs to new post IDs
            foreach ($pages as $page) {
                $newPost = DB::table('posts')
                    ->where('post_type', 'page')
                    ->where('slug', $page->slug)
                    ->first();

                if ($newPost) {
                    // Update taggables
                    DB::table('taggables')
                        ->where('taggable_type', 'App\\Models\\Page')
                        ->where('taggable_id', $page->id)
                        ->update([
                            'taggable_type' => 'App\\Models\\Post',
                            'taggable_id' => $newPost->id,
                        ]);

                    // Update categorizables
                    DB::table('categorizables')
                        ->where('categorizable_type', 'App\\Models\\Page')
                        ->where('categorizable_id', $page->id)
                        ->update([
                            'categorizable_type' => 'App\\Models\\Post',
                            'categorizable_id' => $newPost->id,
                        ]);

                    // Update featured_images (polymorphic: imageable_type/imageable_id)
                    DB::table('featured_images')
                        ->where('imageable_type', 'App\\Models\\Page')
                        ->where('imageable_id', $page->id)
                        ->update([
                            'imageable_type' => 'App\\Models\\Post',
                            'imageable_id' => $newPost->id,
                        ]);
                }
            }

            // Step 4: Drop the pages table
            Schema::dropIfExists('pages');
        }
    }

    public function down(): void
    {
        // Recreate pages table
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wordpress_id')->nullable()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->integer('menu_order')->default(0);
            $table->timestamps();
            $table->index('slug');
        });

        // Move pages back from posts
        $pages = DB::table('posts')->where('post_type', 'page')->get();

        foreach ($pages as $page) {
            DB::table('pages')->insert([
                'wordpress_id' => $page->wordpress_id,
                'user_id' => $page->user_id,
                'title' => $page->title,
                'slug' => $page->slug,
                'content' => $page->content,
                'menu_order' => $page->menu_order,
                'created_at' => $page->created_at,
                'updated_at' => $page->updated_at,
            ]);
        }

        // Remove page rows from posts and drop new columns
        DB::table('posts')->where('post_type', 'page')->delete();

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['post_type']);
            $table->dropColumn(['post_type', 'menu_order']);
        });
    }
};
