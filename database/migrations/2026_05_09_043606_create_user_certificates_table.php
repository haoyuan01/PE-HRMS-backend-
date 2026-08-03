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
        Schema::create('user_certificates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('name')->nullable();
            $table->string('organization')->nullable();
            $table->text('description')->nullable();
            $table->date('date_applied')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('attachment_path')->nullable();
            $table->boolean('is_active')->default(1);
            $table->string('created_by', 350);
            $table->dateTime('created_at', 6);
            $table->string('updated_by', 350);
            $table->dateTime('updated_at', 6);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_certificates');
    }
};
