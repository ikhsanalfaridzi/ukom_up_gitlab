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

$ukomUsers = $db->query("SELECT id, username FROM ukom.users")->getResultArray();
$aclUsers  = $db->query("SELECT id, username FROM acl.users")->getResultArray();

$key = getenv('APP_KEY');
$byUsername = [];
foreach ($aclUsers as $a) {
    $dec = kv_decrypt($a['username'], $key);
    if ($dec !== false && $dec !== null && $dec !== '') {
        $byUsername[$dec] = $a['id'];
    }
}

$updated = 0;
$mapped  = 0;
foreach ($ukomUsers as $u) {
    $oldId = $u['id'];
    $newId = $byUsername[$u['username']] ?? null;
    if ($newId) {
        $mapped++;
        $db->query("UPDATE ukom.raw_assessment SET user_id = ? WHERE user_id = ?", [$newId, $oldId]);
        $updated += $db->affectedRows();
    }
}

echo "ukom.users: " . count($ukomUsers) . ", acl.users: " . count($aclUsers) . PHP_EOL;
echo "user mapping found: " . $mapped . PHP_EOL;
echo "raw_assessment rows updated: " . $updated . PHP_EOL;

$unmapped = $db->query("SELECT COUNT(*) c FROM ukom.raw_assessment ra LEFT JOIN acl.users au ON au.id::text = ra.user_id WHERE au.id IS NULL")->getRowArray();
echo "raw rows unmapped ke acl.users: " . $unmapped['c'] . PHP_EOL;

$mapped_cnt = $db->query("SELECT COUNT(DISTINCT ra.user_id) c FROM ukom.raw_assessment ra JOIN acl.users au ON au.id::text = ra.user_id")->getRowArray();
echo "raw distinct user_id terpetakan: " . $mapped_cnt['c'] . PHP_EOL;

echo "DONE" . PHP_EOL;
