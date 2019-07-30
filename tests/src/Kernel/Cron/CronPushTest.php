<?php

namespace Drupal\Tests\loco_translate\Kernel\Cron;

use Drupal\KernelTests\KernelTestBase;
use Drupal\loco_translate\Loco\Push as LocoPush;
use Prophecy\Argument;

/**
 * Cover the Push hook_cron exposed by Loco Translate.
 *
 * @see loco_translate_cron_push
 *
 * @group loco_translate
 * @group loco_translate_kernel
 * @group loco_translate_cron
 */
class CronPushTest extends KernelTestBase {

  /**
   * The directory of tests .po files.
   *
   * The path should not end with a leading directory separator.
   *
   * @var array
   */
  protected $translationsPath;

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
  public static $modules = [
    'locale',
    'language',
    'file',
    'loco_translate_test',
    'loco_translate',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Get the editable configurations.
    $this->cronConfig = \Drupal::configFactory()->getEditable('loco_translate.settings');

    /** @var string $translationsPath */
    $this->translationsPath = drupal_get_path('module', 'loco_translate_test') . DIRECTORY_SEPARATOR . 'assets';

    // Save the default push configurations.
    $this->cronConfig->set('automation.push.interval', 3600)->save();
    $this->cronConfig->set('automation.push.template', $this->translationsPath . '/en.po')->save();
    $this->cronConfig->set('automation.push.langcodes', ['en' => 'en'])->save();

    /** @var \Drupal\Core\State\StateInterface $state */
    $this->state = $this->container->get('state');

    // Pretend that cron(s) has never been run.
    $this->state->set('loco_translate.api.push_last', ['en' => NULL]);
  }

  /**
   * Ensure the configured interval is respected.
   *
   * @dataProvider goodIntervalProvider
   */
  public function testCronPushGoodInterval($langcode, $last_run, $interval) {
    // Mock the loco push manager to prevent any API call.
    $loco_push = $this->prophesize(LocoPush::class);
    $loco_push->fromFileToLoco(Argument::type('string'), $langcode)->willReturn(TRUE)->shouldBeCalled();
    $this->container->set('loco_translate.loco_api.push', $loco_push->reveal());

    $this->cronConfig->set('automation.push.interval', $interval)->save();
    $this->state->set('loco_translate.api.push_last', [$langcode => $last_run]);

    // The interval has expired, so the cron should.
    loco_translate_cron();

    $last_push = $this->state->get('loco_translate.api.push_last');
    $this->assertArrayHasKey($langcode, $last_push);
    $this->assertInternalType('int', $last_push[$langcode]);
    $this->assertGreaterThan($last_run, $last_push[$langcode]);
  }

  /**
   * Provider of testCronPushGoodInterval.
   *
   * @return array
   *   Return an array of arrays.
   */
  public function goodIntervalProvider() {
    return [
      [
        'en', time() - 4000, 3600,
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
  public function testCronPushBadInterval($langcode, $last_run, $interval) {
    // Mock the loco push manager to prevent any API call.
    $loco_push = $this->prophesize(LocoPush::class);
    $loco_push->fromFileToLoco(Argument::type('string'), $langcode)->willReturn(TRUE)->shouldNotBeCalled();
    $this->container->set('loco_translate.loco_api.push', $loco_push->reveal());

    $this->cronConfig->set('automation.push.interval', $interval)->save();
    $this->state->set('loco_translate.api.push_last', [$langcode => $last_run]);

    // The interval does not expired, so the cron should not run.
    loco_translate_cron();

    $last_push = $this->state->get('loco_translate.api.push_last');
    $this->assertSame([$langcode => $last_run], $last_push);
  }

  /**
   * Provider of testCronPushBadInterval.
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
   * Ensure disabling the push automation will skip the push cron.
   */
  public function testCronPushDisabled() {
    // Disable the push automation.
    $this->cronConfig->set('automation.push.interval', 0)->save();

    // Ensure default values are empty.
    $last_push = $this->state->get('loco_translate.api.push_last');
    $this->assertArrayHasKey('en', $last_push);
    $this->assertNull($last_push['en']);

    // Should not run loco_translate_cron() because push is disabled.
    loco_translate_cron();

    $last_push = $this->state->get('loco_translate.api.push_last');
    $this->assertNull($last_push['en']);
  }

  /**
   * Ensure configuring an invalide template file will skip the push cron.
   */
  public function testCronPushInvalidTemplateFile() {
    // Setup an invalide Template file.
    $this->cronConfig->set('automation.push.template', 'en.po')->save();

    // Should not run loco_translate_cron() because template is not a file.
    loco_translate_cron();

    $last_push = $this->state->get('loco_translate.api.push_last');
    $this->assertNull($last_push['en']);
  }

}
