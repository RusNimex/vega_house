<?php

use App\Enums\SubtaskStatus;
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
        Schema::create('tasks_subtasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')
                ->constrained('tasks')
                ->onDelete('cascade');
            $table->text('target');
            $table->enum('status', SubtaskStatus::toArray())
                ->default(SubtaskStatus::NEW->value);
            $table->timestamps();
        });

        DB::statement("ALTER TABLE `tasks_subtasks` COMMENT 'Миссии к задачам. Имеют свой статус.'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks_subtasks');
    }
};
