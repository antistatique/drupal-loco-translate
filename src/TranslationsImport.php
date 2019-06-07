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
   * @param string $langcode
   *   Language code of the language being written to the database.
   * @param string $source
   *   The .po file's path.
   *
   * @return array
   *   Report array as defined in @see \Drupal\locale\PoDatabaseWriter.
   */
  public function importFromFile($langcode, $source) {
    // Check for existing & enabled langcode.
    if (!$this->utility->isLangcodeEnabled($langcode)) {
      throw LocoTranslateException::invalidLangcode($langcode);
    }

    // Load Drupal 8 Core local global functions.
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
    $file->filename  = $this->fileSystem->basename($source);
    $file->uri       = $source;
    $file->langcode  = $langcode;
    $file->timestamp = filemtime($source);

    return Gettext::fileToDatabase($file, $options);
  }

}
