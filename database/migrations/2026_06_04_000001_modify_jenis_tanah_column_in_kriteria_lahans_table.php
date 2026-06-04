<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use raw SQL to safely convert ENUM to VARCHAR in MySQL
        DB::statement('ALTER TABLE kriteria_lahans MODIFY jenis_tanah VARCHAR(255) NOT NULL');
    }

    public function down(): void
    {
        // Reverting back to original ENUM (might cause data truncation if new types exist)
        DB::statement("ALTER TABLE kriteria_lahans MODIFY jenis_tanah ENUM('Berpasir', 'Lempung', 'Liat') NOT NULL");
    }
};
