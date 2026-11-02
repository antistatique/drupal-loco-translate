<?php

namespace Drupal\loco_translate\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\loco_translate\Utility;
use Psr\Log\LoggerInterface;

/**
 * Implements hook_cron().
 *
 * Automation of Loco Translate module.
 */
#[Hook('cron')]
final class CronHook {

  /**
   * Constructs a new CronHook object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   * @param \Drupal\loco_translate\Utility $utility
   *   The Utility service of Loco Translate.
   * @param \Psr\Log\LoggerInterface $logger
   *   The Loco Translate logger channel.
   */
  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
    private readonly Utility $utility,
    private readonly LoggerInterface $logger,
  ) {
  }

  /**
   * Pull and push the configured langcodes from/to Loco when they are due.
   */
  public function __invoke(): void {
    $now = time();
    $config = $this->configFactory->get('loco_translate.settings');

    // Get configured langcode to pull from loco.
    $langcodes = $config->get('automation.pull.langcodes');
    if ($langcodes) {
      foreach ($langcodes as $langcode => $status) {
        // Skip unchecked langcode.
        if (!$status) {
          continue;
        }

        // Skip when automation pull is disabled.
        if ($config->get('automation.pull.interval') <= 0) {
          continue;
        }

        // Check last run of this langcode.
        $next_pull = $this->utility->getLastPull($langcode) + $config->get('automation.pull.interval');
        if ($now <= $next_pull) {
          continue;
        }

        try {
          loco_translate_cron_pull($langcode, $config->get('automation.pull'));
          $this->utility->setLastPull($langcode, $now);
        }
        catch (\Exception $e) {
          $this->logger->error($e);
        }
      }
    }

    // Get configured langcode to push from loco.
    $langcodes = $config->get('automation.push.langcodes');
    if ($langcodes && $config->get('automation.push.template')) {
      foreach ($langcodes as $langcode => $status) {
        // Skip unchecked langcode.
        if (!$status) {
          continue;
        }

        // Skip when automation push is disabled.
        if ($config->get('automation.push.interval') <= 0) {
          continue;
        }

        // Check last run of this langcode.
        $next_push = $this->utility->getLastPush($langcode) + $config->get('automation.push.interval');
        if ($now <= $next_push) {
          continue;
        }

        try {
          loco_translate_cron_push($langcode, $config->get('automation.push'));
          $this->utility->setLastPush($langcode, $now);
        }
        catch (\Exception $e) {
          $this->logger->error($e);
        }
      }
    }
  }

}
