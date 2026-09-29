<x-cetak-layout judul="Kalibrasi Template Kwitansi" :paper="config('cetak.kwitansi')">
<div class="page"><img class="bg" src="{{ $template }}" alt="Template kwitansi">@if($grid)<div class="grid"></div>@endif
    <div style="position:absolute;left:15mm;top:15mm;background:#fff9;padding:2mm;font-size:9pt">PRATINJAU KALIBRASI · ganti template lewat menu admin</div>
</div>
</x-cetak-layout>
