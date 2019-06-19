<?php

namespace Drupal\Tests\loco_translate\Unit;

/**
 * @coversDefaultClass \Drupal\loco_translate\Utility
 *
 * @group loco_translate
 * @group loco_translate_unit
 * @group loco_translate_unit_utility
 */
class UtilityTest extends UtilityTestBase {

  /**
   * @covers \Drupal\loco_translate\Utility::isLangcodeEnabled
   *
   * @dataProvider getTestIsLangcodeEnabled
   */
  public function testIsLangcodeEnabled($langcode, $expected) {
    $result = $this->utility->isLangcodeEnabled($langcode);
    $this->assertEquals($result, $expected);
  }

  /**
   * Provider of testIsLangcodeEnabled.
   *
   * @return array
   *   Return an array of arrays.
   */
  public function getTestIsLangcodeEnabled() {
    return [
      ['fr', TRUE],
      ['fr-ch', FALSE],
      ['de', FALSE],
      ['de-ch', FALSE],
      ['en', TRUE],
    ];
  }

  /**
   * @covers \Drupal\loco_translate\Utility::setLastPull
   */
  public function testLastPush() {
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  /**
   * @covers \Drupal\loco_translate\Utility::setLastPull
   */
  public function testLastPull() {
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

}
