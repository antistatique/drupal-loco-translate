<?php

namespace Drupal\Tests\loco_translate\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * @coversDefaultClass \Drupal\loco_translate\Form\SettingsForm
 *
 * @group loco_translate
 * @group loco_translate_browser
 * @group loco_translate_functional
 */
class SettingsFormTest extends BrowserTestBase {

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
   * We use the minimal profile because we want to test local action links.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'starterkit_theme';

  /**
   * Ensure the routing permissions works.
   */
  public function testAccessPermission() {
    // Create a user without permission for tests.
    $account = $this->drupalCreateUser();
    $this->drupalLogin($account);

    $this->drupalGet('admin/config/regional/loco_translate/settings');
    $this->assertSession()->statusCodeEquals(403);

    // Create another user with propre permission for tests.
    $account = $this->drupalCreateUser(['administer loco translate']);
    $this->drupalLogin($account);

    $this->drupalGet('admin/config/regional/loco_translate/settings');
    $this->assertSession()->statusCodeEquals(200);
  }

}
