<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds performance indexes for common query patterns:
     * - Posts: status filtering and homepage listing (status + published_at desc)
     * - Taggables/Categorizables: reverse polymorphic lookups
     */
    public function up(): void
    {
        // Posts table: optimize for scopePublished and homepage queries
        Schema::table('posts', function (Blueprint $table) {
            // Single-column index for status filtering (admin, draft queries)
            $table->index('status', 'posts_status_index');

            // Compound index for homepage query: WHERE status = 'published' ORDER BY published_at DESC
            // Note: (published_at, slug) index already exists for URL routing
            $table->index(['status', 'published_at'], 'posts_status_published_index');
        });

        // Taggables: optimize reverse lookup (find all tags for a post)
        // The morphs() helper creates taggable_id and taggable_type but doesn't always
        // create the reverse lookup index we need for efficient queries
        Schema::table('taggables', function (Blueprint $table) {
            $table->index(['taggable_type', 'taggable_id'], 'taggables_reverse_lookup');
        });

        // Categorizables: same pattern as taggables
        Schema::table('categorizables', function (Blueprint $table) {
            $table->index(['categorizable_type', 'categorizable_id'], 'categorizables_reverse_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_status_index');
            $table->dropIndex('posts_status_published_index');
        });

        Schema::table('taggables', function (Blueprint $table) {
            $table->dropIndex('taggables_reverse_lookup');
        });

        Schema::table('categorizables', function (Blueprint $table) {
            $table->dropIndex('categorizables_reverse_lookup');
        });
    }
};
