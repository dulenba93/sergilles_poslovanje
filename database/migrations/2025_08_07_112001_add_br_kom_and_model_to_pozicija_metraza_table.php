<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('pozicija_metraza', function (Blueprint $table) {
        $table->integer('br_kom')->default(1)->after('id');
        $table->string('model')->nullable()->after('br_kom');
    });
}

public function down()
{
    Schema::table('pozicija_metraza', function (Blueprint $table) {
        $table->dropColumn(['br_kom', 'model']);
    });
}

};
