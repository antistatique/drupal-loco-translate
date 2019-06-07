<?php

namespace Drupal\loco_translate;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\loco_translate\Exception\LocoTranslateException;

/**
 * Contains utility methods for the Loco Translate module.
 */
class Utility {
  /**
   * The language Manager.
   *
   * @var \Drupal\language\ConfigurableLanguageManager
   */
  protected $languageManager;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(LanguageManagerInterface $language_manager) {
    $this->languageManager = $language_manager;
  }

  /**
   * From a given langcode, retrieve the langname.
   *
   * @param string $langcode
   *   The langcode.
   *
   * @return string|null
   *   The common langname of this langcode. Otherwise NULL
   */
  public function getLangName($langcode) {
    $languages = $this->languageManager->getLanguages();
    return isset($languages[$langcode]) ? $languages[$langcode]->getName() : NULL;
  }

  /**
   * Check if the given langcode is installed & enabled.
   *
   * @param string $langcode
   *   The langcode to test.
   *
   * @return bool
   *   TRUE if the given langcode exists, FALSE otherwise.
   */
  public function isLangcodeEnabled($langcode) {
    $languages = $this->languageManager->getLanguages();
    return isset($languages[$langcode]);
  }

}
