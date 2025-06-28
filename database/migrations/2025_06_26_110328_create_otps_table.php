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
        Schema::create('otps', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID primary key
            $table->string('email')->index(); // Email of the user who requested the OTP, indexed for quick lookups
            $table->string('otp_code'); // The actual OTP (e.g., a 6-digit number, can be string for leading zeros)
            $table->timestamp('expires_at'); // When the OTP code expires
            $table->timestamps(); // created_at and updated_at

            // Optional: Add a foreign key constraint if you want to link it directly to the users table
            // This assumes the 'email' column in 'users' is unique and you want to enforce integrity.
            // $table->foreign('email')->references('email')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
