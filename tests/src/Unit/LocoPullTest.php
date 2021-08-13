<?php

namespace Drupal\Tests\loco_translate\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\loco_translate\Loco\Pull as locoPull;
use Loco\Http\ApiClient;
use Drupal\loco_translate\Utility;
use Drupal\loco_translate\Exception\LocoApiException;
use Loco\Http\Result\RawResult;
use GuzzleHttp\Psr7\Response;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\loco_translate\Loco\Pull
 *
 * @group loco_translate
 * @group loco_translate_unit
 * @group loco_translate_unit_upload
 */
class LocoPullTest extends UnitTestCase {

  /**
   * The Loco translations pull manager.
   *
   * @var \Drupal\loco_translate\Loco\Pull
   */
  private $locoPull;

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
    /** @var \Drupal\loco_translate\Utility|\Prophecy\Prophecy\ProphecyInterface $language_manager */
    $utility = $this->prophesize(Utility::class);
    $utility->isLangcodeEnabled(Argument::any())
      ->willReturn(TRUE);

    // Mock a fake Loco API Client.
    $this->apiClient = $this->prophesize(ApiClient::class);

    // Mock the loco pull manager.
    $this->locoPull = new locoPull($this->apiClient->reveal(), $utility->reveal());
  }

  /**
   * @covers ::fromLocoToDrupal
   */
  public function testPullFromLocoToDrupalSuccess() {
    $data = file_get_contents(__DIR__ . '/../../modules/loco_translate_test/responses/export-200.po');
    $response = new Response(200, [], $data);
    $response = RawResult::fromResponse($response);

    $this->apiClient->exportLocale([
      'ext' => 'po',
      'index' => NULL,
      'locale' => 'fr',
      'no-folding' => TRUE,
      'status' => 'translated',
    ])->willReturn($response);

    $result = $this->locoPull->fromLocoToDrupal('fr', 'translated');
    $this->assertEquals($result->__toString(), $data);
  }

  /**
   * @covers ::fromLocoToDrupal
   */
  public function testPullFromLocoToDrupalAlteredIndex() {
    $data = file_get_contents(__DIR__ . '/../../modules/loco_translate_test/responses/export-200.po');
    $response = new Response(200, [], $data);
    $response = RawResult::fromResponse($response);

    $this->apiClient->exportLocale([
      'ext' => 'po',
      'index' => 'id',
      'locale' => 'fr',
      'no-folding' => TRUE,
      'status' => 'translated',
    ])->willReturn($response);

    $result = $this->locoPull->fromLocoToDrupal('fr', 'translated', 'id');
    $this->assertEquals($result->__toString(), $data);
  }

  /**
   * @covers ::fromLocoToDrupal
   */
  public function testPullFromLocoToDrupalSucessNoStatus() {
    $data = file_get_contents(__DIR__ . '/../../modules/loco_translate_test/responses/export-404.po');
    $response = new Response(404, [], $data);
    $response = RawResult::fromResponse($response);

    // Ony any non-200 HTTP response, Guzzle will throw an exception.
    $this->apiClient->exportLocale([
      'ext' => 'po',
      'index' => NULL,
      'locale' => 'fr',
      'no-folding' => TRUE,
      'status' => NULL,
    ])->willThrow(new \Exception());

    $this->expectException(LocoApiException::class);
    $this->locoPull->fromLocoToDrupal('fr');
  }

  /**
   * @covers ::fromLocoToDrupal
   */
  public function testPullFromLocoToDrupalException() {
    $data = file_get_contents(__DIR__ . '/../../modules/loco_translate_test/responses/export-404.po');
    $response = new Response(404, [], $data);
    $response = RawResult::fromResponse($response);

    // Ony any non-200 HTTP response, Guzzle will throw an exception.
    $this->apiClient->exportLocale([
      'ext' => 'po',
      'index' => NULL,
      'locale' => 'fr',
      'no-folding' => TRUE,
      'status' => 'translated',
    ])->willThrow(new \Exception());

    $this->expectException(LocoApiException::class);
    $this->locoPull->fromLocoToDrupal('fr', 'translated');
  }

}
