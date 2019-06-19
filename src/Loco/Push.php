<?php

namespace Drupal\loco_translate\Loco;

use Loco\Http\ApiClient;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\loco_translate\Exception\LocoTranslateException;
use Drupal\loco_translate\Exception\LocoApiException;

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
   *
   * @see https://localise.biz/api/#!/import/import
   */
  public function fromFileToLoco($source, $locale) {
    $file = realpath($source);

    if (!file_exists($file) || !is_file($file)) {
      throw LocoTranslateException::notFound($file);
    }

    if (!is_readable($file)) {
      throw LocoTranslateException::isNotReadable($file);
    }

    // TODO: Check Basic PO Formats.
    $data = file_get_contents($file);

    try {
      /* @var \GuzzleHttp\Command\Result */
      $result = $this->client->import([
        'data' => $data,
        'locale' => $locale,
        'ext' => 'po',
        'ignore-existing' => TRUE,
        'tag-absent' => 'absent',
        'index' => 'id',
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
