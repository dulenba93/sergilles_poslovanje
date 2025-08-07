<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pozicija_plise', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('sirina', 8, 2);
            $table->decimal('visina', 8, 2);
            $table->enum('mehanizam', ['mini', 'standard']);
            $table->decimal('broj_kom', 8, 2);
            $table->enum('potez', ['levo', 'desno']);
            $table->string('maska_boja')->nullable();
            $table->text('napomena')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pozicija_plise');
    }
};