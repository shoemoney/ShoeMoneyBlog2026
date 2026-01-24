<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wordpress_id')->unique();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $table->string('author_name');
            $table->string('author_email');
            $table->string('author_url')->nullable();
            $table->string('author_ip', 45)->nullable();
            $table->text('content');
            $table->string('status')->default('approved'); // approved, pending, spam
            $table->timestamps();

            // Critical indexes for 160K+ comments
            $table->index('post_id');
            $table->index('parent_id');
            $table->index('status');
            $table->index(['post_id', 'status', 'created_at']); // For displaying approved comments chronologically
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
