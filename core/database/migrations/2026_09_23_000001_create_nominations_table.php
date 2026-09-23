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
        Schema::create('nominations', function (Blueprint $table) {
            $table->id();
            $table->string('company')->nullable();
            $table->string('contact')->nullable();
            $table->string('jobtitle')->nullable();
            $table->string('email')->nullable();
            $table->string('confirm_email')->nullable();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->text('description')->nullable();
            $table->text('statement')->nullable();
            $table->string('category')->nullable();
            $table->string('subcategory')->nullable();
            $table->boolean('consent1')->default(false);
            $table->boolean('consent2')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nominations');
    }
};
