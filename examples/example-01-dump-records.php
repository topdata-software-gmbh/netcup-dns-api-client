<?php

/**
 * it backups a domain's DNS records to a JSON file.
 */

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use TopdataSoftwareGmbh\NetcupDnsApiClient\NetcupDnsApiClient;

// ---- load config from .env
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');
$domain = $_ENV['NETCUP_DOMAIN'];
$credentials = [
    'customernumber' => $_ENV['NETCUP_CUSTOMERNUMBER'],
    'apikey'         => $_ENV['NETCUP_APIKEY'],
    'apipassword'    => $_ENV['NETCUP_APIPASSWORD'],
];
$pathDestJsonFile = '/tmp/backup-' . $domain . '-' . date('Ymd-His') . '.json';

// ---- print config for debugging
dump([
    'netcupApiCredentials' => $credentials,
    'domain'               => $domain,
    'pathDestJsonFile'     => $pathDestJsonFile,
]);

// --- fetch and dump records
$client = new NetcupDnsApiClient($credentials['customernumber'], $credentials['apikey'], $credentials['apipassword']);
$client->login();

dump($client->getRecords($domain));

$client->logout();
