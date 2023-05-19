<?php

namespace Drupal\migrate_content\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\migrate_content\Controller\LoginController;
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
class MigrateNode extends ActionBase implements ContainerFactoryPluginInterface {

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
     * Sets the MessengerInterface dependency.
     *
     * @param \Drupal\Core\Messenger\MessengerInterface $messenger
     *   The Messenger service.
     */
    public function setMessenger(MessengerInterface $messenger) {
        $this->messenger = $messenger;
    }

    /**
     * Sets the LoginController dependency.
     *
     * @param \Drupal\migrate_content\Controller\LoginController $loginController
     *   The LoginController service.
     */
    public function setLoginController(LoginController $loginController) {
        $this->loginController = $loginController;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        $instance = new static(
            $configuration,
            $plugin_id,
            $plugin_definition
        );
        $instance->setMessenger($container->get('messenger'));
        $instance->setLoginController($container->get('migrate_content.login_controller'));
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
    public function execute($node = NULL)
    {

        $message = $this->t('Node with NID : @id Migratedd.', ['@id' => $node->id()]);

        $this->messenger->addMessage($message);

        //    /** @var \Drupal\node\NodeInterface $node */
        //
        //    $language = $this->languageManager->getCurrentLanguage()->getId();
        //
        //    $old_alias = $this->aliasManager->getAliasByPath('/node/' . $node->id(), $language);
        //
        //    $title = $node->getTitle();
        //    $date = $node->created->value;
        //    $year = date('Y', $date);
        //    // $old_alias = $node->path->alias;
        //    $new_title = $this->t('[Archive] | @title', ['@title' => $title]);
        //    $node->setTitle($new_title);
        //    $node->setSticky(FALSE);
        //    $node->setPromoted(FALSE);
        //
        //    $new_alias = '/archive/' . $year . $old_alias;
        //    $node->set("path", [
        //      'alias' => $new_alias,
        //      'langcode' => $language,
        //      'pathauto' => PathautoState::SKIP,
        //    ]);
        //
        //    $node->save();
        //
        //    $message = $this->t('Node with NID : @id Archived.', ['@id' => $node->id()]);
        //
        //    $this->logger->notice($message);
        //    $this->messenger->addMessage($message);

    }

}
