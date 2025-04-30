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
       // Crear tabla de tags
       Schema::create('tags', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    // Crear tabla intermedia entre Blogs y tags
    Schema::create('blog_tag', function (Blueprint $table) {
        $table->id();
        $table->foreignId('blog_id')->constrained()->onDelete('cascade');
        $table->foreignId('tag_id')->constrained()->onDelete('cascade');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar las tablas si se revierte la migración
        Schema::dropIfExists('blog_tag');
        Schema::dropIfExists('tags');
    }
};
