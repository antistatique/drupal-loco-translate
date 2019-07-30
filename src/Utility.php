<?php

namespace Drupal\loco_translate;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\State\StateInterface;

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
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(LanguageManagerInterface $language_manager, StateInterface $state) {
    $this->languageManager = $language_manager;
    $this->state = $state;
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

  /**
   * Set the last pull time by given langcode.
   *
   * @param string $langcode
   *   The locale to use.
   * @param string $time
   *   The time to save.
   */
  public function setLastPull($langcode, $time) {
    $pull_last = (array) $this->state->get('loco_translate.api.pull_last');
    $pull_last[$langcode] = (int) $time;
    $this->state->set('loco_translate.api.pull_last', $pull_last);
  }

  /**
   * Set the last push time by given langcode.
   *
   * @param string $langcode
   *   The locale to use.
   * @param string $time
   *   The time to save.
   */
  public function setLastPush($langcode, $time) {
    $push_last = (array) $this->state->get('loco_translate.api.push_last');
    $push_last[$langcode] = (int) $time;
    $this->state->set('loco_translate.api.push_last', $push_last);
  }

  /**
   * Set the last pull time by given langcode.
   *
   * @param string $langcode
   *   The locale to use.
   *
   * @return int
   *   The last pull timestamp.
   */
  public function getLastPull($langcode) {
    $pull_last = (array) $this->state->get('loco_translate.api.pull_last');
    return (int) isset($pull_last[$langcode]) ? $pull_last[$langcode] : 0;
  }

  /**
   * Set the last push time by given langcode.
   *
   * @param string $langcode
   *   The locale to use.
   *
   * @return int
   *   The last push timestamp.
   */
  public function getLastPush($langcode) {
    $push_last = $this->state->get('loco_translate.api.push_last');
    return (int) isset($push_last[$langcode]) ? $push_last[$langcode] : 0;
  }

}
