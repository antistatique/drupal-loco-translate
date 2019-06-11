<?php

namespace Drupal\loco_translate;

use Loco\Http\ApiClient;

/**
 * Upload Translations to Loco.
 */
class UploadTranslations {

  /**
   * The Loco SDK HTTP client.
   *
   * @var \Loco\Http\ApiClientLocoApiClient
   */
  private $client;

  /**
   * Constructor.
   *
   * @param \Loco\Http\ApiClient $apiClient
   *   Loco Api Client.
   */
  public function __construct(ApiClient $apiClient) {
    $this->client = $apiClient;
  }

  /**
   * Upload the given .po file into Loco.
   *
   * @param string $po_file
   *   The .po file to upload on Loco.
   * @param string $locale
   *   The local code.
   */
  public function uploadFile($po_file, $locale) {
    $file = realpath($po_file);

    if (!file_exists($file) || !is_file($file)) {
      throw new \InvalidArgumentException(sprintf('PO File "%s" does not exists.', $file));
    }

    if (!is_readable($file)) {
      throw new \InvalidArgumentException(sprintf('PO File "%s" is not readable.', $file));
    }

    // TODO: Check Basic PO Formats.
    $data = file_get_contents($file);

    $result = $this->client->import([
      'data' => $data,
      'locale' => $locale,
      'ext' => 'po',
      'ignore-existing' => TRUE,
      'tag-absent' => 'absent',
    ]);

    if (!$result['status'] !== 200) {
      throw new \RuntimeException(sprintf('Upload failed. Loco returned status %s', $result['status']));
    }

    return $result;
  }

}
