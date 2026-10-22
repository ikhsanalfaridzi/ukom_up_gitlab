<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\Recap\RecapModel;

class TestRegion extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'test:region';
    protected $description = 'Test breakdown wilayah/instansi';

    public function run(array $params)
    {
        $recap = new RecapModel();
        $data  = $recap->get_region_breakdown();

        CLI::write('totals   : ' . json_encode($data['totals']));
        CLI::write('instansi :');
        foreach ($data['instansi'] as $name => $row) {
            CLI::write(sprintf('  %-45s peserta=%d lulus=%d tidak=%d belum=%d groups=%d verif=%s', substr($name, 0, 45), $row['peserta'], $row['lulus'], $row['tidak_lulus'], $row['belum_dinilai'], $row['groups'], json_encode($row['verifikasi'])));
        }
        CLI::write('provinsi :');
        foreach ($data['provinsi'] as $name => $row) {
            CLI::write(sprintf('  %-45s peserta=%d lulus=%d tidak=%d belum=%d groups=%d', substr($name, 0, 45), $row['peserta'], $row['lulus'], $row['tidak_lulus'], $row['belum_dinilai'], $row['groups']));
        }

        $d26 = $recap->get_region_breakdown('2026-01-01', '2026-12-31');
        CLI::write('totals 2026 : ' . json_encode($d26['totals']));
        CLI::write('instansi 26 : ' . json_encode(array_keys($d26['instansi'])));
    }
}
