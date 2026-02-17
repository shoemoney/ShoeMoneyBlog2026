<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('featured_images', function (Blueprint $table) {
            $table->id();
            $table->morphs('imageable');
            $table->string('raw_url', 512)->nullable();
            $table->string('small_url', 512)->nullable();
            $table->string('medium_url', 512)->nullable();
            $table->string('large_url', 512)->nullable();
            $table->string('inline_url', 512)->nullable();
            $table->text('prompt_used')->nullable();
            $table->string('status')->default('pending');
            $table->tinyInteger('attempts')->unsigned()->default(0);
            $table->text('error_message')->nullable();
            $table->string('model_used')->nullable();
            $table->timestamps();

            $table->unique(['imageable_id', 'imageable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('featured_images');
    }
};
