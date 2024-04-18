<?php

/**
 * it backups a domain's DNS records to a JSON file
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

// -- fetch all records for domain and filter by subdomain
$records = $client->getRecords($domain);
$recordsFiltered = array_filter($records, function ($record) {
    return $record['hostname'] === SUBDOMAIN && $record['type'] === RECORD_TYPE && $record['destination'] === DEST_IP;
});
assert(count($recordsFiltered) === 1, "expected exactly one record to delete, got " . count($recordsFiltered));
$record = reset($recordsFiltered);
dump($record);

// -- delete using the record id
$client->delRecord($domain, id: $record['id'], hostname: $record['hostname'], recordType: $record['type'], destination: $record['destination'], priority: $record['priority']);
$client->logout();

echo "deleted subdomain " . SUBDOMAIN . " from domain " . $domain . " with IP " . DEST_IP . "\n";
