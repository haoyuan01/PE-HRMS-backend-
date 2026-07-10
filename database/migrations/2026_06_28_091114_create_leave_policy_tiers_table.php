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
        Schema::create('leave_policy_tiers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('leave_policy_id');

            $table->integer('service_year_from')->default(0);
            $table->integer('service_year_to')->nullable();
            $table->decimal('entitlement_days', 8, 2)->default(0);

            $table->boolean('is_active')->default(true);
            $table->string('created_by', 350);
            $table->dateTime('created_at', 6);
            $table->string('updated_by', 350);
            $table->dateTime('updated_at', 6);

            // index
            $table->index('leave_policy_id');
            $table->index('is_active');

            // foreign key
            $table->foreign('leave_policy_id')->references('id')->on('leave_policies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_policy_tiers');
    }
};
