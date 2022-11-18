<?php

namespace Drupal\Tests\loco_translate\Kernel\Cron;

use Drupal\KernelTests\KernelTestBase;
use Drupal\loco_translate\Loco\Pull as LocoPull;
use Drupal\loco_translate\TranslationsImport;
use Prophecy\Argument;
use Loco\Http\Result\RawResult;
use GuzzleHttp\Psr7\Response;
use org\bovigo\vfs\vfsStream;

/**
 * Cover the Pull hook_cron exposed by Loco Translate.
 *
 * @see loco_translate_cron_pull
 *
 * @group loco_translate
 * @group loco_translate_kernel
 * @group loco_translate_cron
 */
class CronPullTest extends KernelTestBase {

  /**
   * An editable config object for access to 'loco_translate.settings'.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $cronConfig;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

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
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->installSchema('file', ['file_usage']);
    $this->installSchema('locale', [
      'locales_location',
      'locales_source',
      'locales_target',
    ]);
    $this->installEntitySchema('user');
    $this->installEntitySchema('file');

    // Let the translations:// stream wrapper point to a virtual file system to
    // make it independent from the test environment.
    $translations_stream = vfsStream::setup('translations');
    \Drupal::configFactory()->getEditable('locale.settings')
      ->set('translation.path', $translations_stream->url())
      ->save();

    // Get the editable configurations.
    $this->cronConfig = \Drupal::configFactory()->getEditable('loco_translate.settings');

    // Save the default pull configurations.
    $this->cronConfig->set('automation.pull.interval', 3600)->save();
    $this->cronConfig->set('automation.pull.langcodes', ['en' => 'en'])->save();

    /** @var \Drupal\Core\State\StateInterface $state */
    $this->state = $this->container->get('state');

