<?php

namespace Drupal\Tests\loco_translate\Kernel;

use Drupal\loco_translate\Exception\LocoTranslateException;

/**
 * @coversDefaultClass \Drupal\loco_translate\TranslationsImport
 *
 * @group loco_translate
 * @group loco_translate_kernel
 * @group loco_translate_kernel_translations_import
 */
class TranslationsImportTest extends TranslationsTestsBase {

  /**
   * The directory of tests .po files.
   *
   * @var array
   */
  protected $translationsPath;

  /**
   * The Translation importer.
   *
   * @var \Drupal\loco_translate\TranslationsImport
   */
  protected $translationsImport;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'locale',
    'language',
    'file',
    'loco_translate',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    /** @var \Drupal\loco_translate\TranslationsImport $translationsImport */
    $this->translationsImport = $this->container->get('loco_translate.translations.import');

    /** @var string $translationsPath */
    $this->translationsPath = drupal_get_path('module', 'loco_translate_test') . DIRECTORY_SEPARATOR . 'assets';
  }

  /**
   * @covers \Drupal\loco_translate\TranslationsImport::importFromFile
   */
  public function testInvalidLangcode() {
    $this->setExpectedException(LocoTranslateException::class, "The langcode ru is not defined. Please create & enabled it before trying to use it.");

    $source = $this->translationsPath . '/fr.po';
    $this->translationsImport->importFromFile('ru', $source);
  }

}
