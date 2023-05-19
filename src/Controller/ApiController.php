<?php

namespace Drupal\migrate_content\Controller;

use Drupal\migrate_content\Model\Credentials;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Controller for handling API calls
 */
class ApiController
{

    /**
     * @var Client
     */
    private Client $httpClient;

    /**
     * @var Credentials|null
     */
    private Credentials $credentials;

    /**
     * @param LoginController $loginController
     */
    public function __construct(LoginController $loginController)
    {
        $this->httpClient = new Client();
        $this->credentials = $loginController->getCredentials();
    }

    // Function to send a request

    /**
     * @param $method
     * @param $urlPath
     * @param $headers
     * @param $body
     * @return \Psr\Http\Message\StreamInterface|null
     */
    public function sendRequest($method, $urlPath, $headers = [], $body = NULL)
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->credentials->getUsername() . ':' . $this->credentials->getPassword()),
            'X-CSRF-Token' => $this->credentials->getCsrfToken(),
        ];

        $url = $this->credentials->getSiteUrl() . $urlPath;
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

    /**
     * @param string $contentType
     * @return void
     */
    public function getContentType(string $contentType)
    {

    }

    /**
     * @param string $contentType
     *
     * @return bool
     */
    public function checkIfUidAlreadyExists(string $contentType): bool
    {
        //if true, update
        //if false, insert
        return FALSE;
    }

    /**
     * @param $nodePayload
     * @return void
     */
    public function patchNode($nodePayload)
    {

    }

    /**
     * @param $nodePayload
     * @return void
     */
    public function postNode($nodePayload)
    {

        $urlPath = '/node?_format=json';
        $responseBody = $this->sendRequest('POST', $urlPath, [], $nodePayload);


    }

    /**
     * @return void
     */
    public function postFiles()
    {

    }

    /**
     * @param $uid
     * @return \Psr\Http\Message\StreamInterface|null
     */
    public function getNode($uid): ?\Psr\Http\Message\StreamInterface
    {
        $urlPath = '/node/' . $uid . '?_format=json';

        // Send the GET request.
        return $this->sendRequest('GET', $urlPath);
    }

}
