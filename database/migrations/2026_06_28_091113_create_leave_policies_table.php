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
        Schema::create('leave_policies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // naming
            $table->string('name')->nullable();
            $table->string('code');
            $table->text('description')->nullable();

            // leave calculation
            $table->boolean('allow_half_day')->default(true);

            // carry forward
            $table->decimal('carry_forward_days', 8, 2)->default(0);
            $table->integer('carry_forward_expiry_month')->nullable();
            $table->integer('carry_forward_expiry_date')->nullable();

            // handover
            $table->boolean('is_handover_required')->default(false);
            $table->decimal('handover_min_days', 8, 2)->nullable();

            // application rules
            $table->integer('min_notice_days')->default(0);
            $table->boolean('requires_attachment')->default(false);

            // policy behavior
            $table->boolean('is_paid')->default(true);

            $table->boolean('is_active')->default(1);
            $table->string('created_by', 350);
            $table->dateTime('created_at', 6);
            $table->string('updated_by', 350);
            $table->dateTime('updated_at', 6);

            // index
            $table->index('name');
            $table->index('code');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_policies');
    }
};
