<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laravelai_conversation_histories', function (Blueprint $table) {
            $table->id();
            $table->uuid('conversation_id')->index()->nullable();
            $table->string('provider');
            $table->string('model')->nullable();
            $table->string('role');
            $table->longText('content');
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
        Schema::dropIfExists('laravelai_conversation_histories');
    }
}; 