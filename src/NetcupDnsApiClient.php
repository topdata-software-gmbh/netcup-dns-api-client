<?php

namespace TopdataSoftwareGmbh\NetcupDnsApiClient;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * 04/2024 created.
 */
class NetcupDnsApiClient
{
    const ENDPOINT = 'https://ccp.netcup.net/run/webservice/servers/endpoint.php?JSON';

    private HttpClientInterface $httpClient;
    private string $apikey;
    private string $apipassword;
    private string $customernumber;
    private ?string $apisessionid;

    public function __construct(string $customernumber, string $apikey, string $apipassword)
    {
        $this->httpClient = HttpClient::create();
        $this->customernumber = $customernumber;
        $this->apikey = $apikey;
        $this->apipassword = $apipassword;
    }

    /**
     * private helper.
     */
    private function _post($action, array $extraParams = []): array
    {
        $response = $this->httpClient->request('POST', self::ENDPOINT, [
            'json' => [
                'action' => $action,
                'param'  => array_merge([
                    'apikey'         => $this->apikey,
                    'apisessionid'   => $this->apisessionid ?? null,
                    'customernumber' => $this->customernumber,
                ], $extraParams),
            ],
        ]);

        $data = $response->toArray();

        if ($data['status'] !== 'success') {
            throw new \Exception('Error: ' . $data['longmessage']);
        }

        return $data;
    }

    /**
     * Logs in to the API using provided credentials.
     *
     * @throws \Exception if login is unsuccessful
     */
    public function login()
    {
        // Make a POST request to the API endpoint to login
        $response = $this->httpClient->request('POST', self::ENDPOINT, [
            'json' => [
                'action' => 'login',
                'param'  => [
                    'apikey'         => $this->apikey,
                    'apipassword'    => $this->apipassword,
                    'customernumber' => $this->customernumber,
                ],
            ],
        ]);

        // Convert the response to an associative array
        $data = $response->toArray();

        // Check if the login was successful
        if ($data['status'] !== 'success') {
            throw new \Exception('Error: ' . $data['longmessage']);
        }
        $this->apisessionid = $data['responsedata']['apisessionid'];
    }

    public function logout()
    {
        $this->_post('logout');
        $this->apisessionid = null;
    }

    //addRecord() {
    //	login
    //	if [ "$3" == "CAA" ] || [ "$3" == "caa" ]; then
    //		if [ "$(echo "$4" | cut -d' ' -f2)" == "issue" ] || [ "$(echo "$4" | cut -d' ' -f2)" == "iodef" ] || [ "$(echo "$4" | cut -d' ' -f2)" == "issuewild" ]; then
    //			prepstate=$(echo "$4" | cut -d' ' -f3)
    //			dest=${4//$prepstate/\\"\"$prepstate\\"\"}
    //		else
    //			echo "Error: Please Check your CAA Record"
    //			logout
    //			exit 1
    //		fi
    //	else
    //		dest=$4
    //	fi
    //	tmp=$(curl -s -X POST -d "{\"action\": \"updateDnsRecords\", \"param\": {\"apikey\": \"$apikey\", \"apisessionid\": \"$sid\", \"customernumber\": \"$cid\",\"clientrequestid\": \"$client\" , \"domainname\": \"$2\", \"dnsrecordset\": { \"dnsrecords\": [ {\"id\": \"\", \"hostname\": \"$1\", \"type\": \"$3\", \"priority\": \"${5:-"0"}\", \"destination\": \"$dest\", \"deleterecord\": \"false\", \"state\": \"yes\"} ]}}}" "$end")
    //	if [ $debug = true ]; then
    //		echo "${tmp}"
    //	fi
    //	if [ "$(echo "$tmp" | jq -r .status)" != "success" ]; then
    //		echo "Error: $tmp"
    //		logout
    //		return 1
    //	fi
    //	echo "${tmp}" | jq --arg host "$1" --arg type "$3" --arg dest "$dest" '.responsedata.dnsrecords[] | select(.hostname==$host and .type==$type and .destination==$dest) .id' | tr -d \"
    //	logout
    //}
    public function addRecord(string $domain, string $hostname, string $recordType, string $destination, int $priority = 0)
    {
        $data = $this->_post('updateDnsRecords', [
            'clientrequestid' => uniqid(),
            'domainname'      => $domain,
            'dnsrecordset'    => [
                'dnsrecords' => [
                    [
                        //                        'id'           => '',
                        'hostname'     => $hostname,
                        'type'         => $recordType,
                        'priority'     => $priority,
                        'destination'  => $destination,
                        'deleterecord' => 'false',
                        'state'        => 'yes',
                    ],
                ],
            ],
        ]);

        return $data;
    }

