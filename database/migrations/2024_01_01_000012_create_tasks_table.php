<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained('companies')
                ->onDelete('cascade');
            $table->enum('status', ['process', 'new', 'break', 'decline', 'complete'])
                ->default('new');
            $table->text('description');
            $table->dateTime('start');
            $table->dateTime('deadline');
            $table->string('address');
            $table->string('notes');
            $table->timestamps();
            
            $table->index('company_id');
            $table->index('status');
        });

        DB::statement("ALTER TABLE `tasks` COMMENT = 'Задачи компании по осмотрам объектов'");

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')
                ->constrained('tasks')
                ->onDelete('cascade');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            
            $table->index('task_id');
        });

        DB::statement("ALTER TABLE `contacts` COMMENT = 'Контакты к задачам'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('tasks');
    }
};

