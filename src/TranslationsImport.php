<?php

namespace Drupal\loco_translate;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\locale\Gettext;
use Drupal\loco_translate\Exception\LocoTranslateException;

/**
 * Translations Importations.
 */
class TranslationsImport {

  /**
   * The Utility service of Loco Translate.
   *
   * @var \Drupal\loco_translate\Utility
   */
  protected $utility;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Class constructor.
   *
   * @param \Drupal\loco_translate\Utility $utility
   *   Utility methods for Loco Translate.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(Utility $utility, ModuleHandlerInterface $module_handler, FileSystemInterface $file_system) {
    $this->utility = $utility;
    $this->moduleHandler = $module_handler;
    $this->fileSystem = $file_system;
  }

  /**
   * Translation(s) importation from .po file in the database.
   *
   * @param string $source
   *   The .po file's path.
   * @param string $locale
   *   The Language code (Eg. 'fr' or 'en').
   *
   * @return array
   *   Report array as defined in @see \Drupal\locale\PoDatabaseWriter.
   */
  public function fromFile($source, $locale) {
    $path = $this->realpath($source);

    if (!file_exists($path) || !is_file($path)) {
      throw LocoTranslateException::notFound($source);
    }

    if (!is_readable($path)) {
      throw LocoTranslateException::isNotReadable($path);
    }

    // Check for existing & enabled langcode.
    if (!$this->utility->isLangcodeEnabled($locale)) {
      throw LocoTranslateException::invalidLangcode($locale);
    }

    // Load Drupal 8 Core locale global functions.
    $this->moduleHandler->loadInclude('locale', 'translation.inc');
    $this->moduleHandler->loadInclude('locale', 'bulk.inc');

    $options = array_merge(_locale_translation_default_update_options(), [
      'customized' => LOCALE_NOT_CUSTOMIZED,
      'overwrite_options' => [
        'not_customized' => TRUE,
        'customized' => TRUE,
      ],
    ]);

    // Create a valid file class for Gettext::fileToDatabase.
    $file            = new \stdClass();
    $file->filename  = $this->fileSystem->basename($path);
    $file->uri       = $path;
    $file->langcode  = $locale;
    $file->timestamp = filemtime($path);

    return Gettext::fileToDatabase($file, $options);
  }

  /**
   * Wrapper around PHP Built-in realpath in order to mock it for VFS tests.
   */
  public function realpath($source) {
    return realpath($source);
  }

}