    // Pretend that cron(s) has never been run.
    $this->state->set('loco_translate.api.pull_last', ['en' => NULL]);
  }

  /**
   * Ensure the configured interval is respected.
   *
   * @dataProvider goodIntervalProvider
   */
  public function testCronPullGoodInterval($langcode, $last_run, $interval) {
    $data = file_get_contents(\Drupal::service('extension.list.module')->getPath('loco_translate_test') . '/responses/export-200.po');
    $response = new Response(200, [], $data);
    $response = RawResult::fromResponse($response);

    // Mock the loco pull manager to prevent any API call.
    $loco_pull = $this->prophesize(LocoPull::class);
    $loco_pull->fromLocoToDrupal($langcode, Argument::any())->willReturn($response)->shouldBeCalled();
    $this->container->set('loco_translate.loco_api.pull', $loco_pull->reveal());

    // Mock the Translations importer.
    $translation_import = $this->prophesize(TranslationsImport::class);
    $translation_import->fromFile(Argument::type('string'), $langcode)->willReturn([
      "additions" => 0,
      "updates" => 0,
      "deletes" => 0,
      "skips" => 0,
      "strings" => [],
      "seek" => 0,
    ])->shouldBeCalled();
    $this->container->set('loco_translate.translations.import', $translation_import->reveal());

    $this->cronConfig->set('automation.pull.interval', $interval)->save();
    $this->state->set('loco_translate.api.pull_last', [$langcode => $last_run]);

    // The interval has expired, so the cron should.
    loco_translate_cron();

    $last_pull = $this->state->get('loco_translate.api.pull_last');
    $this->assertArrayHasKey($langcode, $last_pull);
    $this->assertIsInt($last_pull[$langcode]);
    $this->assertGreaterThan($last_run, $last_pull[$langcode]);
  }

  /**
   * Provider of testCronPullGoodInterval.
   *
   * @return array
   *   Return an array of arrays.
   */
  public function goodIntervalProvider() {
    return [
      [
        'en', time() - 100000, 3600,
      ],
      [
        'en', 0, 3600,
      ],
      [
        'en', NULL, 3600,
      ],
    ];
  }

  /**
   * Ensure the configured interval is respected.
   *
   * @dataProvider badIntervalProvider
   */
  public function testCronPullBadInterval($langcode, $last_run, $interval) {
    // Mock the loco pull manager to prevent any API call.
    $loco_pull = $this->prophesize(LocoPull::class);
    $loco_pull->fromLocoToDrupal($langcode, Argument::any())->shouldNotBeCalled();
    $this->container->set('loco_translate.loco_api.pull', $loco_pull->reveal());

    $this->cronConfig->set('automation.pull.interval', $interval)->save();
    $this->state->set('loco_translate.api.pull_last', [$langcode => $last_run]);

    // The interval does not expired, so the cron should not run.
    loco_translate_cron();

    $last_pull = $this->state->get('loco_translate.api.pull_last');
    $this->assertSame([$langcode => $last_run], $last_pull);
  }

  /**
   * Provider of testCronPullBadInterval.
   *
   * @return array
   *   Return an array of arrays.
   */
  public function badIntervalProvider() {
    return [
      [
        'en', time() - 4000, 50000,
      ],
    ];
  }

  /**
   * Ensure disabling the pull automation will skip the pull cron.
   */
  public function testCronPullDisabled() {
    // Disable the pull automation.
    $this->cronConfig->set('automation.pull.interval', 0)->save();

    // Ensure default values are empty.
    $last_pull = $this->state->get('loco_translate.api.pull_last');
    $this->assertArrayHasKey('en', $last_pull);
    $this->assertNull($last_pull['en']);

    // Should not run loco_translate_cron() because pull is disabled.
    loco_translate_cron();

    $last_pull = $this->state->get('loco_translate.api.pull_last');
    $this->assertNull($last_pull['en']);
  }

  /**
   * Removing the locale.settings.translation.path should throw in Watchdog.
   */
  public function testCronPullWithoutTranslationDirDestinationFailSilently() {
    \Drupal::configFactory()->getEditable('locale.settings')
      ->set('translation.path', '')
      ->save();

    $data = file_get_contents(\Drupal::service('extension.list.module')->getPath('loco_translate_test') . '/responses/export-200.po');
    $response = new Response(200, [], $data);
    $response = RawResult::fromResponse($response);

    // Mock the loco pull manager to prevent any API call.
    $loco_pull = $this->prophesize(LocoPull::class);
    $loco_pull->fromLocoToDrupal('en', Argument::any())->willReturn($response)->shouldBeCalled();
    $this->container->set('loco_translate.loco_api.pull', $loco_pull->reveal());

    // Mock the Translations importer.
    $translation_import = $this->prophesize(TranslationsImport::class);
    $translation_import->fromFile(Argument::type('string'), Argument::type('string'))->shouldNotBeCalled();
    $this->container->set('loco_translate.translations.import', $translation_import->reveal());

    $this->cronConfig->set('automation.pull.interval', time() - 100000)->save();
    $this->state->set('loco_translate.api.pull_last', ['en' => 3600]);

    // The interval has expired, so the cron should.
    loco_translate_cron();

    $last_pull = $this->state->get('loco_translate.api.pull_last');
    $this->assertArrayHasKey('en', $last_pull);
    $this->assertIsInt($last_pull['en']);
  }

  /**
   * Removing the locale.settings.translation.path should throw an error.
   */
  public function testCronPullWithoutTranslationDirDestination() {
    \Drupal::configFactory()->getEditable('locale.settings')
      ->set('translation.path', '')
      ->save();

    $data = file_get_contents(\Drupal::service('extension.list.module')->getPath('loco_translate_test') . '/responses/export-200.po');
    $response = new Response(200, [], $data);
    $response = RawResult::fromResponse($response);

    // Mock the loco pull manager to prevent any API call.
    $loco_pull = $this->prophesize(LocoPull::class);
    $loco_pull->fromLocoToDrupal('en', Argument::any())->willReturn($response)->shouldBeCalled();
    $this->container->set('loco_translate.loco_api.pull', $loco_pull->reveal());

    // Mock the Translations importer.
    $translation_import = $this->prophesize(TranslationsImport::class);
    $this->container->set('loco_translate.translations.import', $translation_import->reveal());

    // When the download destination directory is not reachable, an exception
    // should be thrown.
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('Download error. Could not move downloaded file from Loco to destination translations://.');

    loco_translate_cron_pull('en', [
      'interval' => 1602738908,
      'langcodes' => ['en' => 'en'],
    ]);
  }

}
