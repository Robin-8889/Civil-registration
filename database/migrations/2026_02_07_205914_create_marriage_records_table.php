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
        Schema::create('marriage_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('groom_id');
            $table->unsignedBigInteger('bride_id');
            $table->unsignedBigInteger('registration_office_id');
            $table->string('marriage_certificate_no')->unique();
            $table->date('date_of_marriage');
            $table->string('place_of_marriage');
            $table->string('groom_name');
            $table->string('bride_name');
            $table->text('witnesses')->nullable();
            $table->date('registration_date');
            $table->enum('status', ['married', 'pending', 'annulled'])->default('pending');
            $table->timestamps();
            $table->foreign('groom_id')->references('id')->on('citizens')->onDelete('cascade');
            $table->foreign('bride_id')->references('id')->on('citizens')->onDelete('cascade');
            $table->foreign('registration_office_id')->references('id')->on('registration_offices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marriage_records');
    }
};
