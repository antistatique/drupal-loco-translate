<?php

namespace Drupal\loco_translate\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
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
      $container->get('language_manager'),
      $container->get('file_system'),
      $container->get('loco_translate.loco_api.pull'),
      $container->get('loco_translate.translations.import')
    );
  }

  /**
   * Constructs a form for language pull.
   *
   * @param \Drupal\language\ConfigurableLanguageManagerInterface $language_manager
   *   The configurable language manager.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\loco_translate\Loco\Pull $loco_pull
   *   The Loco translations pull manager.
   * @param \Drupal\loco_translate\TranslationsImport $translatons_import
   *   The Translation importer.
   */
  public function __construct(ConfigurableLanguageManagerInterface $language_manager, FileSystemInterface $file_system, LocoPull $loco_pull, TranslationsImport $translatons_import) {
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
      '#value' => $this->t('Pull'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $langcode = $form_state->getValue('langcode');

    try {
      $this->locoPull->setApiClientFromConfig();
      $response = $this->locoPull->fromLocoToDrupal($langcode);
    }
    catch (\Exception $e) {
      $form_state->setError($form, $e->getMessage());
      return;
    }

    $file = $this->fileSystem->saveData($response->__toString(), 'translations://');

    if (!$file) {
      $form_state->setError($form, 'error');
      return;
    }

    $form_state->setValue('file', $this->fileSystem->realPath($file));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $langcode = $form_state->getValue('langcode');
    $path = $form_state->getValue('file');

    try {
      $report = $this->translationsImport->fromFile($path, $langcode);
    }
    catch (\Exception $e) {
      $this->messenger()->addError($e->getMessage());
      return;
    }

    $this->messenger()->addMessage($this->t('Successfuly download all translations from local <b>:langcode</b> of Loco.', [':langcode' => $langcode]));
    $this->messenger()->addMessage($this->t('Additions: <b>:additions</b>', [':additions' => $report['additions']]));
    $this->messenger()->addMessage($this->t('Updates: <b>:updates</b>', [':updates' => $report['updates']]));
    $this->messenger()->addMessage($this->t('Deletes: <b>:deletes</b>', [':deletes' => $report['deletes']]));
    $this->messenger()->addMessage($this->t('Skips: <b>:skips</b>', [':skips' => $report['skips']]));
  }

}
