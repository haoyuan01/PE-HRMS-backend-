<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityLogTable extends Migration
{
    public function up()
    {
        Schema::connection(config('activitylog.database_connection'))->create(config('activitylog.table_name'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->string('log_name')->nullable();             // audit
            $table->string('event')->nullable();                // created, updated, deleted
            $table->text('description');                        // Record created
            $table->nullableMorphs('subject', 'subject');       // App\Models\Department & department_id from departments table
            $table->nullableMorphs('causer', 'causer');         // App\Models\User & user_id from users table        
            $table->json('properties')->nullable();             // recorded what is changes
            $table->json('old_values')->nullable();             // recorded old values
            $table->json('new_values')->nullable();             // recorded new values
            $table->json('performance')->nullable();            // recorded performance metrics
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();

            // index
            $table->index('log_name');
            $table->index('event');
            $table->index('description');
        });
    }

    public function down()
    {
        Schema::connection(config('activitylog.database_connection'))->dropIfExists(config('activitylog.table_name'));
    }
}
