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
 *
 *   $settings['template']
 *     Path of the local .po file you want to push into Loco.
 */
function loco_translate_cron_push($langcode, array $settings) {
  $loco_push = \Drupal::service('loco_translate.loco_api.push');

  $path = $settings['template'];
  if (!is_file($path) || !is_readable($path)) {
    \Drupal::service('logger.channel.loco_translate')->warning('Template file @file is not reacheable.', [
      "@file" => $path,
    ]);
    return;
  }

  $loco_push->fromFileToLoco($path, $langcode);
  \Drupal::service('logger.channel.loco_translate')->info('Items sucessfully pushed to Loco:');
}
