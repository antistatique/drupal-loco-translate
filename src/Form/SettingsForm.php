<?php

namespace Drupal\loco_translate\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Loco\Http\ApiClient;

/**
 * Configure loco translate settings for this site.
 *
 * @internal
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The configurable language manager.
   *
   * @var \Drupal\language\ConfigurableLanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a loco translate settings form object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\language\ConfigurableLanguageManagerInterface $language_manager
   *   The configurable language manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ConfigurableLanguageManagerInterface $language_manager) {
    parent::__construct($config_factory);
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('language_manager')
    );
  }

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
    $languages = $this->languageManager->getLanguages();

    // Initialize a language list to the ones available.
    $language_options = [];
    foreach ($languages as $langcode => $language) {
      $language_options[$langcode] = $language->getName();
    }

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
      '#tree' => TRUE,
      '#type' => 'details',
      '#title' => $this->t('Automation'),
      '#description' => $this->t('Automation takes care of running periodic tasks like pulling translations from Loco or pushing new assets to Loco.'),
      // Open the details by default when at least one API keys is fill.
      '#open' => $export_key || $fullaccess_key ? TRUE : FALSE,
    ];

    $form['automation']['push'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Push'),
    ];
    $form['automation']['push']['interval'] = [
      '#type' => 'select',
      '#title' => $this->t('Run push every'),
      '#options' => [
        0 => $this->t('Never'),
        3600 => $this->t('1 hour'),
        10800 => $this->t('3 hours'),
        21600 => $this->t('6 hours'),
        43200 => $this->t('12 hours'),
        86400 => $this->t('1 day'),
        604800 => $this->t('1 week'),
      ],
      '#default_value' => $config->get('automation.push.interval'),
    ];
    $form['automation']['push']['template'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Template'),
      '#description' => $this->t('Template file containing assets to be pushed on Loco.'),
      '#default_value' => $config->get('automation.push.template'),
      '#states' => [
        'invisible' => [
          ':input[name="automation[push][interval]"]' => ['value' => 0],
        ],
      ],
    ];
    $form['automation']['push']['langcodes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Language'),
      '#options' => $language_options,
      '#default_value' => $config->get('automation.push.langcodes'),
      '#states' => [
        'invisible' => [
          ':input[name="automation[push][interval]"]' => ['value' => 0],
        ],
      ],
    ];

    $form['automation']['pull'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Import from Loco'),
    ];
    $form['automation']['pull']['interval'] = [
      '#type' => 'select',
      '#title' => $this->t('Run every'),
      '#options' => [
        0 => $this->t('Never'),
        3600 => $this->t('1 hour'),
        10800 => $this->t('3 hours'),
        21600 => $this->t('6 hours'),
        43200 => $this->t('12 hours'),
        86400 => $this->t('1 day'),
        604800 => $this->t('1 week'),
      ],
      '#default_value' => $config->get('automation.pull.interval'),
    ];
    $form['automation']['pull']['langcodes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Language'),
      '#options' => $language_options,
      '#default_value' => $config->get('automation.pull.langcodes'),
      '#states' => [
        'invisible' => [
          ':input[name="automation[pull][interval]"]' => ['value' => 0],
        ],
      ],
    ];
    $form['automation']['pull']['status'] = [
      '#type' => 'select',
      '#title' => $this->t('Status', [], ['context' => 'Loco Translate']),
      '#options' => [
        '_none' => $this->t('All'),
        'translated' => $this->t('Only translated'),
        'fuzzy' => $this->t('Only fuzzy'),
      ],
      '#description' => $this->t('Pull translations with a specific status.'),
      '#default_value' => $config->get('automation.pull.status') ?? 'translated',
      '#states' => [
        'invisible' => [
          ':input[name="automation[pull][interval]"]' => ['value' => 0],
        ],
      ],
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

    // Push config.
    $config->set('automation.push.interval', $values['automation']['push']['interval'])->save();
    $config->set('automation.push.template', $values['automation']['push']['template'])->save();
    $config->set('automation.push.langcodes', $values['automation']['push']['langcodes'])->save();

    // Pull config.
    $config->set('automation.pull.interval', $values['automation']['pull']['interval'])->save();
    $config->set('automation.pull.langcodes', $values['automation']['pull']['langcodes'])->save();
    $status = $values['automation']['pull']['status'] != '_none' ? $values['automation']['pull']['status'] : NULL;
    $config->set('automation.pull.status', $status)->save();

    parent::submitForm($form, $form_state);
  }

}
