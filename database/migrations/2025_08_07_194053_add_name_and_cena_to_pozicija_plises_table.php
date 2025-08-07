<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('pozicija_plise', function (Blueprint $table) {
        $table->string('name')->nullable()->after('product_id');
                $table->string('model')->nullable()->after('name');
        $table->decimal('cena', 10, 2)->nullable()->after('name');
    });
}

public function down()
{
    Schema::table('pozicija_plise', function (Blueprint $table) {
        $table->dropColumn(['name', 'model', 'cena']);
    });
}
};
