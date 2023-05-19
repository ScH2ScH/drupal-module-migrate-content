<?php

namespace Drupal\migrate_content\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\migrate_content\Controller\ApiController;
use Drupal\migrate_content\Controller\LoginController;
use Drupal\migrate_content\Controller\SessionController;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class ConnectForm extends FormBase
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
     * Class constructor.
     *
     * @param \Drupal\Core\Messenger\MessengerInterface $messenger
     *   The messenger service.
     */
    public function __construct(MessengerInterface $messenger)
    {
        $this->messenger = $messenger;
    }

    /**
     * @return string
     */
    public function getFormId()
    {
        return 'example_form';
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container): ConnectForm|static
    {
        $form = new static(
            $container->get('messenger')
        );
        $form->loginController = $container->get('migrate_content.login_controller');
        return $form;
    }

    /**
     * @param array $form
     * @param FormStateInterface $form_state
     * @return array
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {

        if (!$this->loginController->isLoggedInToOtherInstance()) {
            $form['server_url'] = [
                '#type' => 'textfield',
                '#title' => $this->t('Server URL'),
                '#required' => TRUE,
            ];

            $form['username'] = [
                '#type' => 'textfield',
                '#title' => $this->t('Username'),
                '#required' => TRUE,
            ];

            $form['password'] = [
                '#type' => 'password',
                '#title' => $this->t('Password'),
                '#required' => TRUE,
            ];

            $form['actions'] = [
                '#type' => 'actions',
                'submit' => [
                    '#type' => 'submit',
                    '#value' => $this->t('Submit'),
                ],
            ];
        }
        else {
            $form['message'] = [
                '#markup' => '<p>' . $this->t('Already connected') . '</p>',
            ];
            $form['logout'] = [
                '#type' => 'submit',
                '#value' => $this->t('Logout'),
                '#submit' => ['::logoutSubmit'],
                '#button_type' => 'primary',
            ];
        }

        return $form;
    }

    /**
     * @param array $form
     * @param FormStateInterface $form_state
     * @return void
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        // Perform custom validation if needed.
    }

    /**
     * @param array $form
     * @param FormStateInterface $form_state
     * @return void
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        // Handle form submission.
        $values = $form_state->getValues();
        // Access the form values using $values['server_url'], $values['email'], $values['password'].
        $siteUrl = $values['server_url'];
        $username = $values['username'];
        $password = $values['password'];

        $this->loginController->login($username, $password, $siteUrl);
    }

    /**
     * @param array $form
     * @param FormStateInterface $form_state
     * @return void
     */
    public function logoutSubmit(array &$form, FormStateInterface $form_state)
    {
        // Clear the stored CSRF token or perform any other necessary logout actions.
        $this->loginController->logout();

        // Display a logout message to the user.
        $this->messenger->addMessage($this->t('You have been logged out.'));
    }

}
