<?php

namespace Drupal\Tests\loco_translate\Unit;

use Drupal\loco_translate\Exception\LocoApiException;
use Drupal\loco_translate\Loco\Push as LocoPush;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Command\Result;
use Loco\Http\ApiClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Tests pushing translations to Loco.
 *
 * @group loco_translate
 */
#[Group('loco_translate')]
#[CoversClass(\Drupal\loco_translate\Loco\Push::class)]
#[CoversMethod(\Drupal\loco_translate\Loco\Push::class, 'fromFileToLoco')]
class LocoPushTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * Uploader to Loco.
   *
   * @var \Drupal\loco_translate\Loco\Push
   */
  private $locoPush;

  /**
   * A mock of Loco SDK API client.
   *
   * @var \Loco\Http\ApiClient
   */
  private $apiClient;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Mock a fake Loco API Client.
    $this->apiClient = $this->prophesize(ApiClient::class);

    // Mock the loco push manager.
    $this->locoPush = new LocoPush($this->apiClient->reveal());
  }

  /**
   * Ensures translations can be pushed successfully.
   */
  public function testPushFromFileToLocoSuccess() {
    $file = __DIR__ . '/../../modules/loco_translate_test/assets/fr.po';
    $data = file_get_contents($file);
    $response = new Result(json_decode(file_get_contents(__DIR__ . '/../../modules/loco_translate_test/responses/import-200.json'), TRUE));

    $this->apiClient->import([
      'data' => $data,
      'locale' => 'fr',
      'ext' => 'po',
      'ignore-existing' => TRUE,
      'tag-absent' => 'absent',
      'index' => NULL,
    ])->willReturn($response);

    $result = $this->locoPush->fromFileToLoco($file, 'fr');
    $this->assertEquals($result, $response);
  }

  /**
   * Ensures translations can be pushed with a custom index.
   */
  public function testPushFromFileToLocoAlteredIndexSuccess() {
    $file = __DIR__ . '/../../modules/loco_translate_test/assets/fr.po';
    $data = file_get_contents($file);
    $response = new Result(json_decode(file_get_contents(__DIR__ . '/../../modules/loco_translate_test/responses/import-200.json'), TRUE));

    $this->apiClient->import([
      'data' => $data,
      'locale' => 'fr',
      'ext' => 'po',
      'ignore-existing' => TRUE,
      'tag-absent' => 'absent',
      'index' => 'id',
    ])->willReturn($response);

    $result = $this->locoPush->fromFileToLoco($file, 'fr', 'id');
    $this->assertEquals($result, $response);
  }

  /**
   * Ensures a 404 response raises an API exception.
   */
  public function testPushFromFileToLocoFailed404() {
    $file = __DIR__ . '/../../modules/loco_translate_test/assets/fr.po';
    $data = file_get_contents($file);
    $response = new Result(json_decode(file_get_contents(__DIR__ . '/../../modules/loco_translate_test/responses/import-404.json'), TRUE));

    $this->apiClient->import([
      'data' => $data,
      'locale' => 'fr',
      'ext' => 'po',
      'ignore-existing' => TRUE,
      'tag-absent' => 'absent',
      'index' => NULL,
    ])->willReturn($response);

    $this->expectException(LocoApiException::class);
    $this->expectExceptionMessage("Loco upload failed. Returned status 404. With message: Locale not in project.");
    $this->locoPush->fromFileToLoco($file, 'fr');
  }

  /**
   * Ensures a 403 response raises an API exception.
   */
  public function testPushFromFileToLocoFailed403() {
    $file = __DIR__ . '/../../modules/loco_translate_test/assets/fr.po';
    $data = file_get_contents($file);
    $response = new Result(json_decode(file_get_contents(__DIR__ . '/../../modules/loco_translate_test/responses/import-403.json'), TRUE));

    $this->apiClient->import([
      'data' => $data,
      'locale' => 'fr',
      'ext' => 'po',
      'ignore-existing' => TRUE,
      'tag-absent' => 'absent',
      'index' => NULL,
    ])->willReturn($response);

    $this->expectException(LocoApiException::class);
    $this->expectExceptionMessage("Loco upload failed. Returned status 403. With message: Read-only key disallows POST.");
    $this->locoPush->fromFileToLoco($file, 'fr');
  }

}
