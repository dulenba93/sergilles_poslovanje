<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('type')->after('id');
            $table->string('description')->nullable()->after('type');
            $table->decimal('amount', 15, 2)->after('description');
            $table->string('payment_type')->after('amount');
            $table->string('note')->nullable()->after('payment_type');
            $table->string('month')->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['type', 'description', 'amount', 'payment_type', 'note', 'month']);
        });
    }
};
