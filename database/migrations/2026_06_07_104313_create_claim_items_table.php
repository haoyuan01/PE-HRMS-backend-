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
        Schema::create('claim_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('claim_header_id');
            $table->string('name', 350);
            $table->decimal('amount', 10, 2);
            $table->date('date')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('remark')->nullable();
            $table->boolean('is_active')->default(1);
            $table->string('created_by', 350);
            $table->dateTime('created_at', 6);
            $table->string('updated_by', 350);
            $table->dateTime('updated_at', 6);

            // index
            $table->index('claim_header_id');
            $table->index('name');
            $table->index('is_active');

            // foreign key
            $table->foreign('claim_header_id')->references('id')->on('claim_headers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_items');
    }
};
