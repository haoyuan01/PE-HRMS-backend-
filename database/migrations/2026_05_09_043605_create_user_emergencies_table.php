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
        Schema::create('user_emergencies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('name')->nullable();
            $table->string('phone_number', 25)->nullable();
            $table->string('relationship')->nullable();
            $table->boolean('is_active')->default(1);
            $table->string('created_by', 350);
            $table->dateTime('created_at', 6);
            $table->string('updated_by', 350);
            $table->dateTime('updated_at', 6);

            // index
            $table->index('name');
            $table->index('phone_number');
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
        Schema::dropIfExists('user_emergencies');
    }
};
