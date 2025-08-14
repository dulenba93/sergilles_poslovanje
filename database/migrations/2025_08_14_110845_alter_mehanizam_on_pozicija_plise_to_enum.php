<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Set existing loše/NULL vrednosti na 'standard' pre izmene (opciono ali korisno)
        DB::statement("UPDATE `pozicija_plise` SET `mehanizam` = 'standard' WHERE `mehanizam` IS NULL OR `mehanizam` NOT IN ('standard','zabice','lepljenje')");

        // Promena kolone na ENUM
        DB::statement("
            ALTER TABLE `pozicija_plise`
            MODIFY `mehanizam` ENUM('standard','zabice','lepljenje') NOT NULL DEFAULT 'standard'
        ");
    }

    public function down(): void
    {
        // Ako želiš rollback na VARCHAR(50)
        DB::statement("
            ALTER TABLE `pozicija_plise`
            MODIFY `mehanizam` VARCHAR(50) NOT NULL DEFAULT 'standard'
        ");
    }
};
