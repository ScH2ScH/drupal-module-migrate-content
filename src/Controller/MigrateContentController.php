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
 * Controller for handling content type related operations.
 */
class MigrateContentController extends ControllerBase {


    /*
     * logic:
     * get selected nids
     * foreach
      * check if node content type exist on prod and have same definitions
      * check if uids exist on prod server, insert if not, update if yes
      * transfer files, delete files not in use
     * put the above in queue job
     */

    public function webServicesLinkPage() {
        // Generate the URL for the ConnectForm route.
        $routeName = 'migrate_content.connect_form';
        $url = Url::fromRoute($routeName)->toString();
        return new RedirectResponse($url);
    }

    public function listContentTypes() {


//        $tempstore = \Drupal::service('tempstore.private')
//            ->get('migrate_content');
//        $username = $tempstore->get('username');
//        $password = $tempstore->get('password');
//        $siteUrl = $tempstore->get('server_URL');
//        $csrf_token = $tempstore->get('csrf_token');
//
//        if ($response = $this->getNodeInformation()) {
//            $body = $response->getBody()->getContents();
//            $data = json_decode($body, TRUE);
//            var_dump($data);
//        }


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

        foreach ($nodes as $node) {
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
                }
                else {
                    $nodePayload[$fieldName] = $fieldValue;
                }
            }
            //$data[] = array_merge($nodePayload, $filePayloads);
        }
        return $nodePayload;
        //var_dump($data);
    }


//    /**
//     * Exports a content type configuration.
//     *
//     * @param string $content_type
//     *   The machine name of the content type to export.
//     *
//     * @return \Symfony\Component\HttpFoundation\Response
//     *   The response.
//     */
//    public function exportContentType($content_type): Response {
//        // Load the content type entity.
//        $contentType = $this->entityTypeManager()
//            ->getStorage('node_type')
//            ->load($content_type);
//
//        // Export the content type configuration to YAML.
//        $configFactory = \Drupal::configFactory();
//        $configExportService = \Drupal::service('config.export');
//        $yaml = $configExportService->exportOne('node.type.' . $content_type);
//
//        // Create a response with the YAML content.
//        $response = new Response($yaml);
//        $response->headers->set('Content-Type', 'text/plain');
//        $response->headers->set('Content-Disposition', 'attachment; filename="' . $content_type . '.yml"');
//
//        return $response;
//    }

}
