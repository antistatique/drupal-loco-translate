<?php

namespace Drupal\Tests\loco_translate\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\loco_translate\Utility;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Language\Language;

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
   * The state mocked service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
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
    /** @var \Drupal\Core\State\StateInterface|\Prophecy\Prophecy\ProphecyInterface $state */
    $this->state = $this->prophesize(StateInterface::class);

    $this->utility = new Utility($language_manager->reveal(), $this->state->reveal());
    $language_manager->getLanguages()
      ->willReturn(['en' => $en, 'fr' => $fr]);
  }

}
