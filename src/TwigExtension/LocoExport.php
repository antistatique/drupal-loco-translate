<?php

namespace Drupal\loco_translate\TwigExtension;

use Drupal\Core\Config\ConfigFactoryInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Loco Twig Extensions.
 */
class LocoExport extends AbstractExtension {

  /**
   * The config factory.
   */
  protected ConfigFactoryInterface $configFactory;

  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('loco_translate_export', [
        $this, 'exportLink',
      ]),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'loco_translate.twig.loco_export';
  }

  /**
   * Generate an export link for Loco API.
   *
   * @param string $locale
   *   The locale code.
   *
   * @return string
   *   The export GET link for Loco API.
   */
  public function exportLink($locale) {
    $config = $this->configFactory->get('loco_translate.settings');
    $readonly_key = $config->get('api.readonly_key');

    $params = [
      'index' => 'id',
      'no-folding' => TRUE,
      'key' => $readonly_key,
    ];
    return 'https://localise.biz:443/api/export/locale/' . $locale . '.po?' . http_build_query($params);
  }

}
