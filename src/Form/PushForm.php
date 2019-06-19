<?php

namespace Drupal\loco_translate\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\loco_translate\Utility;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\loco_translate\Loco\Push as LocoPush;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form constructor for the translation push screen.
 *
 * @internal
 */
class PushForm extends FormBase {

  /**
   * The Utility service of Loco Translate.
   *
   * @var \Drupal\loco_translate\Utility
   */
  protected $utility;

  /**
   * Uploaded file entity.
   *
   * @var \Drupal\file\Entity\File
   */
  protected $file;

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
   * The Loco translate push manager.
   *
   * @var \Drupal\loco_translate\Loco\Push
   */
  protected $locoPush;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('loco_translate.utility'),
      $container->get('language_manager'),
      $container->get('file_system'),
      $container->get('loco_translate.loco_api.push')
    );
  }

  /**
   * Constructs a form for language push.
   *
   * @param \Drupal\loco_translate\Utility $utility
   *   The Utility service of Loco Translate.
   * @param \Drupal\language\ConfigurableLanguageManagerInterface $language_manager
   *   The configurable language manager.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\loco_translate\Loco\Push $loco_push
   *   The Loco translate push manager.
   */
  public function __construct(Utility $utility, ConfigurableLanguageManagerInterface $language_manager, FileSystemInterface $file_system, LocoPush $loco_push) {
    $this->utility = $utility;
    $this->languageManager = $language_manager;
    $this->fileSystem = $file_system;
    $this->locoPush = $loco_push;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'loco_translate_push_form';
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

    $validators = [
      'file_validate_extensions' => ['po'],
      'file_validate_size' => [file_upload_max_size()],
    ];

    $form['file'] = [
      '#type' => 'file',
      '#title' => $this->t('Translation file'),
      '#description' => [
        '#theme' => 'file_upload_help',
        '#description' => $this->t('A Gettext Portable Object file.'),
        '#upload_validators' => $validators,
      ],
      '#size' => 50,
      '#upload_validators' => $validators,
      '#upload_location' => 'translations://',
      '#attributes' => ['class' => ['file-import-input']],
    ];
    $form['langcode'] = [
      '#type' => 'select',
      '#title' => $this->t('Language'),
      '#options' => $language_options,
      '#attributes' => ['class' => ['langcode-input']],
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Push'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->file = _file_save_upload_from_form($form['file'], $form_state, 0);

    // Ensure we have the file uploaded.
    if (!$this->file) {
      $form_state->setErrorByName('file', $this->t('File to push not found.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $langcode = $form_state->getValue('langcode');
    $path = $this->fileSystem->realpath($this->file->getFileUri());

    try {
      $this->locoPush->setApiClientFromConfig();
      $response = $this->locoPush->fromFileToLoco($path, $langcode);

      // Save the last push by langcode.
      $request_time = $this->getRequest()->server->get('REQUEST_TIME');
      $this->utility->setLastPush($langcode, $request_time);
    }
    catch (\Exception $e) {
      $this->messenger()->addError($e->getMessage());
      return;
    }
    $this->messenger()->addMessage($this->t('Successfuly pushed asset(s) & translation(s) into Loco.'));
    $this->messenger()->addMessage($response['message']);

    $form_state->setRedirect('loco_translate.overview');
  }

}
