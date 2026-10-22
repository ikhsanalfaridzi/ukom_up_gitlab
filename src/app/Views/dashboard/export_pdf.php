<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Riwayat Statistik UKKJ &amp; PDJL</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        h2 { font-size: 12px; margin: 14px 0 6px; padding-bottom: 3px; border-bottom: 1px solid #999; page-break-after: avoid; }
        .meta { font-size: 10px; color: #555; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #999; padding: 3px 5px; }
        th { background: #e8eef7; text-align: left; }
        .num { text-align: right; }
        .muted { color: #777; text-align: center; }
    </style>
</head>
<body>
    <h1>Laporan Riwayat Statistik UKKJ &amp; PDJL</h1>
    <div class="meta">Periode: <?php echo esc($period); ?> &middot; Dicetak: <?php echo esc($printed); ?></div>

    <h2>Ringkasan</h2>
    <table>
        <thead>
            <tr>
                <th>Jenis</th>
                <th class="num">Pendaftar</th>
                <th class="num">Dinilai</th>
                <th class="num">Lulus</th>
                <th class="num">Tidak Lulus</th>
                <th class="num">Belum Dinilai</th>
                <th class="num">Kelompok</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (['UKKJ' => $summary['UKKJ'], 'PDJL' => $summary['PDJL']] as $jenis => $stat) : ?>
                <tr>
                    <td><?php echo esc($jenis); ?></td>
                    <td class="num"><?php echo number_format($stat['pendaftar']); ?></td>
                    <td class="num"><?php echo number_format($stat['dinilai']); ?></td>
                    <td class="num"><?php echo number_format($stat['lulus']); ?></td>
                    <td class="num"><?php echo number_format($stat['tidak_lulus']); ?></td>
                    <td class="num"><?php echo number_format($stat['belum_dinilai']); ?></td>
                    <td class="num"><?php echo number_format($stat['kelompok']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Riwayat UKKJ per Gelombang</h2>
    <table>
        <thead>
            <tr>
                <th>Tahun</th>
                <th>Gelombang</th>
                <th>Tanggal</th>
                <th class="num">Pendaftar</th>
                <th class="num">Dinilai</th>
                <th class="num">Lulus</th>
                <th class="num">Tidak Lulus</th>
                <th class="num">Belum Dinilai</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($ukkj['groups'])) : ?>
                <tr><td colspan="8" class="muted">Belum ada data UKKJ pada periode ini.</td></tr>
            <?php else : ?>
                <?php foreach ($ukkj['groups'] as $group) : ?>
                    <tr>
                        <td><?php echo esc($group['year']); ?></td>
                        <td><?php echo esc($group['name']); ?></td>
                        <td><?php echo esc($group['date']); ?></td>
                        <td class="num"><?php echo number_format($group['pendaftar']); ?></td>
                        <td class="num"><?php echo number_format($group['dinilai']); ?></td>
                        <td class="num"><?php echo number_format($group['lulus']); ?></td>
                        <td class="num"><?php echo number_format($group['tidak_lulus']); ?></td>
                        <td class="num"><?php echo number_format($group['belum_dinilai']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Tren Partisipasi UKKJ per Tahun</h2>
    <table>
        <thead>
            <tr>
                <th>Tahun</th>
                <th class="num">Pendaftar</th>
                <th class="num">Dinilai</th>
                <th class="num">Lulus</th>
                <th class="num">Tidak Lulus</th>
                <th class="num">Belum Dinilai</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($ukkj['years'])) : ?>
                <tr><td colspan="6" class="muted">Belum ada data.</td></tr>
            <?php else : ?>
                <?php foreach ($ukkj['years'] as $year => $stat) : ?>
                    <tr>
                        <td><?php echo esc($year); ?></td>
                        <td class="num"><?php echo number_format($stat['pendaftar']); ?></td>
                        <td class="num"><?php echo number_format($stat['dinilai']); ?></td>
                        <td class="num"><?php echo number_format($stat['lulus']); ?></td>
                        <td class="num"><?php echo number_format($stat['tidak_lulus']); ?></td>
                        <td class="num"><?php echo number_format($stat['belum_dinilai']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Breakdown Peserta UKKJ per Jenjang Target</h2>
    <table>
        <thead>
            <tr>
                <th>Jenjang Target</th>
                <th class="num">Jumlah Peserta</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ukkj['jenjang'] as $label => $count) : ?>
                <tr>
                    <td><?php echo esc($label); ?></td>
                    <td class="num"><?php echo number_format($count); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Riwayat PDJL per Gelombang</h2>
    <table>
        <thead>
            <tr>
                <th>Tahun</th>
                <th>Gelombang</th>
                <th>Tanggal</th>
                <th class="num">Pendaftar</th>
                <th class="num">Dinilai</th>
                <th class="num">Lulus</th>
                <th class="num">Tidak Lulus</th>
                <th class="num">Belum Dinilai</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pdjl['groups'])) : ?>
                <tr><td colspan="8" class="muted">Belum ada data PDJL pada periode ini.</td></tr>
            <?php else : ?>
                <?php foreach ($pdjl['groups'] as $group) : ?>
                    <tr>
                        <td><?php echo esc($group['year']); ?></td>
                        <td><?php echo esc($group['name']); ?></td>
                        <td><?php echo esc($group['date']); ?></td>
                        <td class="num"><?php echo number_format($group['pendaftar']); ?></td>
                        <td class="num"><?php echo number_format($group['dinilai']); ?></td>
                        <td class="num"><?php echo number_format($group['lulus']); ?></td>
                        <td class="num"><?php echo number_format($group['tidak_lulus']); ?></td>
                        <td class="num"><?php echo number_format($group['belum_dinilai']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Tren Partisipasi PDJL per Tahun</h2>
    <table>
        <thead>
            <tr>
                <th>Tahun</th>
                <th class="num">Pendaftar</th>
                <th class="num">Dinilai</th>
                <th class="num">Lulus</th>
                <th class="num">Tidak Lulus</th>
                <th class="num">Belum Dinilai</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pdjl['years'])) : ?>
                <tr><td colspan="6" class="muted">Belum ada data.</td></tr>
            <?php else : ?>
                <?php foreach ($pdjl['years'] as $year => $stat) : ?>
                    <tr>
                        <td><?php echo esc($year); ?></td>
                        <td class="num"><?php echo number_format($stat['pendaftar']); ?></td>
                        <td class="num"><?php echo number_format($stat['dinilai']); ?></td>
                        <td class="num"><?php echo number_format($stat['lulus']); ?></td>
                        <td class="num"><?php echo number_format($stat['tidak_lulus']); ?></td>
                        <td class="num"><?php echo number_format($stat['belum_dinilai']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Distribusi Peserta PDJL per Asal Jabatan</h2>
    <table>
        <thead>
            <tr>
                <th>Asal Jabatan</th>
                <th class="num">Jumlah Peserta</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pdjl['jabatan'])) : ?>
                <tr><td colspan="2" class="muted">Belum ada data.</td></tr>
            <?php else : ?>
                <?php foreach ($pdjl['jabatan'] as $label => $count) : ?>
                    <tr>
                        <td><?php echo esc($label); ?></td>
                        <td class="num"><?php echo number_format($count); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
