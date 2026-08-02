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
        Schema::create('overtimes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('manager_approver_id');

            // manager
            $table->unsignedBigInteger('manager_action_by')->nullable();
            $table->dateTime('manager_action_at', 6)->nullable();
            $table->boolean('manager_approved')->default(0);
            $table->text('manager_remark')->nullable();

            // director
            $table->unsignedBigInteger('director_action_by')->nullable();
            $table->dateTime('director_action_at', 6)->nullable();
            $table->boolean('director_approved')->default(0);
            $table->text('director_remark')->nullable();

            $table->text('description')->nullable();
            $table->decimal('total_days', 8, 2)->nullable();
            $table->string('attachment_path')->nullable();
            $table->boolean('is_active')->default(1);
            $table->string('created_by', 350);
            $table->dateTime('created_at', 6);
            $table->string('updated_by', 350);
            $table->dateTime('updated_at', 6);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('manager_approver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('manager_action_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};
