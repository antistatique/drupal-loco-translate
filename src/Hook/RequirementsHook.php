<?php

namespace Drupal\loco_translate\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\Requirement\RequirementSeverity;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

/**
 * Implements hook_runtime_requirements() & hook_update_requirements().
 *
 * Checks the Loco SDK is installed and the Loco API keys are configured.
 */
final class RequirementsHook {

  /**
   * Constructs a new RequirementsHook object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   */
  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
  ) {
  }

  /**
   * Implements hook_runtime_requirements().
   *
   * @return array
   *   The runtime requirements.
   */
  #[Hook('runtime_requirements')]
  public function runtime(): array {
    $requirements = self::locoSdk();
    $config = $this->configFactory->get('loco_translate.settings');

    if (empty($config->get('api.readonly_key'))) {
      $requirements['loco_translate_readonly_key'] = [
        'title' => new TranslatableMarkup('Loco Export API key'),
        'value' => new TranslatableMarkup('Missing'),
        'severity' => self::errorSeverity(),
        'description' => new TranslatableMarkup('Loco Translate requires your Export API key. Keep this key secret by adding it in your <code>settings.php</code> or fill the <a href=":settings-url">Settings form</a>. You may find more informations about API keys on <a href=":loco-url" target="_blank">Loco support</a> pages', [
          ':loco-url' => 'https://localise.biz/help/developers/api-keys',
          ':settings-url' => Url::fromRoute('loco_translate.settings', [], ['fragment' => 'edit-api'])->toString(),
        ]),
      ];
    }

    if (empty($config->get('api.fullaccess_key'))) {
      $requirements['loco_translate_fullaccess_key'] = [
        'title' => new TranslatableMarkup('Loco Full Access API key'),
        'value' => new TranslatableMarkup('Missing'),
        'severity' => self::errorSeverity(),
        'description' => new TranslatableMarkup('Loco Translate requires your Full Access API key. Keep this key secret by adding it in your <code>settings.php</code> or fill the <a href=":settings-url">Settings form</a>. You may find more informations about API keys on <a href=":loco-url" target="_blank">Loco support</a> pages', [
          ':loco-url' => 'https://localise.biz/help/developers/api-keys',
          ':settings-url' => Url::fromRoute('loco_translate.settings', [], ['fragment' => 'edit-api'])->toString(),
        ]),
      ];
    }

    return $requirements;
  }

  /**
   * Implements hook_update_requirements().
   *
   * @return array
   *   The update requirements.
   */
  #[Hook('update_requirements')]
  public function update(): array {
    return self::locoSdk();
  }

  /**
   * Asserts the Loco SDK (loco/loco) is installed.
   *
   * Static, so it can be used at install time, before the container knows
   * about this module services.
   *
   * @return array
   *   The Loco SDK requirement, empty when the SDK is installed.
   */
  public static function locoSdk(): array {
    if (class_exists('Loco\Http\ApiClient')) {
      return [];
    }

    return [
      'loco_translate_loco_sdk' => [
        'title' => new TranslatableMarkup('Loco libraries'),
        'value' => new TranslatableMarkup('Missing libraries'),
        'severity' => self::errorSeverity(),
        'description' => new TranslatableMarkup('Loco Translate requires the <a href=":sdk-url" target="_blank">external Loco SDK</a>. The recommended way of solving this dependency is using <a href=":composer-url" target="_blank">Composer</a> running the following from the command line: <br /><code>composer require loco/loco:^2.0</code>', [
          ':sdk-url' => 'https://github.com/loco/loco-php-sdk',
          ':composer-url' => 'https://getcomposer.org',
        ]),
      ],
    ];
  }

  /**
   * Gets the "error" requirement severity supported by the running core.
   *
   * @todo Return RequirementSeverity::Error directly once Drupal < 11.2 is no
   *   longer supported.
   *
   * @return \Drupal\Core\Extension\Requirement\RequirementSeverity|int
   *   RequirementSeverity::Error on Drupal 11.2+, otherwise the legacy integer
   *   value of the REQUIREMENT_ERROR constant (removed from Drupal 12).
   */
  private static function errorSeverity(): mixed {
    // @phpstan-ignore class.notFound
    return enum_exists(RequirementSeverity::class) ? RequirementSeverity::Error : 2;
  }

}
