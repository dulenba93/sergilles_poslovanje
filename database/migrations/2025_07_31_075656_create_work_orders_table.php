<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('phone');
            $table->text('address')->nullable();
            $table->enum('status', ['new', 'in_progress', 'done', 'cancelled'])->default('new');
            $table->dateTime('scheduled_at')->nullable();
            $table->decimal('total_price', 10, 2)->default(0);
            $table->decimal('advance_payment', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('work_orders');
    }
};
