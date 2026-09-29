<?php

require '/var/webapps/app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '/') . '/bootstrap.php';
require_once SYSTEMPATH . 'Config/DotEnv.php';
(new CodeIgniter\Config\DotEnv('/var/webapps/'))->load();
defined('ENVIRONMENT') || define('ENVIRONMENT', getenv('APP_ENVIRONMENT') ?: getenv('CI_ENVIRONMENT') ?: 'production');
require '/var/webapps/app/Helpers/utility_helper.php';
require '/var/webapps/app/Helpers/mt_helper.php';

$db = \Config\Database::connect();
$key = getenv('APP_KEY');

$aclUsers = $db->query("SELECT id, username FROM acl.users")->getResultArray();
$byAclId = [];
foreach ($aclUsers as $a) {
    $dec = kv_decrypt($a['username'], $key);
    $byAclId[$a['id']] = $dec;
}

$ukomUsers = $db->query("SELECT id, username, nama_lengkap FROM ukom.users")->getResultArray();
$byUkomUsername = [];
foreach ($ukomUsers as $u) {
    $byUkomUsername[strtolower(trim($u['username']))] = $u['nama_lengkap'];
}

$rows = $db->query("SELECT user_id, evaluator, COUNT(*) c FROM ukom.raw_assessment GROUP BY user_id, evaluator ORDER BY user_id, evaluator")->getResultArray();

$out = [];
foreach ($rows as $r) {
    $dec = $byAclId[$r['user_id']] ?? '(unknown)';
    $nama = $byUkomUsername[strtolower(trim($dec))] ?? '(none)';
    $out[$r['user_id']]['dec_username'] = $dec;
    $out[$r['user_id']]['nama_lengkap'] = $nama;
    $out[$r['user_id']]['evaluators'][] = $r['evaluator'] . ' x' . $r['c'];
}

foreach ($out as $aclId => $info) {
    echo 'ACL ' . $aclId . ' | user: ' . $info['dec_username'] . ' | ukom.nama_lengkap: ' . $info['nama_lengkap'] . PHP_EOL;
    foreach ($info['evaluators'] as $e) {
        echo '    evaluator: ' . $e . PHP_EOL;
    }
}
echo 'DONE' . PHP_EOL;
