<?php

namespace Drupal\loco_translate\Exception;

/**
 * Represents an exception that occurred in Loco Translate.
 */
class LocoTranslateException extends \Exception {

  /**
   * Invalid langcode exception.
   *
   * @param string $langcode
   *   The ISO langcode which is invalid.
   *
   * @return LocoTranslateException
   *   The invalid langcode exception.
   */
  public static function invalidLangcode($langcode) {
    return new static('The langcode ' . $langcode . ' is not defined. Please create & enabled it before trying to use it.');
  }
}
