<?php

namespace Drupal\migrate_content\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Markup;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Main controller
 */
class MigrateContentController extends ControllerBase
{

    /**
     * @return RedirectResponse
     */
    public function webServicesLinkPage(): RedirectResponse
    {
        // Generate the URL for the ConnectForm route.
        $routeName = 'migrate_content.connect_form';
        $url = Url::fromRoute($routeName)->toString();
        return new RedirectResponse($url);
    }
}

