<?php

namespace Drupal\migrate_content\Model;

/**
 *
 */
class Credentials
{

    /**
     * @var string
     */
    protected string $username;

    /**
     * @var string
     */
    protected string $password;

    /**
     * @var string
     */
    protected string $siteUrl;

    /**
     * @var string
     */
    protected string $csrfToken;

    /**
     * @param $username
     * @param $password
     * @param $siteUrl
     * @param $csrfToken
     */
    public function __construct($username, $password, $siteUrl, $csrfToken)
    {
        $this->username = $username;
        $this->password = $password;
        $this->siteUrl = $siteUrl;
        $this->csrfToken = $csrfToken;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getSiteUrl(): string
    {
        return $this->siteUrl;
    }

    /**
     * @return string
     */
    public function getCsrfToken(): string
    {
        return $this->csrfToken;
    }
}
