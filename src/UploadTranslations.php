<?php

namespace Drupal\loco_translate;

use Loco\Http\ApiClient;

/**
 * Upload Translations to Loco.
 */
class UploadTranslations
{

    /**
     * @var \Loco\Http\ApiClient Loco Api Client.
     */
    private $client;

    /**
     * Constructor.
     *
     * @param \Loco\Http\ApiClient $apiClient Loco Api Client.
     */
    public function __construct(ApiClient $apiClient)
    {
        $this->client = $apiClient;

//        // Testing...
//        $this->client = ApiClient::factory([
//            'key' => 'RkfYjWGWbHm06MO5QVKUj7vGgp7SYh62l',
//        ]);
    }


    /**
     * @param string $poFile
     * @param string $locale
     */
    public function uploadFile($poFile, $locale)
    {
        $file = realpath($poFile);

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
            'tag-absent' => 'absent'
        ]);

        if (!$result['status'] !== 200) {
            throw new \RuntimeException(sprintf('Upload failed. Loco returned status %s', $result['status']));
        }

        return $result;
    }
}