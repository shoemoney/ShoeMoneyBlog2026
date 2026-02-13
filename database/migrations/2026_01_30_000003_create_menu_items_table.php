<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('url')->nullable();
            $table->string('type')->default('custom'); // custom, page, category
            $table->nullableMorphs('linkable');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('menu_items')->nullOnDelete();
            $table->index('position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
