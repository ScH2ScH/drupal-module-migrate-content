<?php
namespace Drupal\migrate_content\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConnectForm extends FormBase {

  public function getFormId() {
    return 'example_form';
  }

  protected $messenger;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): ConnectForm|static {
    return new static(
      $container->get('messenger')
    );
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $tempstore = \Drupal::service('tempstore.private')->get('migrate_content');
    if ($tempstore->get('username') === null) {
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
    } else {
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

  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Perform custom validation if needed.
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Handle form submission.
    $values = $form_state->getValues();
    // Access the form values using $values['server_url'], $values['email'], $values['password'].

    // Replace with the base URL of the Drupal site you want to connect to.
    $siteUrl = $values['server_url'];

    // Replace with the username and password of a valid Drupal user.
    $username = $values['username'];
    $password = $values['password'];

    // Create a Guzzle HTTP client.
    $client = new Client();

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
      $tempstore = \Drupal::service('tempstore.private')->get('migrate_content');
      $tempstore->set('username', $username);
      $tempstore->set('password', $password);
      $tempstore->set('server_URL', $siteUrl);
      $tempstore->set('csrf_token', $data['csrf_token']);


    } catch (ClientException $e) {
      // Handle authentication error.
      if ($e->getCode() === 401) {
        // Authentication failed.
        // Handle the error condition here.
        $this->messenger->addError('Authentication failed. Please check your credentials.');
      } else {
        // Other client exception occurred.
        // Handle the error condition here.
        $this->messenger->addError('An error occurred during the API request.');
      }
    } catch (\Exception $e) {
      // Other generic exception occurred.
      // Handle the error condition here.
      $this->messenger->addError('An error occurred during the API request.');
    }


  }

  public function logoutSubmit(array &$form, FormStateInterface $form_state) {
    // Clear the stored CSRF token or perform any other necessary logout actions.
    $tempstore = \Drupal::service('tempstore.private')->get('migrate_content');
    $tempstore->delete('username');
    $tempstore->delete('password');
    $tempstore->delete('server_URL');

    // Display a logout message to the user.
    $this->messenger->addMessage($this->t('You have been logged out.'));
  }

}