    //delRecord() {
    //	login
    //	if [ "$4" == "CAA" ] || [ "$4" == "caa" ]; then
    //		if [ "$(echo "$5" | cut -d' ' -f2)" == "issue" ] || [ "$(echo "$5" | cut -d' ' -f2)" == "iodef" ] || [ "$(echo "$5" | cut -d' ' -f2)" == "issuewild" ]; then
    //			prepstate=$(echo "$5" | cut -d' ' -f3)
    //			dest=${5//$prepstate/\\"\"$prepstate\\"\"}
    //		else
    //			echo "Error: Please Check your CAA Record"
    //			logout
    //			exit 1
    //		fi
    //	else
    //		dest=$5
    //	fi
    //	tmp=$(curl -s -X POST -d "{\"action\": \"updateDnsRecords\", \"param\": {\"apikey\": \"$apikey\", \"apisessionid\": \"$sid\", \"customernumber\": \"$cid\",\"clientrequestid\": \"$client\" , \"domainname\": \"$3\", \"dnsrecordset\": { \"dnsrecords\": [ {\"id\": \"$1\", \"hostname\": \"$2\", \"type\": \"$4\", \"priority\": \"${6:-"0"}\", \"destination\": \"$dest\", \"deleterecord\": \"TRUE\", \"state\": \"yes\"} ]}}}" "$end")
    //	if [ $debug = true ]; then
    //		echo "${tmp}"
    //	fi
    //	if [ "$(echo "$tmp" | jq -r .status)" != "success" ]; then
    //		echo "Error: $tmp"
    //		logout
    //		return 1
    //	fi
    //	logout
    //}

    public function delRecord(string $domain, $id, ?string $hostname = null, ?string $recordType = null, ?string $destination = null, ?int $priority = null)
    {
        $data = $this->_post('updateDnsRecords', [
            'clientrequestid' => uniqid(),
            'domainname'      => $domain,
            'dnsrecordset'    => [
                'dnsrecords' => [
                    [
                        'id'           => $id,
                        'hostname'     => $hostname,
                        'type'         => $recordType,
                        'priority'     => $priority,
                        'destination'  => $destination,
                        'deleterecord' => 'TRUE',
                        'state'        => 'yes',
                    ],
                ],
            ],
        ]);
    }

    //modRecord() {
    //	login
    //	if [ "$4" == "CAA" ] || [ "$4" == "caa" ]; then
    //		if [ "$(echo "$5" | cut -d' ' -f2)" == "issue" ] || [ "$(echo "$5" | cut -d' ' -f2)" == "iodef" ] || [ "$(echo "$5" | cut -d' ' -f2)" == "issuewild" ]; then
    //			prepstate=$(echo "$5" | cut -d' ' -f3)
    //			dest=${5//$prepstate/\\"\"$prepstate\\"\"}
    //		else
    //			echo "Error: Please Check your CAA Record"
    //			logout
    //			exit 1
    //		fi
    //	else
    //		dest=$5
    //	fi
    //	tmp=$(curl -s -X POST -d "{\"action\": \"updateDnsRecords\", \"param\": {\"apikey\": \"$apikey\", \"apisessionid\": \"$sid\", \"customernumber\": \"$cid\",\"clientrequestid\": \"$client\" , \"domainname\": \"$3\", \"dnsrecordset\": { \"dnsrecords\": [ {\"id\": \"$1\", \"hostname\": \"$2\", \"type\": \"$4\", \"priority\": \"${6:-"0"}\", \"destination\": \"$dest\", \"deleterecord\": \"FALSE\", \"state\": \"yes\"} ]}}}" "$end")
    //	if [ $debug = true ]; then
    //		echo "${tmp}"
    //	fi
    //	if [ "$(echo "$tmp" | jq -r .status)" != "success" ]; then
    //		echo "Error: $tmp"
    //		logout
    //		return 1
    //	fi
    //	logout
    //}

