<?php

/**
 * @file
 * Pull cron function for Loco Translate module.
 */

use Drupal\Core\File\FileSystemInterface;

/**
 * Pull assets & translations from Loco.
 *
 * @param string $langcode
 *   The langcode to pull from.
 * @param array $settings
 *   The configurations needed to pull from Loco.
 *
 *   $settings['status']
 *     Ex: 'translated' or 'fuzzy'. The status of translations to be pulled.
 *     [default: all translations are pulled].
 */
function loco_translate_cron_pull($langcode, array $settings) {
  $loco_pull = \Drupal::service('loco_translate.loco_api.pull');
  $translations_import = \Drupal::service('loco_translate.translations.import');
  $file_system = \Drupal::service('file_system');

  $status = $settings['status'] ?? NULL;
  $response = $loco_pull->fromLocoToDrupal($langcode, $status);

  $destination_directory = 'translations://';
  $destination_writable = $file_system->prepareDirectory($destination_directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

  if (!$destination_writable) {
    throw new \RuntimeException(sprintf('Download error. Could not move downloaded file from Loco to destination %s.', $destination_directory));
  }

  /** @var \Drupal\file\FileInterface $file */
  $file = file_save_data($response->__toString(), $destination_directory);
  $path = $file_system->realPath($file->getFileUri());
  $report = $translations_import->fromFile($path, $langcode);

  \Drupal::service('logger.channel.loco_translate')->info('Items sucessfully imported from Loco: @additions addition(s), @updates update(s), @deletes delete(s) & @skips skip(s).', [
    "@additions" => $report['additions'],
    "@updates" => $report['updates'],
    "@deletes" => $report['deletes'],
    "@skips" => $report['skips'],
  ]);
}
