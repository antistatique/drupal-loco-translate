<?php

namespace Drupal\loco_translate\Commands;

use Drupal\loco_translate\Loco\Pull;
use Drupal\loco_translate\TranslationsImport;
use Drush\Commands\DrushCommands;
use Drupal\Core\File\FileSystemInterface;

/**
 * Drush Loco Pull Commands.
 */
class PullCommand extends DrushCommands {

  /**
   * Loco Pull Api Wrapper.
   *
   * @var \Drupal\loco_translate\Loco\Pull
   */
  private $locoPull;

  /**
   * The Translation importer.
   *
   * @var \Drupal\loco_translate\TranslationsImport
   */
  protected $translationsImport;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * PullCommand constructor.
   *
   * @param \Drupal\loco_translate\Loco\Pull $locoPull
   *   The Loco Push Api Wrapper.
   * @param \Drupal\loco_translate\TranslationsImport $translations_import
   *   The translation import service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(Pull $locoPull, TranslationsImport $translations_import, FileSystemInterface $file_system) {
    $this->locoPull = $locoPull;
    $this->translationsImport = $translations_import;
    $this->fileSystem = $file_system;
  }

  /**
   * Pull a local .po file into Loco SAAS.
   *
   * @param array $options
   *   (Optional) An array of options.
   *
   * @throws \Drupal\loco_translate\Exception\LocoApiException
   * @throws \Drupal\loco_translate\Exception\LocoTranslateException
   *
   * @command loco_translate:pull
   * @option language
   *   Ex: 'en' or 'fr'. Define in which language the .po file is written
   *   and in which locale you want to import this file into Loco.
   *   [default: detect the language from the .po filename]
   * @option status
   *   Ex: 'translated' or 'fuzzy'. The status of translations to be pulled.
   *   [default: all translations are pulled]
   * @aliases loco:pull
   * @usage drush loco_translate:pull --language="fr" --status="fuzzy" ./config/translations/fr.po
   *   Pull only fuzzy translations from the Loco SAAS in the french locale.
   */
  public function pull(array $options = ['language' => NULL, 'status' => NULL]) {
    $language = $options['language'];

    if (!$language) {
      $this->output()->writeln('Language parameter is required.');
      return;
    }

    $status = $options['status'];

    $this->output()->writeln(sprintf('Importing %s "%s" translations from Loco.', $status ?? 'all', $language));

    $response = $this->locoPull->fromLocoToDrupal($language, $status);

    /** @var \Drupal\file\FileInterface $file */
    $file = file_save_data($response->__toString(), 'translations://');
    $path = $this->fileSystem->realPath($file->getFileUri());

    $report = $this->translationsImport->fromFile($path, $language);

    $this->output()->writeln(sprintf('Successfully imported all "%s" translations from Loco.', $language));
    $this->output()->writeln(sprintf('Additions: %s', $report['additions']));
    $this->output()->writeln(sprintf('Updates: %s', $report['updates']));
    $this->output()->writeln(sprintf('Deletes: %s', $report['deletes']));
    $this->output()->writeln(sprintf('Skips: %s', $report['skips']));
  }

}
