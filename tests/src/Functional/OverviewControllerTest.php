<?php

namespace Drupal\Tests\loco_translate\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * @coversDefaultClass \Drupal\loco_translate\Controller\OverviewController
 *
 * @group loco_translate
 * @group loco_translate_browser
 */
class OverviewControllerTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
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
   * We use the minimal profile because we want to test local action links.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * Ensure the routing permissions works.
   */
  public function testAccessPermission() {
    // Create a user whitout permission for tests.
    $account = $this->drupalCreateUser();
    $this->drupalLogin($account);

    $this->drupalGet('admin/config/regional/loco_translate');
    $this->assertSession()->statusCodeEquals(403);

    // Create another user with propre permission for tests.
    $account = $this->drupalCreateUser(['access loco translate overview']);
    $this->drupalLogin($account);

    $this->drupalGet('admin/config/regional/loco_translate');
    $this->assertSession()->statusCodeEquals(200);
  }

}
