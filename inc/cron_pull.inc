<?php

/**
 * @file
 * Pull cron function for Loco Translate module.
 */

function loco_translate_cron_pull($langcode, $settings) {
  $loco_pull = \Drupal::service('loco_translate.loco_api.pull');
  $translations_import = \Drupal::service('loco_translate.translations.import');
  $file_system = \Drupal::service('file_system');

  $loco_pull->setApiClientFromConfig();
  $status = $settings['status'] ?? NULL;
  $response = $loco_pull->fromLocoToDrupal($langcode, $status);

  /** @var \Drupal\file\FileInterface $file */
  $file = file_save_data($response->__toString(), 'translations://');
  $path = $file_system->realPath($file->getFileUri());
  $report = $translations_import->fromFile($path, $langcode);

  \Drupal::service('logger.channel.loco_translate')->info('Items sucessfully imported from Loco: @additions addition(s), @updates update(s), @deletes delete(s) & @skips skip(s).', [
    "@additions" => $report['additions'],
    "@updates" => $report['updates'],
    "@deletes" => $report['deletes'],
    "@skips" => $report['skips'],
  ]);
}