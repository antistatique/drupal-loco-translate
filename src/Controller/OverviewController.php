<?php

namespace Drupal\loco_translate\Controller;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\system\SystemManager;
use Loco\Http\ApiClient;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    $variables['project'] = $this->cache->get('loco_translate.cache.api.project') ? $this->cache->get('loco_translate.cache.api.project')->data : NULL;

    // Get the Loco Locales & Progress.
    $variables['locales'] = $this->cache->get('loco_translate.cache.api.locales') ? $this->cache->get('loco_translate.cache.api.locales')->data : NULL;

    // Get the Loco Assets.
    $variables['assets'] = $this->cache->get('loco_translate.cache.api.assets') ? $this->cache->get('loco_translate.cache.api.assets')->data : NULL;

    // Get the Loco Status.
    $variables['versions'] = $this->cache->get('loco_translate.cache.versions') ? $this->cache->get('loco_translate.cache.versions')->data : NULL;

    // Get Last Pull time by langcode.
    if ($this->state->get('loco_translate.api.pull_last')) {
      foreach ($this->state->get('loco_translate.api.pull_last') as $langcode => $pull_last) {
        $variables['pull_last'][$langcode] = $this->t('<strong>@langcode</strong> was imported @time ago', [
          '@langcode' => strtoupper($langcode),
          '@time' => $this->dateFormatter->formatTimeDiffSince($pull_last),
        ]);
      }
    }

    // Get Last Push time by langcode.
    if ($this->state->get('loco_translate.api.push_last')) {
      foreach ($this->state->get('loco_translate.api.push_last') as $langcode => $push_last) {
        $variables['push_last'][$langcode] = $this->t('<strong>@langcode</strong> was uploaded to Loco @time ago.', [
          '@langcode' => strtoupper($langcode),
          '@time' => $this->dateFormatter->formatTimeDiffSince($push_last),
        ]);
      }
    }

    // Asserts loco/loco library is installed.
    $variables['requirements']['loco_translate_loco_sdk'] = [
      'title' => $this->t('Loco libraries'),
      'value' => $this->t('Installed'),
    ];

    if (!class_exists('Loco\Http\ApiClient')) {
      $variables['requirements']['loco_translate_loco_sdk']['value'] = $this->t('Missing libraries');
      $variables['requirements']['loco_translate_loco_sdk']['severity'] = SystemManager::REQUIREMENT_ERROR;
      $variables['requirements']['loco_translate_loco_sdk']['description'] = $this->t('Loco Translate requires the <a href=":sdk-url" target="_blank">external Loco SDK</a>. The recommended way of solving this dependency is using <a href=":composer-url" target="_blank">Composer</a> running the following from the command line: <br /><code>composer require loco/loco:^2.0</code>', [
        ':sdk-url' => 'https://github.com/loco/loco-php-sdk',
        ':composer-url' => 'https://getcomposer.org',
      ]);
    }

    $config = $this->configFactory->get('loco_translate.settings');
    $variables['requirements']['loco_translate_readonly_key'] = [
      'title' => $this->t('Loco Export API key'),
      'value' => $this->t('Configured'),
    ];
    if (empty($config->get('api.readonly_key'))) {
      $variables['requirements']['loco_translate_readonly_key']['value'] = $this->t('Missing');
      $variables['requirements']['loco_translate_readonly_key']['severity'] = SystemManager::REQUIREMENT_ERROR;
      $variables['requirements']['loco_translate_readonly_key']['description'] = $this->t('Loco Translate requires your Export API key. Keep this key secret by adding it in your <code>settings.php</code> or fill the <a href=":settings-url">Settings form</a>. You may find more informations about API keys on <a href=":loco-url" target="_blank">Loco support</a> pages', [
        ':loco-url' => 'https://localise.biz/help/developers/api-keys',
        ':settings-url' => Url::fromRoute('loco_translate.settings', [], ['fragment' => 'edit-api'])->toString(),
      ]);
    }

    $variables['requirements']['loco_translate_fullaccess_key'] = [
      'title' => $this->t('Loco Full Access API key'),
      'value' => $this->t('Configured'),
    ];
    if (empty($config->get('api.fullaccess_key'))) {
      $variables['requirements']['loco_translate_fullaccess_key']['value'] = $this->t('Missing');
      $variables['requirements']['loco_translate_fullaccess_key']['severity'] = SystemManager::REQUIREMENT_ERROR;
      $variables['requirements']['loco_translate_fullaccess_key']['description'] = $this->t('Loco Translate requires your Full Access API key. Keep this key secret by adding it in your <code>settings.php</code> or fill the <a href="">Settings form</a>. You may find more informations about API keys on <a href=":loco-url" target="_blank">Loco support</a> pages', [
        ':loco-url' => 'https://localise.biz/help/developers/api-keys',
        ':settings-url' => Url::fromRoute('loco_translate.settings', [], ['fragment' => 'edit-api'])->toString(),
      ]);
    }

    return [
      '#theme' => 'loco_translate_overview_page',
      '#variables' => $variables,
      '#attached' => [
        'library' => ['loco_translate/drupal.loco_translate.admin_css'],
      ],
    ];
  }

  /**
   * Refresh the Loco.
   */
  public function refresh() {
    $config = $this->configFactory->get('loco_translate.settings');
    $client = ApiClient::factory([
      'key' => $config->get('api.readonly_key'),
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
      /** @var \GuzzleHttp\Command\Result $result */
      $result = $client->ping();
      $this->cache->set('loco_translate.cache.versions', [
        'api' => $result->offsetGet('version'),
        'library' => ApiClient::API_VERSION,
      ], CacheBackendInterface::CACHE_PERMANENT);

      $this->messenger()->addMessage($this->t('Loco data refreshed.'));
    }
    catch (\Throwable $th) {
      $this->messenger()->addError($th->getMessage());
    }

    return $this->redirect('loco_translate.overview');
  }

}
