<?php

namespace Drupal\Tests\loco_translate\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\loco_translate\Utility;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;

/**
 * Base class for Utility unit tests.
 */
abstract class UtilityTestBase extends UnitTestCase {

  /**
   * The Utility service of Loco Translate.
   *
   * @var \Drupal\loco_translate\Utility
   */
  protected $utility;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $en = new Language([
      'id'        => 'en',
      'name'      => 'English',
      'direction' => Language::DIRECTION_LTR,
      'weight'    => 0,
      'locked'    => FALSE,
    ]);

    $fr = new Language([
      'id'        => 'fr',
      'name'      => 'French',
      'direction' => Language::DIRECTION_LTR,
      'weight'    => 1,
      'locked'    => FALSE,
    ]);

    /** @var \Drupal\Core\Language\LanguageManagerInterface|\Prophecy\Prophecy\ProphecyInterface $language_manager */
    $language_manager = $this->prophesize(LanguageManagerInterface::class);

    $this->utility = new Utility($language_manager->reveal());
    $language_manager->getLanguages()
      ->willReturn(['en' => $en, 'fr' => $fr]);
  }

}
