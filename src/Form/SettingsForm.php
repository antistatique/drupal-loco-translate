<?php

namespace Drupal\loco_translate\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Loco\Http\ApiClient;

/**
 * Configure loco translate settings for this site.
 *
 * @internal
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'loco_translate_translate_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['loco_translate.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('loco_translate.settings');

    // Get configurations values for both mandatory API keys.
    $export_key = $config->get('api.export_key');
    $fullaccess_key = $config->get('api.fullaccess_key');

    if (!$export_key || !$fullaccess_key) {
      $this->messenger()->addWarning($this->t('Loco Translate requires your Export API key & Full Access API Key.<br/>Fill out the form below or keep secret by adding them to your <code>settings.php</code> file.<br/><small>You may find more informations about API keys on <a href=":loco-url" target="_blank">Loco support</a> pages.</small>', [
        ':loco-url' => 'https://localise.biz/help/developers/api-keys',
      ]));
    }

    $form['api'] = [
      '#type' => 'details',
      '#title' => $this->t('Loco API Keys'),
      // Close the details by default when any API keys is fill.
      '#open' => $export_key || $fullaccess_key ? FALSE : TRUE,
    ];
    $form['api']['export_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Export key'),
      '#description' => $this->t('This key provides read-only access to your data.'),
      '#default_value' => $config->get('api.export_key'),
    ];
    $form['api']['fullaccess_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full Access key'),
      '#description' => $this->t('This key provides read and write access to your data.'),
      '#default_value' => $config->get('api.fullaccess_key'),
    ];

    $form['automation'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Automation'),
    ];

    $form['gettext'] = [
      '#type' => 'details',
      '#title' => $this->t('Gettext'),
      '#open' => FALSE,
    ];

    $form['gettext']['path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path to gettext binaries files'),
      '#description' => $this->t('Enter the full path to <code>gettext</code> executable files. Example: "/var/gettext/bin". This may be overridden in settings.php'),
      '#required' => FALSE,
      '#default_value' => $config->get('gettext.path'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->validateKey($form_state->getValue('export_key'), $form['api']['export_key'], $form_state);
    $this->validateKey($form_state->getValue('fullaccess_key'), $form['api']['fullaccess_key'], $form_state);
    $this->validatePath($form_state);

    parent::validateForm($form, $form_state);
  }

  /**
   * Validate the optional given API Key.
   *
   * @param string $key
   *   The API Key to validate.
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  private function validateKey($key, array &$form, FormStateInterface $form_state) {
    // Allow the value to be empty.
    if (empty($key)) {
      return;
    }
    $client = ApiClient::factory([
      'key' => $key,
    ]);

    try {
      /* @var \GuzzleHttp\Command\Result */
      $client->authVerify();
    }
    catch (\Exception $e) {
      $form_state->setError($form, $e->getMessage());
    }
  }

  /**
   * Validate the optional Gettext path value.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  private function validatePath(FormStateInterface $form_state) {
    // Allow the value to be empty.
    if (empty($form_state->getValue('path'))) {
      return;
    }

    // Collection of utilities that must be executable in the gettext $path.
    $utilities = [
      'autopoint',
      'gettext',
      'gettextize',
      'msgcat',
      'msgcomm',
      'msgen',
      'msgfilter',
      'msggrep',
      'msgmerge',
      'msguniq',
      'recode-sr-latin',
      'envsubst',
      'msgattrib',
      'msgcmp',
      'msgconv',
      'msgexec',
      'msgfmt',
      'msginit',
      'msgunfmt',
      'ngettext',
      'xgettext',
    ];

    $path = $form_state->getValue('path');
    if (!is_dir($path)) {
      $form_state->setErrorByName('path', $this->t("The directory %directory does not exist.", ['%directory' => $path]));
    }
    else {
      foreach ($utilities as $utility) {
        if (!is_file($path . $utility)) {
          $form_state->setErrorByName('path', $this->t("The utility %utility does not exist.", ['%utility' => $path . $utility]));
        }
        if (!is_executable($path . $utility)) {
          $form_state->setErrorByName('path', $this->t("The utility %utility is not executable.", ['%utility' => $path . $utility]));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $config = $this->config('loco_translate.settings');
    $config->set('api.export_key', $values['export_key'])->save();
    $config->set('api.fullaccess_key', $values['fullaccess_key'])->save();
    $config->set('gettext.path', $values['path'])->save();

    parent::submitForm($form, $form_state);
  }

}
