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
        Schema::create('fakturs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->string('no_faktur', 50);
            $table->date('tanggal');
            $table->date('tgl_dp');
            $table->enum('status', ['aktif', 'lunas', 'batal'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cabang_id', 'status']);
            $table->index(['cabang_id', 'tanggal']);
        });

        Schema::create('angsurans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->foreignId('faktur_id')->constrained('fakturs')->cascadeOnDelete();
            $table->enum('jenis', ['dp', 'angsuran']);
            $table->integer('angsuran_ke');
            $table->decimal('nominal', 15, 2);
            $table->date('jatuh_tempo');
            $table->foreignId('kolektor_id')->nullable()->constrained('karyawans')->nullOnDelete(); // jabatan kolektor
            $table->enum('status', ['belum', 'menunggu_pembayaran', 'lunas'])->default('belum');
            $table->date('tgl_bayar')->nullable();
            $table->decimal('nominal_dibayar', 15, 2)->nullable();
            $table->string('metode', 50)->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['faktur_id', 'jenis', 'angsuran_ke']);
            $table->index(['cabang_id', 'jatuh_tempo', 'status']);
        });

        Schema::create('surat_jalans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->string('no_ttb', 50);
            $table->foreignId('driver_id')->nullable()->constrained('karyawans')->nullOnDelete(); // jabatan driver
            $table->date('tgl_kirim')->nullable();
            $table->string('nota', 100)->nullable();
            $table->enum('status_verifikasi', ['menunggu', 'terverifikasi'])->default('menunggu');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cabang_id', 'status_verifikasi']);
        });

        Schema::create('nomor_dokumens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->string('jenis', 30); // SO, INV, TTB, dll
            $table->string('periode', 6); // YYYYMM
            $table->integer('terakhir')->default(0);
            $table->timestamps();

            $table->unique(['cabang_id', 'jenis', 'periode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomor_dokumens');
        Schema::dropIfExists('surat_jalans');
        Schema::dropIfExists('angsurans');
        Schema::dropIfExists('fakturs');
    }
};
