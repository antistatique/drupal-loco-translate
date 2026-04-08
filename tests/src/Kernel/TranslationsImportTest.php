<?php

namespace Drupal\Tests\loco_translate\Kernel;

use Drupal\loco_translate\Exception\LocoTranslateException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests importing translations from PO files.
 *
 * @group loco_translate
 */
#[Group('loco_translate')]
#[CoversClass(\Drupal\loco_translate\TranslationsImport::class)]
#[CoversMethod(\Drupal\loco_translate\TranslationsImport::class, 'fromFile')]
#[RunTestsInSeparateProcesses]
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
  protected static $modules = [
    'locale',
    'language',
    'file',
    'loco_translate',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    /** @var \Drupal\loco_translate\TranslationsImport $translationsImport */
    $this->translationsImport = $this->container->get('loco_translate.translations.import');

    /** @var string $translationsPath */
    $this->translationsPath = \Drupal::service('extension.list.module')->getPath('loco_translate_test') . DIRECTORY_SEPARATOR . 'assets';
  }

  /**
   * Ensures importing fails for undefined languages.
   */
  public function testInvalidLangcode() {
    $this->expectException(LocoTranslateException::class);
    $this->expectExceptionMessage("The langcode ru is not defined. Please create & enable it before trying to use it.");

    $source = $this->translationsPath . '/fr.po';
    $this->translationsImport->fromFile($source, 'ru');
  }

  /**
   * Ensures importing fails when the source file is missing.
   */
  public function testSourceNotFound() {
    $this->expectException(LocoTranslateException::class);
    $this->expectExceptionMessageMatches('/No such file or directory "modules\/(custom|contrib)\/loco_translate\/tests\/modules\/loco_translate_test\/assets\/ru.po"./');
    $source = $this->translationsPath . '/ru.po';
    $this->translationsImport->fromFile($source, 'fr');
  }

  /**
   * Ensures translations are imported and reported correctly.
   */
  public function testFromFile() {
    $this->setUpTranslations();

    $source = $this->translationsPath . '/fr.po';
    $report = $this->translationsImport->fromFile($source, 'fr');

    // Ensure the report is formatted as expected.
    $this->assertEquals([
      "additions" => 5,
      "updates" => 4,
      "deletes" => 0,
      "skips" => 0,
      "strings" => [
        0 => "1",
        1 => "2",
        2 => "9",
        3 => "3",
        4 => "4",
        5 => "10",
        6 => "11",
        7 => "12",
        8 => "13",
      ],
      "seek" => 901,
    ], $report);

    // Load all source strings.
    $strings = $this->localStorage->getStrings([]);
    $this->assertEquals(count($strings), 13, 'Found 13 source strings in the database.');

    // Existing "non-customized" source has been overrided.
    $source = $this->localStorage->findString(['source' => 'last year']);
    $string = $this->localStorage->findTranslation([
      'language' => 'fr',
      'lid' => $source->lid,
    ]);
    $this->assertEquals($string->customized, LOCALE_NOT_CUSTOMIZED);
    $this->assertEquals($string->translation, 'l’année dernière');

    // Assert unexisting source (new string) w/ context is imported as
    // "non-customized".
    $source = $this->localStorage->findString([
      'source' => 'Jul',
      'context' => 'Abbreviated month name',
    ]);
    $string = $this->localStorage->findTranslation([
      'language' => 'fr',
      'lid' => $source->lid,
    ]);
    $this->assertEquals($string->customized, LOCALE_NOT_CUSTOMIZED);
    $this->assertEquals($string->translation, 'Juil.', 'Successfully loaded translation by source and context.');

    // Existing "non-customized" trans w/o context has not been overrided.
    $source = $this->localStorage->findString(['source' => 'Jul']);
    $string = $this->localStorage->findTranslation([
      'language' => 'fr',
      'lid' => $source->lid,
    ]);
    $this->assertEquals($string->customized, LOCALE_NOT_CUSTOMIZED);
    $this->assertNotEquals($string->translation, 'Juil.');

    // Existing "customized" trans w/o context has not been overrided.
    $source = $this->localStorage->findString(['source' => 'Jan']);
    $string = $this->localStorage->findTranslation([
      'language' => 'fr',
      'lid' => $source->lid,
    ]);
    $this->assertEquals($string->customized, LOCALE_CUSTOMIZED);
    $this->assertNotEquals($string->translation, 'Janv.');

    // Assert new strings with vars are imported as "non-customized".
    $source = $this->localStorage->findString(['source' => 'I love @color car']);
    $string = $this->localStorage->findTranslation([
      'language' => 'fr',
      'lid' => $source->lid,
    ]);
    $this->assertEquals($string->customized, LOCALE_NOT_CUSTOMIZED);
    $this->assertEquals($string->translation, "J'adore les voitures @color", 'Successfully loaded translation with var(s).');

    // Assert new plural forms are imported as "non-customized".
    $source = $this->localStorage->findString(['source' => '@count doctor@count doctors']);
    $string = $this->localStorage->findTranslation([
      'language' => 'fr',
      'lid' => $source->lid,
    ]);
    $this->assertEquals($string->customized, LOCALE_NOT_CUSTOMIZED);
    $this->assertNotNull($string, 'Successfully loaded plural translation.');

    // Existing "non-customized" translations w/ context has been overrided.
    $source = $this->localStorage->findString([
      'source' => 'March',
      'context' => 'Long month name',
    ]);
    $string = $this->localStorage->findTranslation([
      'language' => 'fr',
      'lid' => $source->lid,
    ]);
    $this->assertEquals($string->customized, LOCALE_NOT_CUSTOMIZED);
    $this->assertEquals($string->translation, 'Mars');

    // Existing "customized" translations w/ context has been overrided and
    // revert-back as "non-customized".
    $source = $this->localStorage->findString([
      'source' => 'April',
      'context' => 'Long month name',
    ]);
    $string = $this->localStorage->findTranslation([
      'language' => 'fr',
      'lid' => $source->lid,
    ]);
    $this->assertEquals($string->customized, LOCALE_NOT_CUSTOMIZED);
    $this->assertEquals($string->translation, 'April');
  }

}
