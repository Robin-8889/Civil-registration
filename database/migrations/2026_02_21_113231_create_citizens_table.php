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
        Schema::create('citizens', function (Blueprint $table) {
            $table->id();

            // Reference to birth record
            $table->unsignedBigInteger('birth_record_id')->nullable();

            // Personal Information
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('gender', ['M', 'F']);
            $table->date('date_of_birth');

            // Birth Details
            $table->string('birth_certificate_no')->unique();
            $table->string('place_of_birth');
            $table->date('birth_registration_date');

            // Family Information
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('nationality')->nullable();

            // Registration Details
            $table->unsignedBigInteger('registration_office_id');
            $table->string('region');
            $table->enum('record_status', ['pending', 'registered', 'rejected'])->default('pending');

            // Marriage Information
            $table->boolean('is_married')->default(false);
            $table->unsignedBigInteger('marriage_record_id')->nullable();
            $table->string('marriage_certificate_no')->nullable();
            $table->date('marriage_date')->nullable();

            // Death Information
            $table->boolean('is_dead')->default(false);
            $table->unsignedBigInteger('death_record_id')->nullable();
            $table->string('death_certificate_no')->nullable();
            $table->date('death_date')->nullable();

            // Metadata
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('birth_record_id')->references('id')->on('birth_records')->onDelete('set null');
            $table->foreign('registration_office_id')->references('id')->on('registration_offices')->onDelete('cascade');
            $table->foreign('marriage_record_id')->references('id')->on('marriage_records')->onDelete('set null');
            $table->foreign('death_record_id')->references('id')->on('death_records')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citizens');
    }
};
