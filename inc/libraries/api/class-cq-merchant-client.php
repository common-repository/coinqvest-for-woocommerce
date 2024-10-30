<?php
namespace WC_COINQVEST\Inc\Libraries\Api;
use WC_COINQVEST\Inc\Core\Init;

defined('ABSPATH') or exit;

/**
 * Class CQ_Merchant_Client
 *
 * PHP implementation of a REST client for the Whalestack Merchant API
 * see https://www.whalestack.com/en/api-docs
 */
class CQ_Merchant_Client extends CQ_Rest_Client {

    /**
     * The API Key as given by https://www.whalestack.com/en/api-settings
     * This is initialized by the constructor, see below.
     *
     * @var string
     */
    var $key = null;

    /**
     * The API Secret as given by https://www.whalestack.com/en/api-settings
     * This is initialized by the constructor, see below.
     *
     * @var string
     */
    var $secret = null;

    /**
     * The API version to which we connect (leave it as is)
     *
     * @var string
     */
    var $apiVersion = 'v1';

    /**
     * Used in the HTTP user agent (leave it as is)
     *
     * @var string
     */
    var $clientName = 'php-merchant-sdk-woo';

    /**
     * The current version of this SDK, used in the HTTP user agent (leave it as is)
     *
     * @var string
     */
    var $clientVersion = '1.0.0';

    /**
     * Indicates whether requests and responses should be logged
     * This is automatically initialized by the constructor, see below.
     *
     * @var boolean
     */
    var $enableLogging = false;

    /**
     * Specifies the log file to which to write, if any.
     * This is initialized by the constructor, see below.
     *
     * @var string
     */
    var $logFile = null;

    /**
     * Merchant API client constructor, initialize this with the API key and secret as given by https://www.whalestack.com/en/api-settings
     *
     * @param string $key Your Whalestack API Key
     * @param string $secret Your Whalestack API Secret
     * @param string $logFile Log file location, if any
     */
    public function __construct($key = null, $secret = null, $logFile = null) {

        $this->key = $key;
        $this->secret = $secret;

        if (!is_null($logFile)) {
            $this->logFile = $logFile;
            $this->enableLogging = true;
        }

        parent::__construct('https', 'www.whalestack.com', '/api/' . $this->apiVersion);

    }

    /**
     * Use this method to communicate with GET endpoints
     *
     * @param string $endpoint
     * @param array $params, a list of GET parameters to be included in the request
     * @return CQ_Rest_Client_Response_Object
     */
    public function get($endpoint = '/', $params = array()) {

        $method = 'GET';
        $authHeaders = $this->buildAuthHeaders($endpoint, $method, $params);
        $response = parent::sendRequest($endpoint, $method, array(), false, $params, $authHeaders, $this->buildCustomOptions());
        $this->log("[CQMerchantClient][get] Request: GET $endpoint Params: " . json_encode($params) . " Auth Headers: " . json_encode($authHeaders));
        $this->log("[CQMerchantClient][get] Response: " . json_encode($response));
        return $response;

    }

    /**
     * Use this method to communicate with POST endpoints
     *
     * @param string $endpoint
     * @param array $params, an array representing the JSON payload to include in this request
     * @return CQ_Rest_Client_Response_Object
     */
    public function post($endpoint = '/', $params = array()) {

        $method = 'POST';
        $authHeaders = $this->buildAuthHeaders($endpoint, $method, $params);
        $response = $this->sendRequest($endpoint, $method, $params, true, array(), $authHeaders, $this->buildCustomOptions());
        $this->log("[CQMerchantClient][post] Request: GET $endpoint Params: " . json_encode($params) . " Auth Headers: " . json_encode($authHeaders));
        $this->log("[CQMerchantClient][post] Response: " . json_encode($response));
        return $response;
    }

    /**
     * Use this method to communicate with DELETE endpoints
     *
     * @param string $endpoint
     * @param array $params, an array representing the JSON payload to include in this request
     * @return CQ_Rest_Client_Response_Object
     */
    public function delete($endpoint = '/', $params = array()) {

        $method = 'DELETE';
        $authHeaders = $this->buildAuthHeaders($endpoint, $method, $params);
        $response = $this->sendRequest($endpoint, $method, $params, true, array(), $authHeaders, $this->buildCustomOptions());
        $this->log("[CQMerchantClient][delete] Request: DELETE $endpoint Params: " . json_encode($params) . " Auth Headers: " . json_encode($authHeaders));
        $this->log("[CQMerchantClient][delete] Response: " . json_encode($response));
        return $response;

    }

    /**
     * Use this method to communicate with PUT endpoints
     *
     * @param string $endpoint
     * @param array $params, an array representing the JSON payload to include in this request
     * @return CQ_Rest_Client_Response_Object
     */
    public function put($endpoint = '/', $params = array()) {

        $method = 'PUT';
        $authHeaders = $this->buildAuthHeaders($endpoint, $method, $params);
        $response = $this->sendRequest($endpoint, $method, $params, true, array(), $authHeaders, $this->buildCustomOptions());
        $this->log("[CQMerchantClient][put] Request: PUT $endpoint Params: " . json_encode($params) . " Auth Headers: " . json_encode($authHeaders));
        $this->log("[CQMerchantClient][put] Response: " . json_encode($response));
        return $response;

    }

    /**
     * Automatically generates authentication headers.
     *
     * @param $path
     * @param $method
     * @param array $params
     * @return array
     */
    private function buildAuthHeaders($path, $method, $params = array()) {

        $timestamp = self::fetchTimestamp();
        $body = $method != 'GET' ? (count($params) ? json_encode($params) : null) : null;
        $origin = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null;
        $version_data = new Init();

        return array(
            'X-Digest-Key: ' . $this->key,
            'X-Digest-Signature: ' . hash_hmac('sha256', $path . $timestamp . $method . $body, $this->secret),
            'X-Digest-Timestamp: ' . $timestamp,
            'X-Origin-URL: ' . $origin,
            'X-Plugin-Data: ' . $version_data->get_plugin_data()
        );

    }

    /**
     * Fetches server timestamp or falls back to local time on error
     *
     * @return int
     */
    private function fetchTimestamp() {

        $timestamp = time();
        $client = new CQ_REST_Client('https', 'www.whalestack.com', '/api/v1');

        $response = $client->sendRequest('/time', 'GET');
        if ($response->httpStatusCode != 200) {
            return $timestamp;
        }

        $data = json_decode($response->responseBody, true);
        return is_null($data) ? $timestamp : $data['time'];

    }

    /**
     * Private class to automatically generate the user agent in the request
     *
     * @return array
     */
    private function buildCustomOptions() {

        return array(CURLOPT_USERAGENT => $this->clientName . ' ' . $this->clientVersion . ' (' . $this->key . ')');

    }

    /**
     * Private class to optionally log API request and response
     *
     * @param $message
     */
    private function log($message) {

        if (!$this->enableLogging) {
            return;
        }

        CQ_Logging_Service::write($message);

    }

}







