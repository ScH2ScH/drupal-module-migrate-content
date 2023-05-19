<?php

namespace Drupal\migrate_content\Model;

class Credentials
{
    protected string $username;
    protected string $password;
    protected string $siteUrl;
    protected string $csrfToken;

    public function __construct($username, $password, $siteUrl, $csrfToken)
    {
        $this->username = $username;
        $this->password = $password;
        $this->siteUrl = $siteUrl;
        $this->csrfToken = $csrfToken;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getSiteUrl(): string
    {
        return $this->siteUrl;
    }

    public function getCsrfToken(): string
    {
        return $this->csrfToken;
    }
}
