<?php

namespace Drupal\loco_translate\Exception;

/**
 * Represents an exception that occurred in the communication with Loco API.
 */
class LocoApiException extends \Exception {

  /**
   * The upload to Loco API has failed.
   *
   * @param array $response
   *   A Loco response message.
   *
   * @return LocoApiException
   *   Exception used when the given path or file does not exist.
   */
  public static function uploadFailed(array $response) {
    $message[] = 'Loco upload failed.';

    // Build the message based on available keys.
    if (isset($response['status'])) {
      $message[] = sprintf('Returned status %s.', $response['status']);
    }
    if (isset($response['error'])) {
      $message[] = sprintf('With message: %s.', $response['error']);
    }

    return new static(implode(' ', $message));
  }

}
