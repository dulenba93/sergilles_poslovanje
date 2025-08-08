<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {


        Schema::table('pozicija_garnisna', function (Blueprint $table) {
            $table->renameColumn('br_kom', 'broj_kom');
        });
    }

    public function down(): void {

        Schema::table('pozicija_garnisna', function (Blueprint $table) {
            $table->renameColumn('broj_kom', 'br_kom');
        });
    }
};
