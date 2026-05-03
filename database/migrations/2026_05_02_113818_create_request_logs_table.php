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
        Schema::create('request_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('method')->nullable();
            $table->string('path')->nullable();
            $table->json('files')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->string('ip')->nullable();
            $table->string('url')->nullable();
            $table->string('scheme')->nullable();
            $table->string('host')->nullable();
            $table->string('port')->nullable();
            $table->json('cookies')->nullable();
            $table->string('user_agent')->nullable();
            $table->integer('status_code')->nullable();
            $table->boolean('success')->default(false);
            $table->json('performance')->nullable();
            $table->timestamps();

            // index
            $table->index('user_id');
            $table->index('method');
            $table->index('url');
            $table->index('user_agent');
            $table->index('status_code');
            $table->index('success');

            // foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_logs');
    }
};
