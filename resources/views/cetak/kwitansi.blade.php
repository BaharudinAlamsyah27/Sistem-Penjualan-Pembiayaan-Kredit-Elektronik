<x-cetak-layout judul="Kwitansi Angsuran" :paper="config('cetak.kwitansi')">
<div class="page">
    @if($template)<img class="bg" src="{{ $template }}" alt="Template kwitansi">@endif
    @php($fields=config('cetak.kwitansi.fields'))
    <div class="field" style="left:{{ $fields['tanggal']['x'] }}mm;top:{{ $fields['tanggal']['y'] }}mm">{{ $angsuran->tgl_bayar?->format('d/m/Y') }}</div>
    <div class="field" style="left:{{ $fields['faktur']['x'] }}mm;top:{{ $fields['faktur']['y'] }}mm">{{ $angsuran->faktur->no_faktur }}</div>
    <div class="field" style="left:{{ $fields['pelanggan']['x'] }}mm;top:{{ $fields['pelanggan']['y'] }}mm">{{ $angsuran->faktur->salesOrder->pelanggan->nama }}</div>
    <div class="field" style="left:{{ $fields['jumlah']['x'] }}mm;top:{{ $fields['jumlah']['y'] }}mm">Rp {{ number_format((float)$angsuran->nominal_dibayar,0,',','.') }}</div>
    <div class="field" style="left:{{ $fields['baris']['x'] }}mm;top:{{ $fields['baris']['y'] }}mm;width:{{ $fields['baris']['width'] }}mm">{{ $angsuran->angsuran_ke }} &nbsp; {{ $angsuran->jatuh_tempo->format('d/m/Y') }} &nbsp; Rp {{ number_format((float)$angsuran->nominal_dibayar,0,',','.') }}</div>
    <div class="field" style="left:{{ $fields['total']['x'] }}mm;top:{{ $fields['total']['y'] }}mm">Rp {{ number_format((float)$angsuran->nominal_dibayar,0,',','.') }}</div>
</div>
</x-cetak-layout>
