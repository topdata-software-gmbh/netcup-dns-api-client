<?php

namespace TopdataSoftwareGmbh\NetcupDnsApiClient;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * 04/2024 created
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
                    'customernumber' => $this->customernumber
                ]
            ]
        ]);

        // Convert the response to an associative array
        $data = $response->toArray();

        // Check if the login was successful
        if ($data['status'] !== 'success') {
            throw new \Exception('Error: ' . $data['longmessage']);
        }

        // Set the session ID for subsequent API requests
        $this->apisessionid = $data['responsedata']['apisessionid'];
    }



    public function logout()
    {
        $response = $this->httpClient->request('POST', self::ENDPOINT, [
            'json' => [
                'action' => 'logout',
                'param'  => [
                    'apikey'         => $this->apikey,
                    'apisessionid'   => $this->apisessionid,
                    'customernumber' => $this->customernumber
                ]
            ]
        ]);

        if($response->toArray()['status'] !== 'success') {
            throw new \Exception('Error: ' . $response->toArray()['longmessage']);
        }
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
    public function addRecord(string $hostname, string $domain, $recordType, $destination, $priority = 0)
    {
        $response = $this->httpClient->request('POST', self::ENDPOINT, [
            'json' => [
                'action' => 'updateDnsRecords',
                'param'  => [
                    'apikey'         => $this->apikey,
                    'apisessionid'   => $this->apisessionid,
                    'customernumber' => $this->customernumber,
                    'clientrequestid' => uniqid(),
                    'domainname'     => $domain,
                    'dnsrecordset'   => [
                        'dnsrecords' => [
                            [
                                'id'          => '',
                                'hostname'    => $hostname,
                                'type'        => $recordType,
                                'priority'    => $priority,
                                'destination' => $destination,
                                'deleterecord' => 'false',
                                'state'       => 'yes'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function delRecord($id, $host, $domain, $recordType, $destination, $priority = 0)
    {
        // Implement delRecord function
    }

    public function modRecord($id, $host, $domain, $recordType, $destination, $priority = 0)
    {
        // Implement modRecord function
    }

    public function getSOA($domain)
    {
        // Implement getSOA function
    }

    public function getRecords($domain)
    {
        // Implement getRecords function
    }

    public function backup($domain)
    {
        // Implement backup function
    }

    public function restore($file)
    {
        // Implement restore function
    }

    public function setSOA($domain, $ttl, $refresh, $retry, $expire, $dnssecstatus)
    {
        // Implement setSOA function
    }

    public function listDomains()
    {
        // Implement listDomains function
    }
}
