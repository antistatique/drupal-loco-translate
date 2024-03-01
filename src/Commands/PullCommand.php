<?php

namespace Drupal\loco_translate\Commands;

use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\loco_translate\Loco\Pull;
use Drupal\loco_translate\TranslationsImport;
use Drush\Commands\DrushCommands;

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
   * The file repository service.
   *
   * @var \Drupal\file\FileRepositoryInterface
   */
  protected $fileRepository;

  /**
   * PullCommand constructor.
   *
   * @param \Drupal\loco_translate\Loco\Pull $locoPull
   *   The Loco Push Api Wrapper.
   * @param \Drupal\loco_translate\TranslationsImport $translations_import
   *   The translation import service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\file\FileRepositoryInterface $file_repository
   *   The file repository service.
   */
  public function __construct(Pull $locoPull, TranslationsImport $translations_import, FileSystemInterface $file_system, FileRepositoryInterface $file_repository) {
    $this->locoPull = $locoPull;
    $this->translationsImport = $translations_import;
    $this->fileSystem = $file_system;
    $this->fileRepository = $file_repository;
  }

  /**
   * Pull keys & translations from your Loco SaSS into Drupal.
   *
   * @param string $language
   *   Define which language to pull from Loco and in which locale it will be
   *   imported into Drupal. Eg: 'en' or 'fr'.
   * @param array $options
   *   (Optional) An array of options.
   *
   * @throws \Drupal\loco_translate\Exception\LocoApiException
   * @throws \Drupal\loco_translate\Exception\LocoTranslateException
   *
   * @command loco_translate:pull
   *
   * @option status
   *   Ex: 'translated' or 'fuzzy'. The status of translations to be pulled.
   *   [default: all translations are pulled]
   * @option index
   *   Override default lookup key for the file format.
   *   Available: "id", "text" or a custom alias. [default: "text"].
   *
   * @aliases loco:pull
   *
   * @usage drush loco_translate:pull fr --status="fuzzy"
   *   Pull only fuzzy translations from the Loco SAAS in the french locale.
   */
  public function pull($language, array $options = [
    'status' => NULL,
    'index' => NULL,
  ]) {
    $status = $options['status'];
    $index = $options['index'];

    $this->output()->writeln(sprintf('Importing %s "%s" translations from Loco.', $status ?? 'all', $language));

    $response = $this->locoPull->fromLocoToDrupal($language, $status, $index);

    $destination_directory = 'translations://';
    // Prepare the translations directory if not already existing.
    $destination_writable = $this->fileSystem->prepareDirectory($destination_directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

    if (!$destination_writable) {
      throw new \RuntimeException(sprintf('Download error. Could not move downloaded file from Loco to destination %s.', $destination_directory));
    }

    /** @var \Drupal\file\FileInterface $file */
    $file = $this->fileRepository->writeData($response->__toString(), $destination_directory);

    // Be sure the file is temporary, so will be garbage collected.
    $file->setTemporary();
    $file->save();

    $path = $this->fileSystem->realPath($file->getFileUri());

    $report = $this->translationsImport->fromFile($path, $language);

    $this->output()->writeln(sprintf('Successfully imported all "%s" translations from Loco.', $language));
    $this->output()->writeln(sprintf('Additions: %s', $report['additions']));
    $this->output()->writeln(sprintf('Updates: %s', $report['updates']));
    $this->output()->writeln(sprintf('Deletes: %s', $report['deletes']));
    $this->output()->writeln(sprintf('Skips: %s', $report['skips']));
  }

}
