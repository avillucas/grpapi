<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->integer('age')->nullable();
            $table->enum('type', ['cat', 'dog'])->nullable();
            $table->string('breed')->nullable();
            $table->enum('size', ['small', 'medium', 'large'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->dropColumn(['age', 'type', 'breed', 'size']);
        });
    }
};
