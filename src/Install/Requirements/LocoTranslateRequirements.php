<?php

namespace Drupal\loco_translate\Install\Requirements;

use Drupal\Core\Extension\InstallRequirementsInterface;
use Drupal\loco_translate\Hook\RequirementsHook;

/**
 * Install time requirements for Loco Translate (Drupal 11.2+).
 *
 * On Drupal < 11.2, the legacy loco_translate_requirements() is used instead.
 */
final class LocoTranslateRequirements implements InstallRequirementsInterface {

  /**
   * {@inheritdoc}
   */
  public static function getRequirements(): array {
    return RequirementsHook::locoSdk();
  }

}
