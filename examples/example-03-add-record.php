<?php

/**
 * it backups a domain's DNS records to a JSON file.
 */
const SUBDOMAIN = 'testsubdomain';
const DEST_IP = '1.2.3.4';
const RECORD_TYPE = 'A';
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

// ---- print config for debugging
dump([
    'netcupApiCredentials' => $credentials,
    'domain'               => $domain,
]);

// ---- main
$client = new NetcupDnsApiClient($credentials['customernumber'], $credentials['apikey'], $credentials['apipassword']);
$client->login();
$responseBody = $client->addRecord($domain, SUBDOMAIN, RECORD_TYPE, DEST_IP);
// dump($responseBody);
$client->logout();

echo 'added subdomain ' . SUBDOMAIN . ' to domain ' . $domain . ' with IP ' . DEST_IP . "\n";
