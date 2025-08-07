<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('description')->nullable();
            $table->enum('type', [
                'Šivenje', 'Reklama', 'Gorivo', 'Nabavka',
                'Plate', 'Greške', 'Porezi', 'Op Troškovi'
            ]);
            $table->decimal('amount', 10, 2);
            $table->enum('payment_type', ['KES', 'FIRMA']);
            $table->text('note')->nullable();
            $table->string('month');
            $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::table('monthly_expenses', function (Blueprint $table) {
            $table->dropColumn(['type', 'description', 'amount', 'payment_type', 'note', 'month']);
        });
    }
};
