<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite stores enums as CHECK constraints; Laravel's change() is the
        // portable way to widen it.
        Schema::table('lands', function (Blueprint $table) {
            $table->string('status')->default('available')->change();
        });
    }

    public function down(): void
    {
        Schema::table('lands', function (Blueprint $table) {
            $table->enum('status', ['available', 'sold', 'reserved'])->default('available')->change();
        });
    }
};
