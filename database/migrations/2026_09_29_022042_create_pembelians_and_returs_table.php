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
        Schema::create('pembelians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->string('no_pembelian', 50);
            $table->date('tanggal');
            $table->string('supplier', 100);
            $table->enum('metode', ['cashmoney', 'tempo']);
            $table->decimal('total', 15, 2)->default(0);
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cabang_id', 'tanggal']);
        });

        Schema::create('pembelian_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembelian_id')->constrained('pembelians')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('barangs')->cascadeOnDelete();
            $table->integer('qty');
            $table->decimal('harga', 15, 2);
            $table->timestamps();
        });

        Schema::create('returs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->string('no_retur', 50);
            $table->date('tanggal');
            $table->enum('jenis', ['retur_pembelian', 'retur_penjualan']);
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->text('alasan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cabang_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returs');
        Schema::dropIfExists('pembelian_details');
        Schema::dropIfExists('pembelians');
    }
};
