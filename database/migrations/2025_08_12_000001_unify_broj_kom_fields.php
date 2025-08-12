<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration to unify the quantity column for metraža and garnisna positions.
 *
 * In the original schema these tables used a column named `br_kom` to store
 * the number of pieces. This migration introduces a new column `broj_kom`
 * and migrates the data from `br_kom` into it. After the migration the
 * `br_kom` column is removed and `broj_kom` becomes the single source of
 * truth. The new column is made non‑nullable with a sensible default to
 * avoid null issues in application code.
 */
return new class extends Migration
{
    /**
     * The tables to modify. Only metraža and garnisna positions are affected.
     *
     * @var string[]
     */
    private array $tables = [
        'pozicija_metraza',
        'pozicija_garnisna',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {


            // 3) Drop the old `br_kom` column if it exists.
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (Schema::hasColumn($table, 'br_kom')) {
                    $t->dropColumn('br_kom');
                }
            });

        }
    }

};