<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('work_order_positions', function (Blueprint $table) {
            $table->string('pozicija_type', 50)->change();
        });
    }

    public function down(): void {
        Schema::table('work_order_positions', function (Blueprint $table) {
            $table->string('pozicija_type', 20)->change(); // ili koliko je bilo
        });
    }
};