<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trello_board_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128);
            $table->timestamps();

            $table->unique(['name'], 'name_unique');
        });

        Schema::create('trello_boards', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128);
            $table->foreignId('trello_category_id')->constrained('trello_board_categories');
            $table->timestamps();

            $table->unique(['trello_category_id', 'name'], 'category_name_unique');
        });

        Schema::create('trello_columns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128);
            $table->integer('order');
            $table->foreignId('trello_board_id')->constrained('trello_boards');
            $table->timestamps();
        });

        Schema::create('trello_items', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128);
            $table->integer('order');
            $table->foreignId('trello_column_id')->constrained('trello_columns');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trello_items');
        Schema::dropIfExists('trello_columns');
        Schema::dropIfExists('trello_boards');
        Schema::dropIfExists('trello_board_categories');
    }
};
