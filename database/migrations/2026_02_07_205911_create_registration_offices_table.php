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
        Schema::create('registration_offices', function (Blueprint $table) {
            $table->id();
            $table->string('office_name');
            $table->string('location');
            $table->string('district');
            $table->string('region');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_offices');
    }
};
