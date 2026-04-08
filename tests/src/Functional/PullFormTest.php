<?php

namespace Drupal\Tests\loco_translate\Functional;

use Drupal\Tests\BrowserTestBase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the manual pull form.
 *
 * @group loco_translate
 * @group loco_translate_functional
 */
#[Group('loco_translate')]
#[Group('loco_translate_functional')]
#[CoversClass(\Drupal\loco_translate\Form\PullForm::class)]
#[RunTestsInSeparateProcesses]
class PullFormTest extends BrowserTestBase {

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

    $this->drupalGet('admin/config/regional/loco_translate/pull');
    $this->assertSession()->statusCodeEquals(403);

    // Create another user with propre permission for tests.
    $account = $this->drupalCreateUser(['pull using loco translate']);
    $this->drupalLogin($account);

    $this->drupalGet('admin/config/regional/loco_translate/pull');
    $this->assertSession()->statusCodeEquals(200);
  }

}
