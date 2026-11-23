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
        Schema::create('themes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->string('panel', 16)->default('shop');
            $table->string('name');
            $table->string('version')->nullable();
            $table->string('parent')->nullable();
            $table->string('assets_path');
            $table->string('views_path');
            $table->json('vite')->nullable();
            $table->json('manifest')->nullable();
            $table->string('source', 32)->default('upload');
            $table->boolean('status')->default(1);
            $table->timestamps();

            $table->unique(['code', 'panel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
