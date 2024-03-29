<?php

namespace Drupal\loco_translate\Form;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Drupal\loco_translate\Loco\Pull as LocoPull;
use Drupal\loco_translate\TranslationsImport;
use Drupal\loco_translate\Utility;
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
   * The file repository service.
   *
   * @var \Drupal\file\FileRepositoryInterface
   */
  protected $fileRepository;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('loco_translate.utility'),
      $container->get('language_manager'),
      $container->get('file_system'),
      $container->get('file.repository'),
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
   * @param \Drupal\file\FileRepositoryInterface|null $file_repository
   *   The file repository service.
   * @param \Drupal\loco_translate\Loco\Pull $loco_pull
   *   The Loco translations pull manager.
   * @param \Drupal\loco_translate\TranslationsImport $translatons_import
   *   The Translation importer.
   */
  public function __construct(Utility $utility, ConfigurableLanguageManagerInterface $language_manager, FileSystemInterface $file_system, FileRepositoryInterface $file_repository, LocoPull $loco_pull, TranslationsImport $translatons_import) {
    $this->utility = $utility;
    $this->languageManager = $language_manager;
    $this->fileSystem = $file_system;
    $this->fileRepository = $file_repository;
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

    $form['help'] = [
      '#type' => 'markup',
      '#markup' => $this->t('This form allows you to import a single locale from Loco, or your whole project at once.'),
    ];

    $form['langcodes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Language'),
      '#options' => $language_options,
    ];

    $form['status'] = [
      '#type' => 'select',
      '#title' => $this->t('Status', [], ['context' => 'Loco Translate']),
      '#options' => [
        '_none' => $this->t('All'),
        'translated' => $this->t('Only translated'),
        'fuzzy' => $this->t('Only fuzzy'),
      ],
      '#default_value' => 'translated',
      '#description' => $this->t('Import translations with a specific status. <br/>Bear in mind that this option is primarily intended for importing single-language. <br/>The status of asset translations is likely to differ between locales, so the result may not make sense.'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
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
        $response = $this->locoPull->fromLocoToDrupal($langcode, $status);

        $destination_directory = 'translations://';
        $destination_writable = $this->fileSystem->prepareDirectory($destination_directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

        if (!$destination_writable) {
          throw new \RuntimeException(sprintf('Download error. Could not move downloaded file from Loco to destination %s.', $destination_directory));
        }

        /** @var \Drupal\file\FileInterface $file */
        $file = $this->fileRepository->writeData($response->__toString(), $destination_directory);
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

        $this->messenger()->addMessage($this->t('Successfully imported all <b>:langcode</b> translations from Loco.', [':langcode' => $langcode]));
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
