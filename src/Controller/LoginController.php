<?php

namespace Drupal\migrate_content\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Helper;

class LoginController
{

    public function login($username, $password, $siteUrl) {
        // Create a Guzzle HTTP client.
        $client = new Client();
        $messenger = \Drupal::messenger();
        try {
            // Make a POST request to the Drupal site's user login endpoint to authenticate and fetch the token.
            $response = $client->post($siteUrl . '/user/login?_format=json', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'name' => $username,
                    'pass' => $password,
                ],
            ]);

            // Get the token from the response.
            $data = json_decode($response->getBody()->getContents(), TRUE);
            SessionController::setCredentialsToSession($username, $password, $siteUrl, $data['csrf_token']);

        }
        catch (ClientException $e) {
            // Handle authentication error.
            if ($e->getCode() === 401) {
                // Authentication failed.
                // Handle the error condition here.
                $messenger->addError('Authentication failed. Please check your credentials.');
            }
            else {
                // Other client exception occurred.
                // Handle the error condition here.
                $messenger->addError('An error occurred during the API request.');
            }
        }
        catch (\Exception $e) {
            // Other generic exception occurred.
            // Handle the error condition here.
            $messenger->addError('An error occurred during the API request.');
        }
    }

    /**
     * @return \Drupal\migrate_content\Model\Credentials
     */
    public function getCredentials(): ?\Drupal\migrate_content\Model\Credentials {
        return SessionController::getCredentialsFromSession();
    }

    /**
     * @return bool
     */
    public function isLoggedInToOtherInstance(): bool
    {
        return $this->getCredentials() !== NULL;
    }

    public function logout(): void
    {
        SessionController::emptySession();
    }
}
