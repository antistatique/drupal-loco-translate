<?php

namespace Drupal\Tests\loco_translate\Kernel;

use org\bovigo\vfs\vfsStream;
use Drupal\loco_translate\TranslationsImport;
use Drupal\loco_translate\Commands\PullCommand;
use Loco\Http\Result\RawResult;
use GuzzleHttp\Psr7\Response;
use Drupal\loco_translate\Loco\Pull as LocoPull;

/**
 * @coversDefaultClass \Drupal\loco_translate\Commands\PullCommand
 *
 * @group loco_translate
 * @group loco_translate_kernel
 *
 * @internal
 */
final class PullCommandTest extends TranslationsTestsBase {

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

    $this->fileStorage = $this->container->get('entity_type.manager')->getStorage('file');

    // Mock the loco pull manager to prevent any API call.
    $this->locoPull = $this->prophesize(LocoPull::class);

    // Partially mock the translation importer in order to prevent realpath
    // on VFS.
    $translationImport = $this->getMockBuilder(TranslationsImport::class)
      ->setMethods(['realpath'])
      ->setConstructorArgs([
        $this->container->get('loco_translate.utility'),
        $this->container->get('module_handler'),
        $this->container->get('file_system'),
      ])
      ->getMock();

    $translationImport->expects(self::any())
      ->method('realpath')
      ->will($this->returnArgument(0));

    $this->pullCommand = new PullCommand(
      $this->locoPull->reveal(),
      $translationImport,
      $this->container->get('file_system')
    );
  }

  /**
   * @covers ::pull
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
    $this->assertEmpty($this->fileStorage->loadMultiple());

    // Run the pull operation on translation english.
    $this->pullCommand->pull('en');

    // Ensure on file has been created as Drupal File Entity as Temporary.
    $files = $this->fileStorage->loadMultiple();
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
