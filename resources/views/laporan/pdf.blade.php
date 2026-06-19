<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekomendasi Pemupukan — {{ $rekomendasiRbs->blokLahan->nama_blok }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1e293b; line-height: 1.6; padding: 24px 32px; }

        /* Header */
        .header { text-align: center; margin-bottom: 18px; border-bottom: 3px solid #059669; padding-bottom: 14px; }
        .header h1 { font-size: 15px; font-weight: 700; color: #059669; margin-bottom: 3px; letter-spacing: 0.5px; }
        .header h2 { font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 4px; }
        .header p { font-size: 10px; color: #6b7280; }

        /* Status Banner — besar dan jelas */
        .status-banner { padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; text-align: center; }
        .status-darurat { background: #fef2f2; border: 2px solid #f87171; color: #991b1b; }
        .status-segera { background: #fff7ed; border: 2px solid #fb923c; color: #9a3412; }
        .status-normal { background: #f0fdf4; border: 2px solid #4ade80; color: #166534; }
        .status-tunda { background: #f8fafc; border: 2px solid #94a3b8; color: #475569; }
        .status-banner .label { font-size: 9px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600; opacity: 0.7; }
        .status-banner .value { font-size: 18px; font-weight: 800; margin-top: 2px; }

        /* Section */
        .section { margin-bottom: 16px; }
        .section-title { font-size: 11px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1.5px solid #d1d5db; }

        /* Info Grid */
        .info-grid { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .info-grid td { padding: 6px 10px; border: 1px solid #e5e7eb; vertical-align: top; font-size: 11px; }
        .info-grid .label { color: #6b7280; width: 30%; background: #f9fafb; font-weight: 500; }
        .info-grid .value { color: #1e293b; font-weight: 600; }

        /* Logistik — tabel paling penting, dibuat besar */
        .logistik-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .logistik-table th, .logistik-table td { border: 1.5px solid #d1d5db; padding: 8px 12px; text-align: center; }
        .logistik-table th { background: #f3f4f6; font-size: 10px; font-weight: 700; color: #374151; text-transform: uppercase; }
        .logistik-table td { font-size: 13px; font-weight: 700; }
        .logistik-table .urea { color: #92400e; }
        .logistik-table .kcl { color: #0e7490; }

        /* Jadwal table */
        .jadwal-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .jadwal-table th, .jadwal-table td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; font-size: 10px; }
        .jadwal-table th { background: #f9fafb; font-weight: 700; color: #4b5563; font-size: 9px; text-transform: uppercase; }
        .jadwal-table .num { text-align: right; font-weight: 700; }
        .jadwal-table .urea { color: #92400e; }
        .jadwal-table .kcl { color: #0e7490; }

        /* Saran box */
        .saran-box { background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 6px; padding: 10px 12px; margin-bottom: 14px; }
        .saran-box .title { font-size: 10px; font-weight: 700; color: #92400e; text-transform: uppercase; margin-bottom: 4px; }
        .saran-box .text { font-size: 11px; color: #78350f; line-height: 1.6; }

        /* Catatan box */
        .catatan-box { background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 6px; padding: 10px 12px; margin-bottom: 14px; }
        .catatan-box .title { font-size: 10px; font-weight: 700; color: #1e40af; text-transform: uppercase; margin-bottom: 4px; }
        .catatan-box .text { font-size: 11px; color: #1e3a5f; line-height: 1.6; }

        /* Masalah */
        .masalah-item { display: inline-block; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 4px; padding: 3px 8px; font-size: 10px; margin: 2px 3px 2px 0; color: #374151; font-weight: 500; }

        /* Pupuk card */
        .pupuk-card { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 8px 12px; margin-bottom: 6px; }
        .pupuk-card .nama { font-size: 12px; font-weight: 700; color: #15803d; margin-bottom: 3px; }
        .pupuk-card .detail { font-size: 10px; color: #4b5563; line-height: 1.5; }

        /* Rules table — teknis, font lebih kecil */
        .rules-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .rules-table th, .rules-table td { border: 1px solid #e5e7eb; padding: 4px 6px; text-align: left; font-size: 9px; }
        .rules-table th { background: #f9fafb; font-weight: 700; color: #6b7280; }
        .rules-table td { color: #4b5563; }

        .badge { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 8px; font-weight: 700; }
        .badge-darurat { background: #fee2e2; color: #991b1b; }
        .badge-segera { background: #ffedd5; color: #9a3412; }
        .badge-normal { background: #dcfce7; color: #166534; }
        .badge-tunda { background: #f1f5f9; color: #475569; }

        /* Disclaimer */
        .disclaimer { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px 10px; margin-bottom: 12px; font-size: 9px; color: #64748b; line-height: 1.5; }

        /* Footer */
        .footer { margin-top: 16px; padding-top: 10px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 9px; color: #9ca3af; }

        /* Meta info kecil */
        .meta-info { font-size: 9px; color: #6b7280; margin-bottom: 14px; padding: 6px 10px; background: #f9fafb; border-radius: 4px; border: 1px solid #e5e7eb; }
    </style>
</head>
<body>

    {{-- ═══ 1. HEADER ═══ --}}
    <div class="header">
        <h1>LAPORAN REKOMENDASI PEMUPUKAN</h1>
        <h2>{{ $rekomendasiRbs->blokLahan->nama_blok }} — {{ $rekomendasiRbs->blokLahan->nama_pemilik }}</h2>
        <p>Tanggal Analisis: {{ $rekomendasiRbs->tanggal_analisis->format('d F Y') }} · Admin: {{ $rekomendasiRbs->admin->nama_lengkap }}</p>
    </div>

    {{-- ═══ 2. STATUS — besar & jelas ═══ --}}
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
        <div class="value">{{ \App\Models\RekomendasiRbs::labelStatus($rekomendasiRbs->status_kebutuhan_dominan) }}</div>
    </div>

    {{-- ═══ 3. INFO LAHAN — ringkas ═══ --}}
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
                <td class="label">Luas</td>
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
                <td class="label">Total Pohon</td>
                <td class="value">{{ number_format($rekomendasiRbs->blokLahan->sph * $rekomendasiRbs->blokLahan->luas_ha) }} pohon</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- ═══ 4. KEBUTUHAN PUPUK — yang paling dicari petani ═══ --}}
    @if($rekomendasiRbs->total_urea || $rekomendasiRbs->total_kcl)
    <div class="section">
        <div class="section-title">Kebutuhan Pupuk</div>
        <table class="logistik-table">
            <thead>
                <tr>
                    <th>Jenis Pupuk</th>
                    <th>Dosis / Pokok</th>
                    <th>Total Kebutuhan</th>
                    <th>Jumlah Karung</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="urea">Urea</td>
                    <td class="urea">{{ $rekomendasiRbs->dosis_urea ?? '-' }} kg</td>
                    <td class="urea">{{ $rekomendasiRbs->total_urea ? number_format($rekomendasiRbs->total_urea, 1) . ' kg' : '-' }}</td>
                    <td class="urea">{{ $rekomendasiRbs->karung_urea ?? '-' }} karung</td>
                </tr>
                <tr>
                    <td class="kcl">KCl</td>
                    <td class="kcl">{{ $rekomendasiRbs->dosis_kcl ?? '-' }} kg</td>
                    <td class="kcl">{{ $rekomendasiRbs->total_kcl ? number_format($rekomendasiRbs->total_kcl, 1) . ' kg' : '-' }}</td>
                    <td class="kcl">{{ $rekomendasiRbs->karung_kcl ?? '-' }} karung</td>
                </tr>
            </tbody>
        </table>
        <p style="font-size: 9px; color: #6b7280; text-align: right;">*1 karung = 50 kg</p>
    </div>
    @endif

    {{-- ═══ 5. JADWAL PEMUPUKAN — kapan harus bertindak ═══ --}}
    @if($rekomendasiRbs->jadwal_pemupukan && count($rekomendasiRbs->jadwal_pemupukan) > 0)
    <div class="section">
        <div class="section-title">Jadwal Pemupukan</div>
        <table class="jadwal-table">
            <thead>
                <tr>
                    <th style="width:14%">Tahap</th>
                    <th style="width:24%">Waktu Aplikasi</th>
                    <th style="width:12%">Urea</th>
                    <th style="width:12%">KCl</th>
                    <th style="width:38%">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rekomendasiRbs->jadwal_pemupukan as $jadwal)
                <tr>
                    <td style="font-weight:600;">{{ $jadwal['nama_tahap'] }}</td>
                    <td>{{ $jadwal['estimasi_waktu'] }}</td>
                    <td class="num urea">{{ number_format($jadwal['urea_kg'], 1) }} kg</td>
                    <td class="num kcl">{{ number_format($jadwal['kcl_kg'], 1) }} kg</td>
                    <td style="font-style:italic;">{{ $jadwal['catatan'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ═══ 6. CATATAN DOSIS — peringatan penting ═══ --}}
    @if($rekomendasiRbs->catatan_dosis)
    <div class="catatan-box">
        <div class="title">⚠ Catatan Penting</div>
        <div class="text">{{ $rekomendasiRbs->catatan_dosis }}</div>
    </div>
    @endif

    {{-- ═══ 7. SARAN TINDAKAN — apa yang harus dilakukan ═══ --}}
    @if($rekomendasiRbs->saran_tindakan_utama)
    <div class="saran-box">
        <div class="title">Saran Tindakan</div>
        <div class="text">{{ $rekomendasiRbs->saran_tindakan_utama }}</div>
    </div>
    @endif

    {{-- ═══ 8. MASALAH TERIDENTIFIKASI ═══ --}}
    @if($rekomendasiRbs->masalah_teridentifikasi && count($rekomendasiRbs->masalah_teridentifikasi) > 0)
    <div class="section">
        <div class="section-title">Masalah Teridentifikasi</div>
        <div style="margin-bottom: 8px;">
            @foreach($rekomendasiRbs->masalah_teridentifikasi as $masalah)
                <span class="masalah-item">{{ $masalah }}</span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ═══ 9. REKOMENDASI PUPUK SPESIFIK ═══ --}}
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

    {{-- ═══ 10. DETAIL TEKNIS (font kecil, untuk dokumentasi) ═══ --}}
    @if($rekomendasiRbs->rules_terpicu && count($rekomendasiRbs->rules_terpicu) > 0)
    <div class="section">
        <div class="section-title">Detail Teknis — Rules Terpicu ({{ $rekomendasiRbs->jumlah_rule_terpicu }})</div>
        <table class="rules-table">
            <thead>
                <tr>
                    <th style="width:5%">No</th>
                    <th style="width:40%">Indikasi</th>
                    <th style="width:25%">Pupuk</th>
                    <th style="width:15%">Status</th>
                    <th style="width:15%">Prioritas</th>
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
                        <span class="badge {{ $badgeClass }}">{{ \App\Models\RekomendasiRbs::labelStatus($rule['status'] ?? '') }}</span>
                    </td>
                    <td>{{ $rule['prioritas'] ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ═══ 11. META INFO — validitas & confidence ═══ --}}
    <div class="meta-info">
        <strong>Validitas:</strong> {{ $rekomendasiRbs->validitas_rekomendasi ?? 'Estimasi Visual' }}
        &nbsp;·&nbsp;
        <strong>Tingkat Keyakinan:</strong> {{ $rekomendasiRbs->confidence_label ?? 'Rendah' }} ({{ $rekomendasiRbs->confidence_score ?? 0 }}%)
        @if(!$rekomendasiRbs->data_cukup)
        &nbsp;·&nbsp; <span style="color: #dc2626;">Data observasi belum lengkap</span>
        @endif
    </div>

    {{-- ═══ 12. DISCLAIMER ═══ --}}
    <div class="disclaimer">
        <strong>Catatan:</strong> Rekomendasi ini dihasilkan oleh sistem berbasis aturan (Rule-Based System) berdasarkan data observasi lapangan. Hasil ini bersifat rekomendasi dan bukan pengganti analisis laboratorium tanah/daun. Untuk keputusan yang lebih akurat, disarankan melengkapi dengan hasil uji lab.
    </div>

    {{-- ═══ FOOTER ═══ --}}
    <div class="footer">
        Sistem Pendukung Keputusan Pemupukan Kelapa Sawit (SPK Sawit)<br>
        Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WITA
    </div>
</body>
</html>
