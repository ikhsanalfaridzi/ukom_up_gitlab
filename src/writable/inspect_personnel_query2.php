<?php
chdir(__DIR__ . '/../public');
require __DIR__ . '/../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once SYSTEMPATH . 'Config/DotEnv.php';
(new CodeIgniter\Config\DotEnv(ROOTPATH))->load();
$app = Config\Services::codeigniter();
$app->initialize();
$app->setContext('php-cli');
$model = new App\Models\Employee\PersonnelModel();
$query = $model->get_datatables_query();
echo $query->getCompiledSelect();
