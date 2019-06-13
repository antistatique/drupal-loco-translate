<?php

namespace Drupal\loco_translate\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Loco\Http\ApiClient;

/**
 * Loco dashboard overview.
 */
class OverviewController extends ControllerBase {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Cache backend service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Construct a OverviewController object.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The configuration factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend.
   */
  public function __construct(StateInterface $state, ConfigFactoryInterface $config_factory, DateFormatterInterface $date_formatter, CacheBackendInterface $cache) {
    $this->state = $state;
    $this->configFactory = $config_factory;
    $this->dateFormatter = $date_formatter;
    $this->cache = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
      $container->get('config.factory'),
      $container->get('date.formatter'),
      $container->get('cache.data')
    );
  }

  /**
   * Shows the dashboard screen.
   *
   * @return array
   *   The render array for the dashboard screen.
   */
  public function dashboard() {
    $variables = [];

    // Get the Loco Porject.
    $variables['project'] = $this->cache->get('loco_translate.cache.api.project')->data;

    // Get the Loco Locales & Progress.
    $variables['locales'] = $this->cache->get('loco_translate.cache.api.locales')->data;

    // Get the Loco Assets.
    $variables['assets'] = $this->cache->get('loco_translate.cache.api.assets')->data;

    // Get the Loco Status.
    $variables['versions'] = $this->cache->get('loco_translate.cache.versions')->data;

    // Get Last Pull time by langcode.
    foreach ($this->state->get('loco_translate.api.pull_last') as $langcode => $pull_last) {
      $variables['pull_last'][$langcode] = $this->t('<strong>%langcode</strong> - last run: %time ago.', [
        '%langcode' => strtoupper($langcode),
        '%time' => $this->dateFormatter->formatTimeDiffSince($pull_last),
      ]);
    }

    // Get Last Push time by langcode.
    foreach ($this->state->get('loco_translate.api.push_last') as $langcode => $push_last) {
      $variables['push_last'][$langcode] = $this->t('<strong>%langcode</strong> - last run: %time ago.', [
        '%langcode' => strtoupper($langcode),
        '%time' => $this->dateFormatter->formatTimeDiffSince($push_last),
      ]);
    }

    return [
      '#theme' => 'loco_translate_overview_page',
      '#variables' => $variables,
    ];
  }

  /**
   * Refresh the Loco.
   */
  public function refresh() {
    $config = $this->configFactory->get('loco_translate.settings');
    $client = ApiClient::factory([
      'key' => $config->get('api.export_key'),
    ]);

    try {
      // Get Project.
      $auth = $client->authVerify();
      $this->cache->set('loco_translate.cache.api.project', $auth->offsetGet('project'), CacheBackendInterface::CACHE_PERMANENT);

      // Get the Loco Locales & Progress.
      $locales = $client->getLocales();
      $this->cache->set('loco_translate.cache.api.locales', $locales, CacheBackendInterface::CACHE_PERMANENT);

      // Get the Loco Assets.
      $assets = $client->getAssets();
      $this->cache->set('loco_translate.cache.api.assets', $assets, CacheBackendInterface::CACHE_PERMANENT);

      // Get the Loco Status.
      /* @var \GuzzleHttp\Command\Result $result */
      $result = $client->ping();
      $this->cache->set('loco_translate.cache.versions', [
        'api' => $result->offsetGet('version'),
        'library' => ApiClient::API_VERSION,
      ], CacheBackendInterface::CACHE_PERMANENT);
    }
    catch (\Throwable $th) {
      $this->messenger()->addError($th->getMessage());
    }

    $this->messenger()->addMessage($this->t('Loco data refreshed.'));
    return $this->redirect('loco_translate.overview');
  }

}
