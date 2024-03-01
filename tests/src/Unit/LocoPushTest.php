<?php

namespace Drupal\Tests\loco_translate\Unit;

use Drupal\loco_translate\Exception\LocoApiException;
use Drupal\loco_translate\Loco\Push as LocoPush;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Command\Result;
use Loco\Http\ApiClient;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @coversDefaultClass \Drupal\loco_translate\Loco\Push
 *
 * @group loco_translate
 * @group loco_translate_unit
 * @group loco_translate_unit_upload
 */
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
    // Mock a fake Loco API Client.
    $this->apiClient = $this->prophesize(ApiClient::class);

    // Mock the loco push manager.
    $this->locoPush = new LocoPush($this->apiClient->reveal());
  }

  /**
   * @covers ::fromFileToLoco
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
   * @covers ::fromFileToLoco
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
   * @covers ::fromFileToLoco
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
   * @covers ::fromFileToLoco
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
