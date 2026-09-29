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

$db->query("ALTER TABLE acl.users ADD COLUMN IF NOT EXISTS full_name VARCHAR(255)");

$ukomUsers = $db->query("SELECT username, nama_lengkap FROM ukom.users")->getResultArray();
$byUkomUsername = [];
foreach ($ukomUsers as $u) {
    $byUkomUsername[strtolower(trim($u['username']))] = $u['nama_lengkap'];
}

$aclUsers = $db->query("SELECT id, username FROM acl.users")->getResultArray();
$updated = 0;
foreach ($aclUsers as $a) {
    $dec = kv_decrypt($a['username'], $key);
    if ($dec === false || $dec === null || $dec === '') {
        continue;
    }
    $name = $byUkomUsername[strtolower(trim($dec))] ?? null;
    if ($name !== null && $name !== '') {
        $db->query("UPDATE acl.users SET full_name = ? WHERE id = ?", [$name, $a['id']]);
        $updated += $db->affectedRows();
        echo 'acl ' . $a['id'] . ' (' . $dec . ') -> ' . $name . PHP_EOL;
    }
}

echo 'updated acl.users.full_name: ' . $updated . PHP_EOL;
$stillNull = $db->query("SELECT COUNT(*) c FROM acl.users WHERE full_name IS NULL OR full_name = ''")->getRowArray();
echo 'acl users tanpa full_name: ' . $stillNull['c'] . PHP_EOL;
echo 'DONE' . PHP_EOL;
