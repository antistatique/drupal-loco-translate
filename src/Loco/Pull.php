<?php

namespace Drupal\loco_translate\Loco;

use Loco\Http\ApiClient;
use Drupal\loco_translate\Utility;
use Drupal\loco_translate\Exception\LocoTranslateException;
use Drupal\loco_translate\Exception\LocoApiException;

/**
 * Pull asset(s) & translation(s) from Loco to Drupal.
 */
class Pull {

  /**
   * The Loco SDK HTTP client.
   *
   * @var \Loco\Http\ApiClient
   */
  private $client;

  /**
   * The Utility service of Loco Translate.
   *
   * @var \Drupal\loco_translate\Utility
   */
  protected $utility;

  /**
   * Constructor.
   *
   * @param \Loco\Http\ApiClient $api_client
   *   Loco Api Client.
   * @param \Drupal\loco_translate\Utility $utility
   *   Utility methods for Loco Translate.
   */
  public function __construct(ApiClient $api_client, Utility $utility) {
    $this->client = $api_client;
    $this->utility = $utility;
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
   * Get back all assets & translation string from Loco to Drupal.
   *
   * @param string $locale
   *   The locale code.
   * @param string $status
   *   Export translations with a specific status or flag.
   *
   * @return \Loco\Http\Result\RawResult
   *   The result of the query.
   *
   * @throws \Drupal\loco_translate\Exception\LocoApiException
   * @throws \Drupal\loco_translate\Exception\LocoTranslateException
   *
   * @see https://localise.biz/api/#!/import/import
   */
  public function fromLocoToDrupal($locale, $status = NULL) {
    // Check for existing & enabled langcode.
    if (!$this->utility->isLangcodeEnabled($locale)) {
      throw LocoTranslateException::invalidLangcode($locale);
    }

    try {
      /* @var \Loco\Http\Result\RawResult */
      $result = $this->client->exportLocale([
        'ext' => 'po',
        'index' => 'id',
        'locale' => $locale,
        'no-folding' => TRUE,
        'status' => $status,
      ]);
    }
    catch (\Exception $e) {
      throw LocoApiException::unhandled($e);
    }

    return $result;
  }

}
