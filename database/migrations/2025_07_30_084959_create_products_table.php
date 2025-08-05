<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('code');
        $table->string('name');
        $table->text('description')->nullable();
        $table->decimal('purchase_price', 10, 2);
        $table->decimal('sale_price', 10, 2);
        $table->unsignedBigInteger('category_id');
        $table->string('model_label')->nullable();
        $table->integer('max_height')->nullable();
        $table->string('composition')->nullable();
        $table->timestamps();
        $table->softDeletes();

        $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
