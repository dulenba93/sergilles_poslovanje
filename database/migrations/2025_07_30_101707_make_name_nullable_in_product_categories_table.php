<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends \Illuminate\Database\Migrations\Migration {
    public function up(): void
    {
Schema::table('categories', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
        });
    }

    public function down(): void
    {
Schema::table('categories', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
        });
    }
};
