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
        Schema::create('user_personals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('full_name', 350)->nullable();
            $table->string('first_name', 350)->nullable();
            $table->string('last_name', 350)->nullable();
            $table->string('identity_number')->nullable();
            $table->string('passport_number')->nullable();
            $table->date('passport_expiry_date')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('gender')->nullable();
            $table->boolean('is_married')->default(0);
            $table->boolean('is_active')->default(1);
            $table->string('created_by', 350);
            $table->dateTime('created_at', 6);
            $table->string('updated_by', 350);
            $table->dateTime('updated_at', 6);

            // index
            $table->index('user_id');
            $table->index('full_name');
            $table->index('first_name');
            $table->index('last_name');
            $table->index('identity_number');
            $table->index('passport_number');
            $table->index('gender');
            $table->index('is_married');
            $table->index('is_active');

            // foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_personals');
    }
};
