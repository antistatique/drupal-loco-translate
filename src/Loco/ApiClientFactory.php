<?php

namespace Drupal\loco_translate\Loco;

use Drupal\Core\Config\ConfigFactoryInterface;
use Loco\Http\ApiClient;

/**
 * Factory to create ApiClient from Drupal Config.
 */
class ApiClientFactory {

  /**
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *  The Drupal Config Factory.
   *
   * @return \Loco\Http\ApiClient
   *  The Loco ApiClient.
   */
  public static function withFullAccess(ConfigFactoryInterface $config_factory) {
    $config = $config_factory->get('loco_translate.settings');
    $key = $config->get('api.fullaccess_key');

    return ApiClient::factory([
      'key' => $key,
    ]);
  }

  /**
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *  The Drupal Config Factory.
   *
   * @return \Loco\Http\ApiClient
   *  The Loco ApiClient.
   */
  public static function withReadOnlyAccess(ConfigFactoryInterface $config_factory) {
    $config = $config_factory->get('loco_translate.settings');
    $key = $config->get('api.export_key');

    return ApiClient::factory([
      'key' => $key,
    ]);
  }

}
