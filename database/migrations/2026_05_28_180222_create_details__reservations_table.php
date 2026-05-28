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
        Schema::create('details_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservations_id');
            $table->unsignedBigInteger('tables_id');

            $table->timestamps();

            $table->foreign('reservations_id')->references('id')->on('reservations')->onDelete('cascade');
            $table->foreign('tables_id')->references('id')->on('tables')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('details_reservations');
    }
};
