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
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->locoConfig = $config_factory->get('loco_translate.settings');
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
      'key' => $this->locoConfig->get('api.fullaccess_key'),
    ]);
  }

  /**
   * Upload the given .po file into Loco.
   *
   * @param string $source
   *   The .po file to upload on Loco.
   * @param string $locale
   *   The local code.
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

    return $result;
  }

}
