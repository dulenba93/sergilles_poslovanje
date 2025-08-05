<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('code')->nullable()->after('id');
            $table->enum('type', [
                'METRAZA',
                'GARNISNE',
                'ROLO',
                'ZEBRA',
                'PLISE',
                'KOMARNICI',
                'PAKETO',
                'USLUGA',
            ])->default('USLUGA')->after('code');
        });
    }

    public function down(): void {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['code', 'type']);
        });
    }
};
