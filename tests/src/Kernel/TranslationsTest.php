<?php

namespace Drupal\Tests\loco_translate\Kernel;

/**
 * Cover default behaviors of translations.
 *
 * @group loco_translate
 * @group loco_translate_kernel
 * @group loco_translate_kernel_translations
 */
class TranslationsTest extends TranslationsTestsBase {

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
   * Cover setup doesn't install translations by default.
   *
   * Default translations could result in FALSE positive into following tests.
   */
  public function testNoTranslationsOnSetup() {
    // Assert there is not translations in the database.
    $strings = $this->localStorage->getStrings([]);
    $this->assertEquals(count($strings), 0, 'Found 0 source strings in the database.');
    $translations = $this->localStorage->findTranslation([]);
    $this->assertEquals(count($translations), 0, 'Found 0 translations strings in the database.');
  }

}
