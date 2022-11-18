<?php

namespace Drupal\loco_translate\TwigExtension;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Loco Twig Extensions.
 */
class LocoExport extends AbstractExtension {
  use ContainerAwareTrait;

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
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->container->get('config.factory');

    $config = $config_factory->get('loco_translate.settings');
    $readonly_key = $config->get('api.readonly_key');

    $params = [
      'index' => 'id',
      'no-folding' => TRUE,
      'key' => $readonly_key,
    ];
    return 'https://localise.biz:443/api/export/locale/' . $locale . '.po?' . http_build_query($params);
  }

}
