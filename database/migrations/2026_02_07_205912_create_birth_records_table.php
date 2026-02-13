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
        Schema::create('birth_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('child_id');
            $table->unsignedBigInteger('registration_office_id');
            $table->string('birth_certificate_no')->unique();
            $table->date('date_of_birth');
            $table->string('place_of_birth');
            $table->string('child_first_name');
            $table->string('child_middle_name')->nullable();
            $table->string('child_last_name');
            $table->enum('gender', ['M', 'F']);
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->date('registration_date');
            $table->enum('status', ['registered', 'pending', 'rejected'])->default('pending');
            $table->timestamps();
            $table->foreign('child_id')->references('id')->on('citizens')->onDelete('cascade');
            $table->foreign('registration_office_id')->references('id')->on('registration_offices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('birth_records');
    }
};
