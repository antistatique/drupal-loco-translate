<?php

namespace Drupal\loco_translate\Loco;

use Drupal\loco_translate\Exception\LocoApiException;
use Drupal\loco_translate\Exception\LocoTranslateException;
use Loco\Http\ApiClient;

/**
 * Push/Upload asset(s) & translation(s) to Loco.
 */
class Push {

  /**
   * The Loco SDK HTTP client.
   *
   * @var \Loco\Http\ApiClient
   */
  private $client;

  /**
   * Constructor.
   *
   * @param \Loco\Http\ApiClient $api_client
   *   Loco Api Client.
   */
  public function __construct(ApiClient $api_client) {
    $this->client = $api_client;
  }

  /**
   * Set the API Client.
   *
   * @param \Loco\Http\ApiClient $api_client
   *   Loco Api Client.
   */
  public function setApiClient(ApiClient $api_client) {
    $this->client = $api_client;
  }

  /**
   * Upload the given .po file into Loco.
   *
   * @param string $source
   *   The .po file to upload on Loco.
   * @param string $locale
   *   The locale code.
   * @param string $index
   *   Specify whether translations in your file are indexed by IDs or text.
   *
   * @see https://localise.biz/api/#!/import/import
   */
  public function fromFileToLoco($source, $locale, $index = NULL) {
    $file = realpath($source);

    if (!file_exists($file) || !is_file($file)) {
      throw LocoTranslateException::notFound($file);
    }

    if (!is_readable($file)) {
      throw LocoTranslateException::isNotReadable($file);
    }

    // @todo Check Basic PO Formats.
    $data = file_get_contents($file);

    try {
      /** @var \GuzzleHttp\Command\Result */
      $result = $this->client->import([
        'data' => $data,
        'locale' => $locale,
        'ext' => 'po',
        'ignore-existing' => TRUE,
        'tag-absent' => 'absent',
        'index' => $index,
      ]);

      if ($result['status'] !== 200) {
        throw LocoApiException::uploadFailed($result);
      }
    }
    catch (\Exception $e) {
      throw LocoApiException::unhandled($e);
    }

    return $result;
  }

}
