<?php

namespace Drupal\Tests\loco_translate\Unit;

use Drupal\Tests\UnitTestCase;

use Drupal\loco_translate\UploadTranslations;
use Loco\Http\ApiClient;
use Drupal\loco_translate\Exception\LocoApiException;

/**
 * @coversDefaultClass \Drupal\loco_translate\UploadTranslations
 *
 * @group loco_translate
 * @group loco_translate_unit
 * @group loco_translate_unit_upload
 */
class UploadTranslationsTest extends UnitTestCase {

  /**
   * Uploader to Loco.
   *
   * @var \Drupal\loco_translate\UploadTranslations
   */
  private $uploader;

  /**
   * A mock of Loco SDK API client.
   *
   * @var \Loco\Http\ApiClient
   */
  private $apiClient;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->apiClient = $this->prophesize(ApiClient::class);
    $this->uploader = new UploadTranslations($this->apiClient->reveal());
  }

  /**
   * @covers ::uploadFile
   */
  public function testUploadFileSuccess() {
    $file = __DIR__ . '/../../modules/loco_translate_test/assets/fr.po';
    $data = file_get_contents($file);
    $response = json_decode(file_get_contents(__DIR__ . '/../../modules/loco_translate_test/responses/import-200.json'), TRUE);

    $this->apiClient->import([
      'data' => $data,
      'locale' => 'fr',
      'ext' => 'po',
      'ignore-existing' => TRUE,
      'tag-absent' => 'absent',
    ])->willReturn($response);

    $result = $this->uploader->uploadFile($file, 'fr');
    $this->assertEquals($result, $response);
  }

  /**
   * @covers ::uploadFile
   */
  public function testUploadFileFailed404() {
    $file = __DIR__ . '/../../modules/loco_translate_test/assets/fr.po';
    $data = file_get_contents($file);
    $response = json_decode(file_get_contents(__DIR__ . '/../../modules/loco_translate_test/responses/import-404.json'), TRUE);

    $this->apiClient->import([
      'data' => $data,
      'locale' => 'fr',
      'ext' => 'po',
      'ignore-existing' => TRUE,
      'tag-absent' => 'absent',
    ])->willReturn($response);

    $this->setExpectedException(LocoApiException::class, "Loco upload failed. Returned status 404. With message: Locale not in project.");
    $this->uploader->uploadFile($file, 'fr');
  }

  /**
   * @covers ::uploadFile
   */
  public function testUploadFileFailed403() {
    $file = __DIR__ . '/../../modules/loco_translate_test/assets/fr.po';
    $data = file_get_contents($file);
    $response = json_decode(file_get_contents(__DIR__ . '/../../modules/loco_translate_test/responses/import-403.json'), TRUE);

    $this->apiClient->import([
      'data' => $data,
      'locale' => 'fr',
      'ext' => 'po',
      'ignore-existing' => TRUE,
      'tag-absent' => 'absent',
    ])->willReturn($response);

    $this->setExpectedException(LocoApiException::class, "Loco upload failed. Returned status 403. With message: Read-only key disallows POST.");
    $this->uploader->uploadFile($file, 'fr');
  }

}
