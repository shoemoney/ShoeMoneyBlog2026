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
        Schema::table('users', function (Blueprint $table) {
            $table->string('author_name')->nullable()->after('name');
            $table->unsignedBigInteger('wordpress_id')->nullable()->unique()->after('author_name');
            $table->string('role')->default('author')->after('wordpress_id'); // administrator, editor, author
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['author_name', 'wordpress_id', 'role']);
        });
    }
};
