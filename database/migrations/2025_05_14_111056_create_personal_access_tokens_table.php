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
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // ✅ Ensures proper storage engine

            $table->bigIncrements('id'); // ✅ BIGINT UNSIGNED AUTO_INCREMENT
            $table->string('name');
            $table->text('token')->unique();
            $table->uuidMorphs('tokenable'); // tokenable_id (UUID) + tokenable_type + index
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
