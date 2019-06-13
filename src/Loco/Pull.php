<?php

namespace Drupal\loco_translate\Loco;

use Loco\Http\ApiClient;
use Drupal\Core\Config\ConfigFactoryInterface;
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
   * @var \Loco\Http\ApiClientLocoApiClient
   */
  private $client;

  /**
   * The loco translate settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $locoConfig;

  /**
   * The Utility service of Loco Translate.
   *
   * @var \Drupal\loco_translate\Utility
   */
  protected $utility;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\loco_translate\Utility $utility
   *   Utility methods for Loco Translate.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Utility $utility) {
    $this->locoConfig = $config_factory->get('loco_translate.settings');
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
   * Set the API Client automatically from Drupal settings.
   */
  public function setApiClientFromConfig() {
    $this->client = ApiClient::factory([
      'key' => $this->locoConfig->get('api.export_key'),
    ]);
  }

  /**
   * Get back all assets & translation string from Loco to Drupal.
   *
   * @param string $locale
   *   The locale code.
   * @param string $status
   *   Export translations with a specific status or flag.
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
        'status' => $status ?? NULL,
      ]);
    }
    catch (\Exception $e) {
      throw LocoApiException::unhandled($e);
    }

    return $result;
  }

}
