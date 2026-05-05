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
        Schema::create('configurations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('key');                              // configuration key (e.g., app_name, app_version, etc.)
            $table->text('value')->nullable();                  // actual value (stored as string/json)
            $table->string('value_type')->default('string');    // string, integer, boolean, json, float
            $table->text('description')->nullable();            // optional description (for admin UI / docs)
            $table->boolean('is_editable')->default(true);      // whether this config can be edited via UI
            $table->boolean('is_viewable')->default(true);      // whether this config can be viewed via UI
            $table->timestamps();

            // index
            $table->index('key');
            $table->index('value_type');
            $table->index('is_editable');
            $table->index('is_viewable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configurations');
    }
};
