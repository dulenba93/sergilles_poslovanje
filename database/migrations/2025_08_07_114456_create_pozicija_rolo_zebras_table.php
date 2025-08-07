<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
       Schema::create('pozicija_rolo_zebra', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    // Sirina roloa/zebra (m)
    $table->decimal('sirina', 8, 3);
    // Visina roloa/zebra (m)
    $table->decimal('visina', 8, 2);
    // Odnos širine: mehanizam ili platno
    $table->enum('sirina_type', ['mehanizam', 'platno'])->default('mehanizam');
    $table->enum('mehanizam', ['mini', 'standard']);
    $table->decimal('broj_kom', 8, 2);
    $table->enum('potez', ['levo', 'desno']);
    $table->enum('kacenje', ['plafon', 'zid', 'pvc_kacenje']);
    $table->string('maska_boja')->nullable();
    $table->text('napomena')->nullable();
    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('pozicija_rolo_zebra');
    }
};