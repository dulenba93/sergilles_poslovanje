<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id')->nullable()->after('id');
            // Ako hoćeš FK constraint:
            // $table->foreign('vendor_id')->references('id')->on('vendors')->nullOnDelete();
        });

        // Postavi vendor_id = 4 za sve postojeće proizvode
        \DB::table('products')->update(['vendor_id' => 4]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Ako si pravio FK constraint, prvo ga drop-uj:
            // $table->dropForeign(['vendor_id']);
            $table->dropColumn('vendor_id');
        });
    }
};
