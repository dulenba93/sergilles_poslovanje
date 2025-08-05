<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->enum('tip_placanja', ['FIRMA', 'KES'])->default('KES');
        });
    }

    public function down(): void {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn('tip_placanja');
        });
    }
};
