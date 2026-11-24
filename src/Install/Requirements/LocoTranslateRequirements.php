<?php

namespace Drupal\loco_translate\Install\Requirements;

use Drupal\Core\Extension\InstallRequirementsInterface;
use Drupal\Core\Extension\Requirement\RequirementSeverity;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Asserts the Loco SDK is installed (Drupal 11.2+).
 *
 * Used at install time. On Drupal < 11.2, the legacy
 * loco_translate_requirements() is used instead.
 */
final class LocoTranslateRequirements implements InstallRequirementsInterface {

  /**
   * {@inheritdoc}
   */
  public static function getRequirements(): array {
    if (class_exists('Loco\Http\ApiClient')) {
      return [];
    }

    return [
      'loco_translate_loco_sdk' => [
        'title' => new TranslatableMarkup('Loco libraries'),
        'value' => new TranslatableMarkup('Missing libraries'),
        // @phpstan-ignore class.notFound
        'severity' => RequirementSeverity::Error,
        'description' => new TranslatableMarkup('Loco Translate requires the <a href=":sdk-url" target="_blank">external Loco SDK</a>. The recommended way of solving this dependency is using <a href=":composer-url" target="_blank">Composer</a> running the following from the command line: <br /><code>composer require loco/loco:^2.0</code>', [
          ':sdk-url' => 'https://github.com/loco/loco-php-sdk',
          ':composer-url' => 'https://getcomposer.org',
        ]),
      ],
    ];
  }

}
