<?php

namespace Database\Seeders;

use App\Models\Angsuran;
use App\Models\Barang;
use App\Models\Cabang;
use App\Models\Faktur;
use App\Models\Karyawan;
use App\Models\Pelanggan;
use App\Models\SalesOrder;
use App\Models\Survey;
use App\Models\User;
use App\Services\KreditService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DashboardDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@nexa.com')->firstOrFail();
        $barang = Barang::where('kode', 'BRG-KLK-01')->firstOrFail();
        $today = Carbon::today();

        $pusat = Cabang::where('kode', 'CAB-PUSAT')->firstOrFail();
        $gesi = Cabang::where('kode', 'CAB-GESI')->firstOrFail();
        $gemolong = Cabang::where('kode', 'CAB-GEMOLONG')->firstOrFail();

        $this->buatSoTerkirim(
            cabang: $pusat,
            pelanggan: Pelanggan::where('kode', 'PLG-SL-001')->firstOrFail(),
            barang: $barang,
            admin: $admin,
            noSo: 'SO-DEMO-PS-001',
            noFaktur: 'INV-202609-101',
            tanggalSo: $today->copy()->startOfMonth()->addDays(2),
            angsuranDueKe: null,
        );

        $this->buatSoTerkirim(
            cabang: $gesi,
            pelanggan: Pelanggan::where('kode', 'PLG-GS-001')->firstOrFail(),
            barang: $barang,
            admin: $admin,
            noSo: 'SO-DEMO-GS-001',
            noFaktur: 'INV-202609-001',
            tanggalSo: $today->copy()->startOfMonth()->addDays(3),
            angsuranDueKe: 3,
            statusDueHariIni: 'menunggu_pembayaran',
            tenor: 6,
        );

        $this->buatSoTerkirim(
            cabang: $gemolong,
            pelanggan: Pelanggan::where('kode', 'PLG-GM-001')->firstOrFail(),
            barang: $barang,
            admin: $admin,
            noSo: 'SO-DEMO-GM-001',
            noFaktur: 'INV-202609-002',
            tanggalSo: $today->copy()->startOfMonth()->addDays(4),
            angsuranDueKe: 1,
            statusDueHariIni: 'lunas',
            tenor: 12,
        );

        $this->buatSoTerkirim(
            cabang: $pusat,
            pelanggan: Pelanggan::where('kode', 'PLG-SL-001')->firstOrFail(),
            barang: $barang,
            admin: $admin,
            noSo: 'SO-DEMO-PS-002',
            noFaktur: 'INV-202608-050',
            tanggalSo: $today->copy()->subMonthNoOverflow()->startOfMonth()->addDays(10),
            angsuranDueKe: null,
            tenor: 10,
        );

        $this->buatSoSurvey($gesi, Pelanggan::where('kode', 'PLG-GS-001')->firstOrFail(), $barang, $admin, 'SO-DEMO-GS-SVY-01');
        $this->buatSoSurvey($gemolong, Pelanggan::where('kode', 'PLG-GM-001')->firstOrFail(), $barang, $admin, 'SO-DEMO-GM-SVY-01');
        $this->buatSoSurvey($pusat, Pelanggan::where('kode', 'PLG-SL-001')->firstOrFail(), $barang, $admin, 'SO-DEMO-PS-SVY-01');
        $this->buatSoSurvey($gesi, Pelanggan::where('kode', 'PLG-GS-001')->firstOrFail(), $barang, $admin, 'SO-DEMO-GS-SVY-02');
    }

    private function buatSoTerkirim(
        Cabang $cabang,
        Pelanggan $pelanggan,
        Barang $barang,
        User $admin,
        string $noSo,
        string $noFaktur,
        Carbon $tanggalSo,
        ?int $angsuranDueKe,
        string $statusDueHariIni = 'belum',
        int $tenor = 12,
    ): void {
        if (SalesOrder::withoutGlobalScopes()->where('no_so', $noSo)->exists()) {
            return;
        }

        $sales = Karyawan::where('cabang_id', $cabang->id)->where('jabatan', 'sales')->firstOrFail();
        $kolektor = Karyawan::where('cabang_id', $cabang->id)->where('jabatan', 'kolektor')->firstOrFail();

        $harga = (float) $barang->harga_jual;
        $dp = 800000;
        $bunga = 1.5;
        $hit = KreditService::hitung($harga, $dp, $tenor, $bunga);
        $totalKredit = (float) $hit['total_piutang'];
        $angsuranBulanan = (float) $hit['angsuran_per_bulan'];

        $so = SalesOrder::create([
            'cabang_id' => $cabang->id,
            'no_so' => $noSo,
            'tanggal' => $tanggalSo,
            'pelanggan_id' => $pelanggan->id,
            'barang_id' => $barang->id,
            'qty' => 1,
            'sales_id' => $sales->id,
            'harga_jual' => $harga,
            'dp' => $dp,
            'tenor' => $tenor,
            'bunga_persen' => $bunga,
            'pokok_piutang' => $hit['pokok_piutang'],
            'total_margin' => $hit['total_margin'],
            'total_kredit' => $totalKredit,
            'angsuran_per_bulan' => $angsuranBulanan,
            'status' => 'terkirim',
            'created_by' => $admin->id,
        ]);

        $tglDp = $angsuranDueKe
            ? Carbon::today()->subMonthsNoOverflow($angsuranDueKe)
            : $tanggalSo->copy();

        $faktur = Faktur::create([
            'cabang_id' => $cabang->id,
            'sales_order_id' => $so->id,
            'no_faktur' => $noFaktur,
            'tanggal' => $tanggalSo,
            'tgl_dp' => $tglDp,
            'status' => 'aktif',
        ]);

        Angsuran::create([
            'cabang_id' => $cabang->id,
            'faktur_id' => $faktur->id,
            'jenis' => 'dp',
            'angsuran_ke' => 0,
            'nominal' => $dp,
            'jatuh_tempo' => $tglDp,
            'kolektor_id' => $kolektor->id,
            'status' => 'lunas',
            'tgl_bayar' => $tglDp,
            'nominal_dibayar' => $dp,
            'metode' => 'tunai',
            'dicatat_oleh' => $admin->id,
        ]);

        $sudah = 0;
        for ($i = 1; $i <= $tenor; $i++) {
            $nominal = ($i === $tenor) ? $totalKredit - $sudah : $angsuranBulanan;
            $sudah += $nominal;
            $jatuhTempo = $tglDp->copy()->addMonthsNoOverflow($i);

            $status = 'belum';
            $tglBayar = null;
            $dibayar = null;

            if ($angsuranDueKe !== null && $i === $angsuranDueKe) {
                $jatuhTempo = Carbon::today();
                if ($statusDueHariIni === 'lunas') {
                    $status = 'lunas';
                    $tglBayar = Carbon::today();
                    $dibayar = $nominal;
                } else {
                    $status = 'menunggu_pembayaran';
                }
            } elseif ($jatuhTempo->lt(Carbon::today())) {
                $status = 'lunas';
                $tglBayar = $jatuhTempo;
                $dibayar = $nominal;
            }

            Angsuran::create([
                'cabang_id' => $cabang->id,
                'faktur_id' => $faktur->id,
                'jenis' => 'angsuran',
                'angsuran_ke' => $i,
                'nominal' => $nominal,
                'jatuh_tempo' => $jatuhTempo,
                'kolektor_id' => $kolektor->id,
                'status' => $status,
                'tgl_bayar' => $tglBayar,
                'nominal_dibayar' => $dibayar,
                'metode' => $dibayar ? 'tunai' : null,
                'dicatat_oleh' => $dibayar ? $admin->id : null,
            ]);
        }
    }

    private function buatSoSurvey(Cabang $cabang, Pelanggan $pelanggan, Barang $barang, User $admin, string $noSo): void
    {
        if (SalesOrder::withoutGlobalScopes()->where('no_so', $noSo)->exists()) {
            return;
        }

        $sales = Karyawan::where('cabang_id', $cabang->id)->where('jabatan', 'sales')->firstOrFail();
        $surveyor = Karyawan::where('cabang_id', $cabang->id)->where('jabatan', 'surveyor')->firstOrFail();
        $hit = KreditService::hitung((float) $barang->harga_jual, 800000, 12, 1.5);

        $so = SalesOrder::create([
            'cabang_id' => $cabang->id,
            'no_so' => $noSo,
            'tanggal' => Carbon::today()->subDays(2),
            'pelanggan_id' => $pelanggan->id,
            'barang_id' => $barang->id,
            'qty' => 1,
            'sales_id' => $sales->id,
            'harga_jual' => $barang->harga_jual,
            'dp' => 800000,
            'tenor' => 12,
            'bunga_persen' => 1.5,
            'pokok_piutang' => $hit['pokok_piutang'],
            'total_margin' => $hit['total_margin'],
            'total_kredit' => $hit['total_piutang'],
            'angsuran_per_bulan' => $hit['angsuran_per_bulan'],
            'status' => 'survey',
            'created_by' => $admin->id,
        ]);

        Survey::create([
            'cabang_id' => $cabang->id,
            'sales_order_id' => $so->id,
            'surveyor_id' => $surveyor->id,
            'tanggal' => Carbon::today()->subDay(),
            'hasil' => 'menunggu',
            'catatan' => 'Menunggu hasil survey lapangan',
        ]);
    }
}
