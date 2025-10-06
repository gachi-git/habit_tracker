<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('habit_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habit_id')->constrained()->onDelete('cascade');
            $table->date('recorded_date');
            $table->boolean('completed')->default(true);
            $table->text('note')->nullable();
            $table->integer('duration_minutes')->nullable(); // 実施時間（分単位）
            $table->timestamps();

            $table->unique(['habit_id', 'recorded_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habit_records');
    }
};