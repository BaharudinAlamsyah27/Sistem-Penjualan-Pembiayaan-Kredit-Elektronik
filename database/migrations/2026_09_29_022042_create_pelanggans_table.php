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
        Schema::create('pelanggans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->string('kode', 30);
            $table->string('nama', 100);
            $table->string('no_hp', 30)->nullable();
            $table->text('no_ktp')->nullable(); // cast encrypted in Eloquent
            $table->text('alamat');
            $table->string('kabupaten', 100);
            $table->string('kecamatan', 100);
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cabang_id', 'kabupaten', 'kecamatan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggans');
    }
};
