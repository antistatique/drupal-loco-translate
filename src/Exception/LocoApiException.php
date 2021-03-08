<?php

namespace Drupal\loco_translate\Exception;

use GuzzleHttp\Command\Result;
use Loco\Http\Result\RawResult;

/**
 * Represents an exception that occurred in the communication with Loco API.
 */
class LocoApiException extends \Exception {

  /**
   * The upload to Loco API has failed.
   *
   * @param \GuzzleHttp\Command\Result $result
   *   A Loco response message.
   *
   * @return LocoApiException
   *   Exception used when the given path or file does not exist.
   */
  public static function uploadFailed(Result $result) {
    $message = ['Loco upload failed.'];

    // Build the message based on available keys.
    if (isset($result['status'])) {
      $message[] = sprintf('Returned status %s.', $result['status']);
    }
    if (isset($result['error'])) {
      $message[] = sprintf('With message: %s.', $result['error']);
    }

    return new static(implode(' ', $message));
  }

  /**
   * Download from Loco API has failed.
   *
   * @param Loco\Http\Result\RawResult $raw_result
   *   A Loco response message.
   *
   * @return LocoApiException
   *   Exception used when the given path or file does not exist.
   */
  public static function downloadFailed(RawResult $raw_result) {
    $message = ['Loco upload failed.'];
    return new static(implode(' ', $message));
  }

  /**
   * Something went wrong when communicating with Loco.
   *
   * @param \Exception $exception
   *   A Loco exception.
   *
   * @return LocoApiException
   *   Exception used when we don't have a specific use-case for this error.
   */
  public static function unhandled(\Exception $exception) {
    $message = ['Loco upload failed.'];

    // Build the message based on available keys.
    $message[] = sprintf('Returned status %s.', $exception->getCode());
    $message[] = sprintf('Returned message: %s.', $exception->getMessage());

    return new static(implode(' ', $message));
  }

}
