<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('work_order_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->enum('pozicija_type', ['metraza', 'garnisna']); // tip pozicije
            $table->unsignedBigInteger('pozicija_id');              // ID iz tabele metraza/garnisna
            $table->string('naziv')->nullable();                    // korisnički unos (npr. "Dnevna soba")
            $table->text('napomena')->nullable();                   // dodatna napomena
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('work_order_positions');
    }
};
