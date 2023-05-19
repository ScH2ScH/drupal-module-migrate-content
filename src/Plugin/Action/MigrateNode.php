<?php

namespace Drupal\migrate_content\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\file\Entity\File;
use Drupal\migrate_content\Controller\ApiController;
use Drupal\migrate_content\Controller\LoginController;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides an Archive Node Action.
 *
 * @Action(
 *   id = "migrate_content_migrate_node",
 *   label = @Translation("Migrate Node"),
 *   type = "node",
 *   category = @Translation("Custom")
 * )
 */
class MigrateNode extends ActionBase implements ContainerFactoryPluginInterface
{

    /**
     * The Messenger service.
     *
     * @var \Drupal\Core\Messenger\MessengerInterface
     */
    protected $messenger;

    /**
     * The LoginController.
     *
     * @var \Drupal\migrate_content\Controller\LoginController
     */
    protected $loginController;

    /**
     * The ApiController.
     *
     * @var \Drupal\migrate_content\Controller\ApiController
     */
    protected $apiController;

    /**
     * Sets the MessengerInterface dependency.
     *
     * @param \Drupal\Core\Messenger\MessengerInterface $messenger
     *   The Messenger service.
     */
    public function setMessenger(MessengerInterface $messenger)
    {
        $this->messenger = $messenger;
    }

    /**
     * Sets the LoginController dependency.
     *
     * @param \Drupal\migrate_content\Controller\LoginController $loginController
     *   The LoginController service.
     */
    public function setLoginController(LoginController $loginController)
    {
        $this->loginController = $loginController;
    }

    /**
     * Sets the ApiController dependency.
     *
     * @param \Drupal\migrate_content\Controller\ApiController $apiController
     *   The ApiController service.
     */
    public function setApiController(ApiController $apiController)
    {
        $this->apiController = $apiController;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
    {
        $instance = new static(
            $configuration,
            $plugin_id,
            $plugin_definition
        );
        $instance->setMessenger($container->get('messenger'));
        $instance->setLoginController($container->get('migrate_content.login_controller'));
        $instance->setApiController($container->get('migrate_content.api_controller'));
        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function access($node, AccountInterface $account = NULL, $return_as_object = FALSE)
    {

        if ($this->loginController->isLoggedInToOtherInstance()) {
            return TRUE;
        }
        // Create a custom error message with a link.
        $error_message = $this->t("You don't have access to execute Migrate content on this Content. Please <a href='@login'>login</a> to other Drupal instance to perform this action.", [
            '@login' => Url::fromRoute('migrate_content.web_services_link')
                ->toString(),
        ]);
        // Set the error message in the Drupal message system.
        $this->messenger->addError($error_message);

        return FALSE;
    }

    /**
     * {@inheritdoc}
     */
    #[NoReturn] public function execute($node = NULL)
    {

        if (!$this->remoteContentTypeExistAndHasSameConfiguration($node->getType())) {
            $message = $this->t('Node with NID : @id hasn\'t been migrated, content types definitions are not the same.', ['@id' => $node->id()]);
            $this->messenger->addMessage($message);

            return;
        }

        $nodePayload = $this->prepareNodePayload($node);
        if ($this->remoteNodeWithSameUuidExist($node->uuid())) {
            //update
            $this->apiController->patchNode($nodePayload);
            $message = $this->t('Node with NID : @id has been updated.', ['@id' => $node->id()]);
        }
        else {
            //insert
            $this->apiController->postNode($nodePayload);
            $message = $this->t('Node with NID : @id has been migrated.', ['@id' => $node->id()]);
        }

        //post related files
        $this->apiController->postFiles();


        $this->messenger->addMessage($message);

    }

    /**
     * @param $node
     * @return array
     */
    private function prepareNodePayload($node)
    {
        $nodePayload = [
            'type' => $node->getType(),
            'title' => $node->getTitle(),
        ];

        // Dynamically discover and include the fields in the payload.
        $fieldDefinitions = $node->getFieldDefinitions();
        foreach ($fieldDefinitions as $fieldName => $fieldDefinition) {
            // Exclude non-data fields and certain field types if needed.
            $fieldValue = $node->get($fieldName)->getValue();
            if ($fieldDefinition->getType() == !'file') {
                $nodePayload[$fieldName] = $fieldValue;
            }
        }

        /* these few cannot be transferred */
        unset($nodePayload['nid']);
        unset($nodePayload['revision_timestamp']);
        unset($nodePayload['revision_uid']);
        unset($nodePayload['changed']);

        return $nodePayload;
    }

    /**
     * @param $node
     * @return array
     */
    private function prepareNodeFilePayload($node): array
    {
        $filePayloads = [];
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
        }

        return $filePayloads;
    }

    /**
     * @param $uuid
     * @return false
     */
    private function remoteNodeWithSameUuidExist($uuid)
    {
        return false;
    }

    /**
     * @param $content_type
     * @return true
     */
    private function remoteContentTypeExistAndHasSameConfiguration($content_type)
    {
        return true;
    }

}
