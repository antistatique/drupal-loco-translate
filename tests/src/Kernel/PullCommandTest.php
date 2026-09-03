<?php

namespace Drupal\Tests\loco_translate\Kernel;

use Drupal\loco_translate\Commands\PullCommand;
use Drupal\loco_translate\Loco\Pull as LocoPull;
use Drupal\loco_translate\TranslationsImport;
use GuzzleHttp\Psr7\Response;
use Loco\Http\Result\RawResult;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Tests the pull Drush command.
 *
 * @group loco_translate
 *
 * @internal
 */
#[Group('loco_translate')]
#[CoversClass(\Drupal\loco_translate\Commands\PullCommand::class)]
#[CoversMethod(\Drupal\loco_translate\Commands\PullCommand::class, 'pull')]
#[RunTestsInSeparateProcesses]
final class PullCommandTest extends TranslationsTestsBase {

  use ProphecyTrait;

  /**
   * The Loco translations pull manager.
   *
   * @var \Drupal\loco_translate\Loco\Pull
   */
  private $locoPull;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'locale',
    'language',
    'file',
    'user',
    'loco_translate_test',
    'loco_translate',
  ];

  /**
   * The Doctor Synchronizer commands.
   *
   * @var \Drupal\loco_translate\Commands\PullCommand
   */
  protected $pullCommand;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');

    // Setup the file system so we will be able to store downloaded file(s).
    $this->installSchema('file', ['file_usage']);
    $this->installEntitySchema('file');

    // Let the translations:// stream wrapper point to a virtual file system to
    // make it independent from the test environment.
    $translations_stream = vfsStream::setup('translations');
    \Drupal::configFactory()->getEditable('locale.settings')
      ->set('translation.path', $translations_stream->url())
      ->save();

    // Mock the loco pull manager to prevent any API call.
    $this->locoPull = $this->prophesize(LocoPull::class);

    // Use a real importer instance so realpath() works with VFS paths.
    $translationImport = new class(
      $this->container->get('loco_translate.utility'),
      $this->container->get('module_handler')
    ) extends TranslationsImport {

      /**
       * {@inheritdoc}
       */
      public function realpath($source) {
        return $source;
      }

    };

    $this->pullCommand = new PullCommand(
      $this->locoPull->reveal(),
      $translationImport,
      $this->container->get('file_system'),
      $this->container->get('file.repository')
    );
  }

  /**
   * Ensures pulling imports the downloaded translation file.
   */
  public function testPull(): void {
    // Mock the Loco Response export response.
    $data = file_get_contents(\Drupal::service('extension.list.module')->getPath('loco_translate_test') . '/responses/export-200.po');
    $response = new Response(200, [], $data);
    $response = RawResult::fromResponse($response);
    $this->locoPull->fromLocoToDrupal('en', NULL, NULL)
      ->willReturn($response)
      ->shouldBeCalled();

    // Ensure the translation does not already exists.
    $source = $this->localStorage->findString(['source' => 'Abbreviated-month-name-Jul']);
    $this->assertNull($source);

    // Ensure there is no Drupal File Entity.
    $file_storage = $this->container->get('entity_type.manager')
      ->getStorage('file');
    $this->assertEmpty($file_storage->loadMultiple());

    // Run the pull operation on translation english.
    $this->pullCommand->pull('en');

    // Ensure on file has been created as Drupal File Entity as Temporary.
    $files = $file_storage->loadMultiple();
    $this->assertCount(1, $files);
    $file = reset($files);
    $this->assertFalse($file->isPermanent());

    // Ensure propre translation has been imported.
    $source = $this->localStorage->findString(['source' => 'Abbreviated-month-name-Jul']);
    $string = $this->localStorage->findTranslation([
      'language' => 'en',
      'lid' => $source->lid,
    ]);
    $this->assertEquals($string->customized, LOCALE_NOT_CUSTOMIZED);
    $this->assertEquals($string->translation, 'Jul');
  }

}