    public function modRecord($id, $host, $domain, $recordType, $destination, $priority = 0)
    {
        $data = $this->_post('updateDnsRecords', [
            'clientrequestid' => uniqid(),
            'domainname'      => $domain,
            'dnsrecordset'    => [
                'dnsrecords' => [
                    [
                        'id'           => $id,
                        'hostname'     => $host,
                        'type'         => $recordType,
                        'priority'     => $priority,
                        'destination'  => $destination,
                        'deleterecord' => 'FALSE',
                        'state'        => 'yes',
                    ],
                ],
            ],
        ]);
    }

    //getSOA() {
    //	login
    //	tmp=$(curl -s -X POST -d "{\"action\": \"infoDnsZone\", \"param\": {\"apikey\": \"$apikey\", \"apisessionid\": \"$sid\", \"customernumber\": \"$cid\", \"domainname\": \"$1\"}}" "$end")
    //	if [ $debug = true ]; then
    //		echo "$tmp"
    //	fi
    //	if [ "$(echo "$tmp" | jq -r .status)" != "success" ]; then
    //		echo "Error: $tmp"
    //		logout
    //		return 1
    //	fi
    //	xxd=$(echo "${tmp}" | jq -r '.responsedata')
    //	echo "$xxd"
    //	logout
    //}

    public function getSOA($domain)
    {
        $data = $this->_post('infoDnsZone', [
            'domainname' => $domain,
        ]);

        return $data['responsedata'];
    }

    //getRecords() {
    //	login
    //	tmp=$(curl -s -X POST -d "{\"action\": \"infoDnsRecords\", \"param\": {\"apikey\": \"$apikey\", \"apisessionid\": \"$sid\", \"customernumber\": \"$cid\", \"domainname\": \"$1\"}}" "$end")
    //	if [ $debug = true ]; then
    //		echo "$tmp"
    //	fi
    //	if [ "$(echo "$tmp" | jq -r .status)" != "success" ]; then
    //		echo "Error: $tmp"
    //		logout
    //		return 1
    //	fi
    //	xxd=$(echo "${tmp}" | jq -r '.responsedata.dnsrecords')
    //	echo "$xxd"
    //	logout
    //}

    public function getRecords($domain)
    {
        $data = $this->_post('infoDnsRecords', [
            'domainname' => $domain,
        ]);

        return $data['responsedata']['dnsrecords'];
    }

    //setSOA() {
    //	login
    //	tmp=$(curl -s -X POST -d "{\"action\": \"updateDnsZone\", \"param\": {\"apikey\": \"$apikey\", \"apisessionid\": \"$sid\", \"customernumber\": \"$cid\",\"clientrequestid\": \"$client\" , \"domainname\": \"$1\", \"dnszone\": { \"name\": \"$1\", \"ttl\": \"$2\", \"serial\": \"\", \"refresh\": \"$3\", \"retry\": \"$4\", \"expire\": \"$5\", \"dnssecstatus\": \"$6\"} }}" "$end")
    //	if [ $debug = true ]; then
    //		echo "${tmp}"
    //	fi
    //	if [ "$(echo "$tmp" | jq -r .status)" != "success" ]; then
    //		echo "Error: $tmp"
    //		logout
    //		return 1
    //	fi
    //	logout
    //}

    // untested
    public function setSOA($domain, $ttl, $refresh, $retry, $expire, $dnssecstatus)
    {
        $data = $this->_post('updateDnsZone', [
            'clientrequestid' => uniqid(),
            'domainname'      => $domain,
            'dnszone'         => [
                'name'         => $domain,
                'ttl'          => $ttl,
                'serial'       => '',
                'refresh'      => $refresh,
                'retry'        => $retry,
                'expire'       => $expire,
                'dnssecstatus' => $dnssecstatus,
            ],
        ]);
    }

    //listDomains() {
    //	login
    //	tmp=$(curl -s -X POST -d "{\"action\": \"listallDomains\", \"param\": {\"apikey\": \"$apikey\", \"apisessionid\": \"$sid\", \"customernumber\": \"$cid\", \"domainname\": \"$1\"}}" "$end")
    //	if [ $debug = true ]; then
    //		echo "$tmp"
    //	fi
    //	if [ "$(echo "$tmp" | jq -r .status)" != "success" ]; then
    //		echo "Error: $tmp"
    //		logout
    //		return 1
    //	fi
    //	xxd=$(echo "${tmp}" | jq -r '.responsedata[].domainname')
    //	echo "$xxd"
    //	logout
    //}

    public function getDomains()
    {
        $data = $this->_post('listallDomains');

        return $data['responsedata'];
    }
}
