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
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('request_id')->nullable();
            $table->string('level'); 
            $table->string('exception_class')->nullable();
            $table->text('message')->nullable();
            $table->string('exception_code')->nullable();
            $table->string('source_file')->nullable();
            $table->integer('source_line')->nullable();
            $table->longText('stack_trace')->nullable();
            $table->json('previous_exception')->nullable();
            $table->json('performance')->nullable();
            $table->string('hostname')->nullable();
            $table->timestamps();

            // index
            $table->index('user_id');
            $table->index('request_id');
            $table->index('level');
            $table->index('created_at');

            // foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('request_id')->references('id')->on('request_logs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('error_logs');
    }
};
