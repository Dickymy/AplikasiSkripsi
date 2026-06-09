<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekomendasi Pemupukan — {{ $rekomendasiRbs->blokLahan->nama_blok }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1e293b; line-height: 1.5; padding: 20px 30px; }

        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #059669; padding-bottom: 15px; }
        .header h1 { font-size: 16px; font-weight: 700; color: #059669; margin-bottom: 2px; }
        .header h2 { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px; }
        .header p { font-size: 10px; color: #6b7280; }

        .status-banner { padding: 10px 15px; border-radius: 6px; margin-bottom: 16px; text-align: center; }
        .status-darurat { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }
        .status-segera { background: #fff7ed; border: 1px solid #fdba74; color: #9a3412; }
        .status-normal { background: #f0fdf4; border: 1px solid #86efac; color: #166534; }
        .status-tunda { background: #f8fafc; border: 1px solid #cbd5e1; color: #475569; }
        .status-banner .label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; opacity: 0.7; }
        .status-banner .value { font-size: 16px; font-weight: 800; margin-top: 2px; }

        .section { margin-bottom: 14px; }
        .section-title { font-size: 11px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1px solid #e5e7eb; }

        .info-grid { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .info-grid td { padding: 5px 8px; border: 1px solid #e5e7eb; vertical-align: top; }
        .info-grid .label { color: #6b7280; font-size: 10px; width: 35%; background: #f9fafb; font-weight: 500; }
        .info-grid .value { color: #1e293b; font-weight: 600; }

        .two-col { width: 100%; margin-bottom: 12px; }
        .two-col td { width: 50%; vertical-align: top; padding-right: 10px; }

        .pupuk-card { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 8px 10px; margin-bottom: 6px; }
        .pupuk-card .nama { font-size: 12px; font-weight: 700; color: #15803d; margin-bottom: 3px; }
        .pupuk-card .detail { font-size: 10px; color: #4b5563; }

        .logistik-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .logistik-table th, .logistik-table td { border: 1px solid #d1d5db; padding: 6px 10px; text-align: center; }
        .logistik-table th { background: #f3f4f6; font-size: 10px; font-weight: 700; color: #374151; text-transform: uppercase; }
        .logistik-table td { font-size: 12px; font-weight: 600; }
        .logistik-table .urea { color: #92400e; }
        .logistik-table .kcl { color: #0e7490; }

        .masalah-list { margin-bottom: 8px; }
        .masalah-item { display: inline-block; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 4px; padding: 2px 6px; font-size: 10px; margin: 2px 2px 2px 0; color: #475569; }

        .saran-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 8px 10px; margin-bottom: 10px; }
        .saran-box .title { font-size: 10px; font-weight: 700; color: #92400e; text-transform: uppercase; margin-bottom: 4px; }
        .saran-box .text { font-size: 11px; color: #78350f; line-height: 1.6; }

        .catatan-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 8px 10px; margin-bottom: 10px; }
        .catatan-box .title { font-size: 10px; font-weight: 700; color: #1e40af; text-transform: uppercase; margin-bottom: 4px; }
        .catatan-box .text { font-size: 11px; color: #1e3a5f; line-height: 1.6; }

        .rules-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .rules-table th, .rules-table td { border: 1px solid #e5e7eb; padding: 5px 8px; text-align: left; font-size: 10px; }
        .rules-table th { background: #f9fafb; font-weight: 700; color: #4b5563; }
        .rules-table td { color: #374151; }

        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 9px; color: #9ca3af; }

        .badge { display: inline-block; padding: 1px 6px; border-radius: 4px; font-size: 9px; font-weight: 700; }
        .badge-darurat { background: #fee2e2; color: #991b1b; }
        .badge-segera { background: #ffedd5; color: #9a3412; }
        .badge-normal { background: #dcfce7; color: #166534; }
        .badge-tunda { background: #f1f5f9; color: #475569; }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>LAPORAN REKOMENDASI PEMUPUKAN</h1>
        <h2>{{ $rekomendasiRbs->blokLahan->nama_blok }} — {{ $rekomendasiRbs->blokLahan->nama_pemilik }}</h2>
        <p>Kelompok Tani · Tanggal Analisis: {{ $rekomendasiRbs->tanggal_analisis->format('d F Y') }} · Admin: {{ $rekomendasiRbs->admin->nama_lengkap }}</p>
    </div>

    {{-- Status Banner --}}
    @php
        $statusClass = match($rekomendasiRbs->status_kebutuhan_dominan) {
            'Darurat' => 'status-darurat',
            'Segera' => 'status-segera',
            'Normal' => 'status-normal',
            default => 'status-tunda',
        };
    @endphp
    <div class="status-banner {{ $statusClass }}">
        <div class="label">Status Kebutuhan Pemupukan</div>
        <div class="value">{{ $rekomendasiRbs->status_kebutuhan_dominan }}</div>
    </div>

    {{-- Info Lahan --}}
    <div class="section">
        <div class="section-title">Informasi Blok Lahan</div>
        <table class="info-grid">
            <tr>
                <td class="label">Nama Blok</td>
                <td class="value">{{ $rekomendasiRbs->blokLahan->nama_blok }}</td>
                <td class="label">Pemilik</td>
                <td class="value">{{ $rekomendasiRbs->blokLahan->nama_pemilik }}</td>
            </tr>
            <tr>
                <td class="label">Luas Lahan</td>
                <td class="value">{{ number_format($rekomendasiRbs->blokLahan->luas_ha, 2) }} Ha</td>
                <td class="label">SPH</td>
                <td class="value">{{ number_format($rekomendasiRbs->blokLahan->sph) }} pohon/Ha</td>
            </tr>
            @if($rekomendasiRbs->blokLahan->tahun_tanam)
            <tr>
                <td class="label">Umur Tanaman</td>
                <td class="value">{{ $rekomendasiRbs->blokLahan->umur_tanaman }} tahun ({{ $rekomendasiRbs->blokLahan->kategori_umur }})</td>
                <td class="label">Jenis Tanah</td>
                <td class="value">{{ $rekomendasiRbs->blokLahan->jenis_tanah }}</td>
            </tr>
            <tr>
                <td class="label">Topografi</td>
                <td class="value">{{ $rekomendasiRbs->blokLahan->topografi }}</td>
                <td class="label">Tahun Tanam</td>
                <td class="value">{{ $rekomendasiRbs->blokLahan->tahun_tanam }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- Kebutuhan Logistik Pupuk --}}
    @if($rekomendasiRbs->total_urea || $rekomendasiRbs->total_kcl)
    <div class="section">
        <div class="section-title">Kebutuhan Logistik Pupuk</div>
        <table class="logistik-table">
            <thead>
                <tr>
                    <th>Jenis Pupuk</th>
                    <th>Dosis/Pokok</th>
                    <th>Total Kebutuhan</th>
                    <th>Karung (50kg)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="urea"><strong>Urea</strong></td>
                    <td class="urea">{{ $rekomendasiRbs->dosis_urea ?? '-' }} kg</td>
                    <td class="urea"><strong>{{ $rekomendasiRbs->total_urea ? number_format($rekomendasiRbs->total_urea, 1) : '-' }} kg</strong></td>
                    <td class="urea"><strong>{{ $rekomendasiRbs->karung_urea ?? '-' }}</strong></td>
                </tr>
                <tr>
                    <td class="kcl"><strong>KCl</strong></td>
                    <td class="kcl">{{ $rekomendasiRbs->dosis_kcl ?? '-' }} kg</td>
                    <td class="kcl"><strong>{{ $rekomendasiRbs->total_kcl ? number_format($rekomendasiRbs->total_kcl, 1) : '-' }} kg</strong></td>
                    <td class="kcl"><strong>{{ $rekomendasiRbs->karung_kcl ?? '-' }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    {{-- Catatan Dosis --}}
    @if($rekomendasiRbs->catatan_dosis)
    <div class="catatan-box">
        <div class="title">Catatan Aplikasi Dosis</div>
        <div class="text">{{ $rekomendasiRbs->catatan_dosis }}</div>
    </div>
    @endif

    {{-- Masalah Teridentifikasi --}}
    @if($rekomendasiRbs->masalah_teridentifikasi && count($rekomendasiRbs->masalah_teridentifikasi) > 0)
    <div class="section">
        <div class="section-title">Masalah Teridentifikasi ({{ count($rekomendasiRbs->masalah_teridentifikasi) }} masalah)</div>
        <div class="masalah-list">
            @foreach($rekomendasiRbs->masalah_teridentifikasi as $masalah)
                <span class="masalah-item">{{ $masalah }}</span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Rekomendasi Pupuk Spesifik --}}
    @if($rekomendasiRbs->rekomendasi_pupuk && count($rekomendasiRbs->rekomendasi_pupuk) > 0)
    <div class="section">
        <div class="section-title">Rekomendasi Pupuk Spesifik</div>
        @foreach($rekomendasiRbs->rekomendasi_pupuk as $pupuk)
        <div class="pupuk-card">
            <div class="nama">{{ $pupuk['jenis_utama'] ?? '' }}@if(!empty($pupuk['jenis_pendukung'])) + {{ $pupuk['jenis_pendukung'] }}@endif</div>
            <div class="detail">
                @if(!empty($pupuk['dosis']))Dosis: {{ $pupuk['dosis'] }}<br>@endif
                @if(!empty($pupuk['metode']))Metode: {{ $pupuk['metode'] }}<br>@endif
                @if(!empty($pupuk['waktu']))Waktu: {{ $pupuk['waktu'] }}@endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Saran Tindakan --}}
    @if($rekomendasiRbs->saran_tindakan_utama)
    <div class="saran-box">
        <div class="title">Saran Tindakan Utama</div>
        <div class="text">{{ $rekomendasiRbs->saran_tindakan_utama }}</div>
    </div>
    @endif

    {{-- Rules Terpicu --}}
    @if($rekomendasiRbs->rules_terpicu && count($rekomendasiRbs->rules_terpicu) > 0)
    <div class="section">
        <div class="section-title">Detail Rules yang Terpicu ({{ $rekomendasiRbs->jumlah_rule_terpicu }} rule)</div>
        <table class="rules-table">
            <thead>
                <tr>
                    <th style="width:5%">No</th>
                    <th style="width:35%">Indikasi Masalah</th>
                    <th style="width:25%">Pupuk</th>
                    <th style="width:15%">Status</th>
                    <th style="width:10%">Prioritas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rekomendasiRbs->rules_terpicu as $i => $rule)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $rule['indikasi'] ?? '-' }}</td>
                    <td>{{ $rule['pupuk'] ?? '-' }}</td>
                    <td>
                        @php $badgeClass = match($rule['status'] ?? '') {
                            'Darurat' => 'badge-darurat',
                            'Segera' => 'badge-segera',
                            'Normal' => 'badge-normal',
                            default => 'badge-tunda',
                        }; @endphp
                        <span class="badge {{ $badgeClass }}">{{ $rule['status'] ?? '-' }}</span>
                    </td>
                    <td>{{ $rule['prioritas'] ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh Sistem Pendukung Keputusan Pemupukan Kelapa Sawit (SPK Sawit)<br>
        Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB
    </div>
</body>
</html>
