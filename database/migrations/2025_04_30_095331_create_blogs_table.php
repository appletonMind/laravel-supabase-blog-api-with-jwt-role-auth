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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique(); // Para la URL amigable
            $table->string('description')->nullable(); // Breve descripción
            $table->text('content'); // Cuerpo del blog
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('keywords')->nullable(); // Coma separadas
            $table->string('image_url')->nullable();
            $table->string('video_url')->nullable();
            $table->string('image_path_url');  // Ruta del archivo descargable
            $table->string('image_path')->nullable(); // Ruta local de la imagen subida
            $table->string('image_path_supabase')->nullable(); // Ruta local de la imagen subida
            $table->string('image_path_supabase_url')->nullable(); // Ruta local de la imagen subida
            $table->integer('file_size');  // Tamaño del archivo en bytes
            $table->string('file_type');  // Tipo de archivo (por ejemplo, 'pdf', 'zip', 'js')
            $table->boolean('is_available')->default(true);  // Estado del archivo (disponible o no)
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
