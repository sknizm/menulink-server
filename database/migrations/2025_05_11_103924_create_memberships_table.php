<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\MembershipStatus;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('restaurant_id')->unique();
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            $table->string('plan_id');
            $table->enum('status', array_column(MembershipStatus::cases(), 'value'))->default(MembershipStatus::ACTIVE->value);
            $table->timestamp('start_date')->useCurrent();
            $table->dateTime('end_date');         // <-- fixed
            $table->dateTime('renews_at');        // <-- fixed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
