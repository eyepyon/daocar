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
        Schema::create('full_calenders', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('UID');
            $table->integer('group_id')->nullable()->comment('');
            $table->string('all_day')->nullable()->comment('');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('start_str')->nullable()->comment('');
            $table->string('end_str')->nullable()->comment('');
            $table->string('title')->nullable()->comment('');
            $table->string('url')->nullable()->comment('');
            $table->string('class_names')->nullable()->comment('');
            $table->string('editable')->nullable()->comment('');
            $table->string('start_editable')->nullable()->comment('');
            $table->string('resource_editable')->nullable()->comment('');
            $table->string('display')->nullable()->comment('');
            $table->string('overlap')->nullable()->comment('');
            $table->string('constraint')->nullable()->comment('');
            $table->string('background_color')->nullable()->comment('');
            $table->string('border_color')->nullable()->comment('');
            $table->string('text_color')->nullable()->comment('');
            $table->string('description')->nullable()->comment('');
            $table->string('extended_props')->nullable()->comment('');
            $table->integer('delete_flg')->nullable()->comment('');
            $table->timestamp('deleted_at')->nullable()->comment('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('full_calenders');
    }
};
