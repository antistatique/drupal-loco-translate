<?php

namespace Drupal\loco_translate\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Implements hook_theme().
 */
#[Hook('theme')]
final class ThemeHook {

  /**
   * Register the Loco Translate theme implementations.
   *
   * @param array $existing
   *   An array of existing implementations that may be used for override.
   * @param string $type
   *   Whether a theme, module, etc. is being processed.
   * @param string $theme
   *   The actual name of theme, module, etc. that is being processed.
   * @param string $path
   *   The directory path of the theme or module.
   *
   * @return array
   *   An associative array of information about theme implementations.
   */
  public function __invoke(array $existing, string $type, string $theme, string $path): array {
    return [
      'loco_translate_overview_page' => [
        'variables' => ['variables' => []],
        'template'  => 'loco-translate--overview--page',
      ],
    ];
  }

}
