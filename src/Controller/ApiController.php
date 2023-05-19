<?php

namespace Drupal\migrate_content\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Controller for handling API calls
 */
class ApiController {

    private Client $httpClient;

    private string $username;

    private string $password;
    private string $siteUrl;

    private string $csrf_token;

    public function __construct() {
        $this->httpClient = new Client();
        [
            $this->username,
            $this->password,
            $this->siteUrl,
            $this->csrf_token,
        ] = SessionController::getCredentialsFromSession();
    }

    // Function to send a request
    public function sendRequest($method, $urlPath, $headers = [], $body = NULL)
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->password),
            'X-CSRF-Token' => $this->csrf_token,
        ];

        $url = $this->siteUrl . $urlPath;
        $options = [
            'headers' => $headers,
        ];

        if ($method === 'POST') {
            $options['json'] = $body;
        }

        try {
            $response = $this->httpClient->request($method, $url, $options);

            return $response->getBody();
        }
        catch (GuzzleException $e) {
            // Handle exception if needed.
            echo $e->getMessage();
            return NULL;
        }
    }

    public function getContentType(string $contentType) {

    }

    /**
     * @param string $contentType
     *
     * @return bool
     */
    public function checkIfUidAlreadyExists(string $contentType): bool {
        //if true, update
        //if false, insert
        return FALSE;
    }

    public function patchContent() {

    }

    public function postContent($nodePayload) {

        /* these few cannot be transferred */
        unset($nodePayload['nid']);
        unset($nodePayload['revision_timestamp']);
        unset($nodePayload['revision_uid']);
        unset($nodePayload['changed']);


        $urlPath = '/node?_format=json';
        $responseBody = $this->sendRequest('POST', $urlPath);
        var_dump($responseBody->getContents());
        die();
        return [];
    }

    public function postFiles() {

    }

    public function getNode($uid)
    {
        $urlPath = '/node/' . $uid . '?_format=json';

        // Send the GET request.
        $response = $this->sendRequest('GET', $urlPath);

        return $response;
    }

}
