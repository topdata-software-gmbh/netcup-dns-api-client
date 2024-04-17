<?php

/**
 * it backups a domain's DNS records to a JSON file
 */

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use TopdataSoftwareGmbh\NetcupDnsApiClient\NetcupDnsApiClient;

/**
 * @param array $credentials
 * @param string $domain
 * @param string $pathDestJsonFile
 */
function makeBackup(array $credentials, string $domain, string $pathDestJsonFile): void
{
    $client = new NetcupDnsApiClient($credentials['customernumber'], $credentials['apikey'], $credentials['apipassword']);
    $client->login();

    $content = [
        'soa'     => $client->getSOA($domain),
        'records' => $client->getRecords($domain),
    ];
    file_put_contents($pathDestJsonFile, json_encode($content, JSON_PRETTY_PRINT));

    $client->logout();
}

// ---- load config from .env
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');
$domain = $_ENV['NETCUP_DOMAIN'];
$netcupApiCredentials = [
    'customernumber' => $_ENV['NETCUP_CUSTOMERNUMBER'],
    'apikey'         => $_ENV['NETCUP_APIKEY'],
    'apipassword'    => $_ENV['NETCUP_APIPASSWORD'],
];
$pathDestJsonFile = '/tmp/backup-' . $domain . '-' . date('Ymd-His') . '.json';

// ---- print config for debugging
dump([
    'netcupApiCredentials' => $netcupApiCredentials,
    'domain'               => $domain,
    'pathDestJsonFile'     => $pathDestJsonFile
]);

// ---- main
makeBackup($netcupApiCredentials, $domain, $pathDestJsonFile);

echo "wrote backup to $pathDestJsonFile\n";
