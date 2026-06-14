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
        Schema::create('claim_headers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->dateTime('approved_at', 6)->nullable();
            $table->unsignedBigInteger('payer_id')->nullable();
            $table->dateTime('paid_at', 6)->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->dateTime('rejected_at', 6)->nullable();
            $table->string('name');
            $table->text('remark')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(1);
            $table->string('created_by', 350);
            $table->dateTime('created_at', 6);
            $table->string('updated_by', 350);
            $table->dateTime('updated_at', 6);

            // index
            $table->index('user_id');
            $table->index('approver_id');
            $table->index('payer_id');
            $table->index('name');
            $table->index('is_active');

            // foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('payer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_headers');
    }
};
