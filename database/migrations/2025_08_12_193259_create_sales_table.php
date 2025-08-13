<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable(); // P-{id}-{Y}
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();

            // tip prodaje, isti domen kao work_orders.type
            $table->enum('type', ['METRAZA','GARNISNE','ROLO','ZEBRA','PLISE','KOMARNICI','USLUGA'])->default('USLUGA');

            // jedinica (derivira se iz type, ali čuvamo i u bazi radi izveštaja)
            $table->string('unit', 10)->default('kol');

            // količina i cene
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->nullable();   // iz product.sale_price ili ručno
            $table->decimal('total_price', 14, 2)->default(0);  // quantity * unit_price (editabilno)
            $table->decimal('paid_amount', 14, 2)->default(0);  // "plaćeno do sad"

            // opis kupca / napomena
            $table->string('customer_description')->nullable();

            // tip plaćanja
            $table->enum('payment_type', ['KES','FIRMA'])->default('KES');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};