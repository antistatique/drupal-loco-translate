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
   *   Exception used when the langcode is not enabled/available on Drupal.
   */
  public static function invalidLangcode($langcode) {
    return new static(sprintf('The langcode %s is not defined. Please create & enabled it before trying to use it.', $langcode));
  }

  /**
   * The path or file does not exist.
   *
   * @param string $path
   *   The file or path.
   *
   * @return LocoTranslateException
   *   Exception used when the given path or file does not exist.
   */
  public static function notFound($path) {
    return new static(sprintf('No such file or directory "%s".', $path));
  }

  /**
   * The file is not readalbe.
   *
   * @param string $file
   *   The file to read.
   *
   * @return LocoTranslateException
   *   Exception used when the file is not readable.
   */
  public static function isNotReadable($file) {
    return new static(sprintf('The file "%s" is not readable.', $file));
  }

  /**
   * The path is not writable.
   *
   * @param string $path
   *   The path.
   *
   * @return LocoTranslateException
   *   Exception used when the path is not writable.
   */
  public static function isNotWritable($path) {
    return new static(sprintf('The path "%s" is not writable.', $path));
  }

}
