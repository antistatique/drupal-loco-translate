<?php

namespace Drupal\Tests\loco_translate\Unit;

use Drupal\Tests\UnitTestCase;

use Drupal\loco_translate\UploadTranslations;
use Loco\Http\ApiClient;

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
   * Loco SDK API client.
   *
   * @var \Loco\Http\ApiClient
   */
  private $apiClient;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->apiClient = $this->prophetize(ApiClient::class);

    $this->uploader = new UploadTranslations($this->apiClient->reveal());
  }

  /**
   * @covers ::uploadFile
   */
  public function testUploadFile() {
    $file = __DIR__ . '/../../modules/loco_translate_test/assets/fr.po';
    $this->apiClient->expect()->with('import', [
      'data' => 'LOT OF RAW DATA. TODO',
      'locale' => 'fr',
      'ext' => 'po',
        // ...
    ]);
    $result = $this->uploader->uploadFile($file, 'fr');
  }

}
