<?php

namespace Drupal\migrate_content\Controller;

use Drupal\migrate_content\Model\Credentials;
use Helper;

class SessionController
{

    /**
     * @param string $username
     * @param string $password
     * @param string $siteUrl
     * @param string $csrf_token
     *
     * @return void
     */
    public static function setCredentialsToSession(string $username, string $password, string $siteUrl, string $csrf_token): void
    {
        $credentials = new Credentials($username, $password, $siteUrl, $csrf_token);
        $tempstore = self::getTempStore();
        $tempstore->set('credentials', $credentials);
    }


    /**
     * @return Credentials|null
     */
    public static function getCredentialsFromSession(): ?Credentials
    {
        $tempstore = self::getTempStore();
        return $tempstore->get('credentials');
    }

    public static function emptySession(): void
    {
        $tempstore = self::getTempStore();
        $tempstore->delete('credentials');
    }

    /**
     * @return mixed
     */
    public static function getTempStore(): mixed
    {
        return \Drupal::service('tempstore.private')->get('migrate_content');
    }
}
