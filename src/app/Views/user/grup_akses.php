<?php echo $this->extend('layouts/app') ?>

<?php echo $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <h1><?php echo $title ?></h1>
    </div>
    <div class="row">
        <table class="table table-bordered" id="tableGrup">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Grup Akses</th>
                    <th class="text-center">Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">1</td>
                    <td class="text-center">Super Admin</td>
                    <td>Hak akses untuk melakukan mastering pada aplikasi</td>
                </tr>
                <tr>
                    <td class="text-center">2</td>
                    <td class="text-center">Penilai</td>
                    <td>Hak akses untuk melakukan penilaian dan melihat hasil penilaian</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php echo $this->endSection() ?>