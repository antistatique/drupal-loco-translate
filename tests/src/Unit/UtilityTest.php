<?php

namespace Drupal\Tests\loco_translate\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the Loco Translate utility service.
 *
 * @group loco_translate
 */
#[Group('loco_translate')]
#[CoversClass(\Drupal\loco_translate\Utility::class)]
#[CoversMethod(\Drupal\loco_translate\Utility::class, 'isLangcodeEnabled')]
#[CoversMethod(\Drupal\loco_translate\Utility::class, 'setLastPush')]
#[CoversMethod(\Drupal\loco_translate\Utility::class, 'setLastPull')]
#[CoversMethod(\Drupal\loco_translate\Utility::class, 'getLastPush')]
#[CoversMethod(\Drupal\loco_translate\Utility::class, 'getLastPull')]
class UtilityTest extends UtilityTestBase {

  /**
   * Ensures enabled languages are detected correctly.
   *
   * @dataProvider getTestIsLangcodeEnabled
   */
  #[DataProvider('getTestIsLangcodeEnabled')]
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
  public static function getTestIsLangcodeEnabled(): iterable {
    return [
      ['fr', TRUE],
      ['fr-ch', FALSE],
      ['de', FALSE],
      ['de-ch', FALSE],
      ['en', TRUE],
    ];
  }

  /**
   * Ensures push timestamps are stored correctly.
   *
   * @dataProvider setterProvider
   */
  #[DataProvider('setterProvider')]
  public function testSetLastPush($langcode, $timestamp, array $previous_state, array $expected) {
    $this->state->get('loco_translate.api.push_last')->shouldBeCalled();
    $this->state->get('loco_translate.api.push_last')->willReturn($previous_state);

    $this->state->set('loco_translate.api.push_last', $expected)->shouldBeCalled();

    $this->utility->setLastPush($langcode, $timestamp);
  }

  /**
   * Ensures pull timestamps are stored correctly.
   *
   * @dataProvider setterProvider
   */
  #[DataProvider('setterProvider')]
  public function testSetLastPull($langcode, $timestamp, array $previous_state, array $expected) {
    $this->state->get('loco_translate.api.pull_last')->shouldBeCalled();
    $this->state->get('loco_translate.api.pull_last')->willReturn($previous_state);

    $this->state->set('loco_translate.api.pull_last', $expected)->shouldBeCalled();

    $this->utility->setLastPull($langcode, $timestamp);
  }

  /**
   * Provider of testSetLastPull.
   *
   * @return array
   *   Return an array of arrays.
   */
  public static function setterProvider(): iterable {
    return [
      'ensure an existing langcode will be replaced' => [
        'fr', 8000,
        ['fr' => 3600, 'en' => 3601],
        ['fr' => 8000, 'en' => 3601],
      ],
      'ensure a string timestamp still works as expected and return as integer' => [
        'fr', '8000',
        ['fr' => 3600, 'en' => 3601],
        ['fr' => 8000, 'en' => 3601],
      ],
      'ensure an non-int value will be casted' => [
        'fr', 'abcdef',
        ['fr' => 3600, 'en' => 3601],
        ['fr' => 0, 'en' => 3601],
      ],
      'ensure non-existing langcode will be added' => [
        'de', 8000,
        ['fr' => 3600, 'en' => 3601],
        ['fr' => 3600, 'en' => 3601, 'de' => '8000'],
      ],
      'ensure an empty state still works' => [
        'fr', 8000,
        [],
        ['fr' => 8000],
      ],
    ];
  }

  /**
   * Ensures push timestamps are returned correctly.
   *
   * @dataProvider getterProvider
   */
  #[DataProvider('getterProvider')]
  public function testGetLastPush($langcode, array $last_state, $expected) {
    $this->state->get('loco_translate.api.push_last')->shouldBeCalled();
    $this->state->get('loco_translate.api.push_last')->willReturn($last_state);

    $result = $this->utility->getLastPush($langcode);
    $this->assertSame($expected, $result);
  }

  /**
   * Ensures pull timestamps are returned correctly.
   *
   * @dataProvider getterProvider
   */
  #[DataProvider('getterProvider')]
  public function testGetLastPull($langcode, array $last_state, $expected) {
    $this->state->get('loco_translate.api.pull_last')->shouldBeCalled();
    $this->state->get('loco_translate.api.pull_last')->willReturn($last_state);

    $result = $this->utility->getLastPull($langcode);
    $this->assertSame($expected, $result);
  }

  /**
   * Provider of testSetLastPull & testSetLastPush.
   *
   * @return array
   *   Return an array of arrays.
   */
  public static function getterProvider(): iterable {
    return [
      'ensure an existing langcode will be returned' => [
        'fr',
        ['fr' => 3600, 'en' => 3601],
        3600,
      ],
      'ensure a string timestamp will still be returned as string' => [
        'fr',
        ['fr' => '8000', 'en' => 3601],
        8000,
      ],
      'ensure an non-int value will be still returned as string' => [
        'fr',
        ['fr' => 'abcdef', 'en' => 3601],
        0,
      ],
      'ensure non-existing langcode returned 0' => [
        'de',
        ['fr' => 3600, 'en' => 3601],
        0,
      ],
    ];
  }

}
