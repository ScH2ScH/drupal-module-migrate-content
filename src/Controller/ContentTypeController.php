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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Controller for handling content type related operations.
 */
class ContentTypeController extends ControllerBase
{


  /*
   * logic:
   * get selected nids
   * foreach
    * check if node content type exist on prod and have same definitions
    * check if uids exist on prod server, insert if not, update if yes
    * transfer files, delete files not in use
   * put the above in queue job
   */

  /**
   * @return \Psr\Http\Message\ResponseInterface|null
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  function getNodeInformation() {
    $tempstore = \Drupal::service('tempstore.private')->get('migrate_content');
    $username = $tempstore->get('username');
    $password = $tempstore->get('password');
    $siteUrl = $tempstore->get('server_URL');
    $url = $siteUrl . '/node/' . '2' . '?_format=json';

    // Set up the headers.
    $headers = [
      'Accept' => 'application/json',
      'Content-Type' => 'application/json',
      'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
    ];

    // Send the GET request.
    $httpClient = \Drupal::httpClient();
    try {
      $response = $httpClient->request('GET', $url, [
        'headers' => $headers,
      ]);
      return $response;
    } catch (\Exception $e) {
      // Handle exception if needed.

      echo $e->getMessage();
      return null;
    }
  }
  public function listContentTypes()
  {



    $tempstore = \Drupal::service('tempstore.private')->get('migrate_content');
    $username = $tempstore->get('username');
    $password = $tempstore->get('password');
    $siteUrl = $tempstore->get('server_URL');
    $csrf_token = $tempstore->get('csrf_token');

    if($response = $this->getNodeInformation()){
      $body = $response->getBody()->getContents();
      $data = json_decode($body, TRUE);
      var_dump($data);
    }

//    $serialized_entity = json_encode([
//      'title' => [['value' => 'Example node title']],
//      'type' => [['target_id' => 'article']],
//      '_links' => ['type' => [
//        'href' => $siteUrl. '/rest/type/node/article'
//      ]],
//    ]);
//
//    $response = \Drupal::httpClient()
//      ->post($siteUrl . '/entity/node?_format=hal_json', [
//        'auth' => [$username, $password],
//        'form_params' => $serialized_entity,
//        'headers' => [
//            'Content-Type' => 'application/hal+json',
//            'X-CSRF-Token' => $csrf_token
//      ],
//    ]);
//
//    dd($response);
    $nodePayload = $this->getNodes();
    unset($nodePayload['nid']);
    unset($nodePayload['revision_timestamp']);
    unset($nodePayload['revision_uid']);
    unset($nodePayload['changed']);
    //dd($nodePayload);
    $url = $siteUrl . '/node?_format=json';
    $headers = [
      'Accept' => 'application/json',
      'Content-Type' => 'application/json',
      'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
      'X-CSRF-Token' => $csrf_token
    ];
    $httpClient = \Drupal::httpClient();
    try {
      $response = $httpClient->request('POST', $url, [
        'headers' => $headers,
        'json' => $nodePayload,
      ]);
      echo $response->getBody();
    } catch (\Exception $e) {
      // Handle exception if needed.
      echo $e->getMessage();
    }
die();
    return [];
  }


  function getNodes(string $nodeType = 'article') {
    $nodeStorage = \Drupal::entityTypeManager()->getStorage('node');
    $query = $nodeStorage->getQuery()
      ->condition('type', $nodeType)
      ->condition('status', 1) // Only published nodes.
      ->sort('created', 'DESC');
    $nids = $query->execute();
    $nodes = $nodeStorage->loadMultiple($nids);
    $data = [];

    foreach ($nodes as $node)
    {
      $nodePayload = [
        'type' => $node->getType(),
        'title' => $node->getTitle(),
      ];
      $filePayloads = [];
      // Dynamically discover and include the fields in the payload.
      $fieldDefinitions = $node->getFieldDefinitions();
      foreach ($fieldDefinitions as $fieldName => $fieldDefinition) {
        // Exclude non-data fields and certain field types if needed.
        $fieldValue = $node->get($fieldName)->getValue();
        if ($fieldDefinition->getType() === 'file') {
          $file = File::load($fieldValue[0]['target_id']);
          if ($file) {
            $filePayloads[] = [
              'filename' => $file->getFilename(),
              'file_data' => base64_encode(file_get_contents($file->getFileUri())),
              // Add any other file-related fields you want to include.
            ];
          }
        } else {
          $nodePayload[$fieldName] = $fieldValue;
        }
      }
      //$data[] = array_merge($nodePayload, $filePayloads);
    }
  return $nodePayload;
    //var_dump($data);
  }


  /**
   * Exports a content type configuration.
   *
   * @param string $content_type
   *   The machine name of the content type to export.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  public function exportContentType($content_type) {
    // Load the content type entity.
    $contentType = $this->entityTypeManager()->getStorage('node_type')->load($content_type);

    // Export the content type configuration to YAML.
    $configFactory = \Drupal::configFactory();
    $configExportService = \Drupal::service('config.export');
    $yaml = $configExportService->exportOne('node.type.' . $content_type);

    // Create a response with the YAML content.
    $response = new Response($yaml);
    $response->headers->set('Content-Type', 'text/plain');
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $content_type . '.yml"');

    return $response;
  }

}

