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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->string('no_so', 50);
            $table->date('tanggal');
            $table->foreignId('pelanggan_id')->constrained('pelanggans');
            $table->foreignId('barang_id')->constrained('barangs');
            $table->integer('qty')->default(1);
            $table->foreignId('sales_id')->constrained('karyawans'); // karyawan jabatan sales
            $table->decimal('harga_jual', 15, 2);
            $table->decimal('dp', 15, 2);
            $table->integer('tenor'); // bulan
            $table->decimal('bunga_persen', 5, 2); // % per bulan
            $table->decimal('pokok_piutang', 15, 2);
            $table->decimal('total_margin', 15, 2);
            $table->decimal('total_kredit', 15, 2);
            $table->decimal('angsuran_per_bulan', 15, 2);
            $table->enum('status', ['input', 'survey', 'disetujui', 'ditolak', 'terkirim', 'batal'])->default('input');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cabang_id', 'status']);
            $table->index(['cabang_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
