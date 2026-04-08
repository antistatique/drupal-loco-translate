<?php

namespace Drupal\Tests\loco_translate\Kernel;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Cover default behaviors of translations.
 *
 * @group loco_translate
 */
#[Group('loco_translate')]
#[RunTestsInSeparateProcesses]
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
  protected static $modules = [
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
    $this->assertNull($translations, 'Found 0 translations strings in the database.');
  }

}
