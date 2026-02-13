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
        Schema::create('death_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deceased_id');
            $table->unsignedBigInteger('registration_office_id');
            $table->string('death_certificate_no')->unique();
            $table->date('date_of_death');
            $table->string('place_of_death');
            $table->string('deceased_name');
            $table->string('cause_of_death')->nullable();
            $table->string('informant_name');
            $table->string('informant_relation');
            $table->date('registration_date');
            $table->enum('status', ['registered', 'pending', 'rejected'])->default('pending');
            $table->timestamps();
            $table->foreign('deceased_id')->references('id')->on('citizens')->onDelete('cascade');
            $table->foreign('registration_office_id')->references('id')->on('registration_offices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('death_records');
    }
};
