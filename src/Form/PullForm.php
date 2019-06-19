<?php

namespace Drupal\loco_translate\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\loco_translate\Utility;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\loco_translate\Loco\Pull as LocoPull;
use Drupal\loco_translate\TranslationsImport;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form constructor for the translation pull screen.
 *
 * @internal
 */
class PullForm extends FormBase {

  /**
   * The Utility service of Loco Translate.
   *
   * @var \Drupal\loco_translate\Utility
   */
  protected $utility;

  /**
   * The configurable language manager.
   *
   * @var \Drupal\language\ConfigurableLanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The Loco translations pull manager.
   *
   * @var \Drupal\loco_translate\Loco\Pull
   */
  protected $locoPull;

  /**
   * The Translation importer.
   *
   * @var \Drupal\loco_translate\TranslationsImport
   */
  protected $translationsImport;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('loco_translate.utility'),
      $container->get('language_manager'),
      $container->get('file_system'),
      $container->get('loco_translate.loco_api.pull'),
      $container->get('loco_translate.translations.import')
    );
  }

  /**
   * Constructs a form for language pull.
   *
   * @param \Drupal\loco_translate\Utility $utility
   *   The Utility service of Loco Translate.
   * @param \Drupal\language\ConfigurableLanguageManagerInterface $language_manager
   *   The configurable language manager.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\loco_translate\Loco\Pull $loco_pull
   *   The Loco translations pull manager.
   * @param \Drupal\loco_translate\TranslationsImport $translatons_import
   *   The Translation importer.
   */
  public function __construct(Utility $utility, ConfigurableLanguageManagerInterface $language_manager, FileSystemInterface $file_system, LocoPull $loco_pull, TranslationsImport $translatons_import) {
    $this->utility = $utility;
    $this->languageManager = $language_manager;
    $this->fileSystem = $file_system;
    $this->locoPull = $loco_pull;
    $this->translationsImport = $translatons_import;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'loco_translate_pull_form';
  }

  /**
   * Form constructor for the translation import screen.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $languages = $this->languageManager->getLanguages();

    // Initialize a language list to the ones available.
    $language_options = [];
    foreach ($languages as $langcode => $language) {
      $language_options[$langcode] = $language->getName();
    }

    $form['langcodes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Language'),
      '#options' => $language_options,
    ];

    $form['status'] = [
      '#type' => 'select',
      '#title' => $this->t('Flag'),
      '#options' => [
        '_none' => $this->t('None'),
        'translated' => $this->t('Translated'),
        '!translated' => $this->t('Untranslated'),
        'fuzzy' => $this->t('Fuzzy'),
        '!fuzzy' => $this->t('Unuzzy'),
      ],
      '#default_value' => 'translated',
      '#description' => $this->t('Pull translations with a specific status or flag.'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Pull'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $langcodes = $form_state->getValue('langcodes');
    $status = $form_state->getValue('status') != '_none' ? $form_state->getValue('status') : NULL;

    foreach ($langcodes as $langcode) {
      // Skip unchecked langcode.
      if ($langcode === 0) {
        continue;
      }

      try {
        $this->locoPull->setApiClientFromConfig();
        $response = $this->locoPull->fromLocoToDrupal($langcode, $status);

        /** @var \Drupal\file\FileInterface $file */
        $file = file_save_data($response->__toString(), 'translations://');
        $form_state->setValue('files[' . $langcode . ']', $this->fileSystem->realPath($file->getFileUri()));
      }
      catch (\Exception $e) {

        $form_state->setError($form, $e->getMessage());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $langcodes = $form_state->getValue('langcodes');

    foreach ($langcodes as $langcode) {
      // Skip unchecked langcode.
      if ($langcode === 0) {
        continue;
      }

      $path = $form_state->getValue('files[' . $langcode . ']');

      try {
        $report = $this->translationsImport->fromFile($path, $langcode);

        // Save the last pull by langcode.
        $request_time = $this->getRequest()->server->get('REQUEST_TIME');
        $this->utility->setLastPull($langcode, $request_time);

        $this->messenger()->addMessage($this->t('Successfuly download all translations from locale <b>:langcode</b> of Loco.', [':langcode' => $langcode]));
        $this->messenger()->addMessage($this->t('Additions: <b>:additions</b>', [':additions' => $report['additions']]));
        $this->messenger()->addMessage($this->t('Updates: <b>:updates</b>', [':updates' => $report['updates']]));
        $this->messenger()->addMessage($this->t('Deletes: <b>:deletes</b>', [':deletes' => $report['deletes']]));
        $this->messenger()->addMessage($this->t('Skips: <b>:skips</b>', [':skips' => $report['skips']]));

      }
      catch (\Exception $e) {
        $this->messenger()->addError($e->getMessage());
      }
    }
  }

}
