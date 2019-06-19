<?php

/**
 * @file
 * Push cron function for Loco Translate module.
 */

/**
 * Push assets & translations to Loco.
 *
 * @param string $langcode
 *   The langcode to push from.
 * @param array $settings
 *   The configurations needed to push on Loco.
 */
function loco_translate_cron_push($langcode, array $settings) {
  $loco_push = \Drupal::service('loco_translate.loco_api.push');

  $loco_push->setApiClientFromConfig();
  $path = $settings['template'];

  $loco_push->fromFileToLoco($path, $langcode);
  \Drupal::service('logger.channel.loco_translate')->info('Items sucessfully pushed to Loco:');
}
