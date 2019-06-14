<?php

namespace Drupal\loco_translate\Commands;

use Drupal\loco_translate\Loco\Push;
use Drush\Commands\DrushCommands;

/**
 * Drush Loco Push Commands.
 */
class PushCommand extends DrushCommands {

  /**
   * Loco Push Api Wrapper.
   *
   * @var \Drupal\loco_translate\Loco\Push
   */
  private $locoPush;

  /**
   * PushCommand constructor.
   *
   * @param \Drupal\loco_translate\Loco\Push $locoPush
   *   The Loco Push Api Wrapper.
   */
  public function __construct(Push $locoPush) {
    $this->locoPush = $locoPush;
  }

  /**
   * Push a local .po file into Loco SAAS.
   *
   * @param string $file
   *   Path of the local .po file you want to push into Loco.
   * @param array $options
   *   (Optional) An array of options.
   *
   * @command loco_translate:push
   *
   * @option language
   *   Ex: 'en' or 'fr'. Define in which language the .po file is written
   *   and in which locale you want to import this file into Loco.
   *   [default: detect the language from the .po filename]
   *
   * @aliases loco:push
   *
   * @usage drush loco_translate:push --language="fr" ./translations/fr.po
   *   Push a local .po file into the Loco SAAS in the french locale.
   */
  public function push($file, array $options = ['language' => NULL]) {
    // Try to guess the language.
    $language = $options['language'];
    if (NULL === $language) {
      $filename = pathinfo($file, PATHINFO_FILENAME);
      $language = $filename;
    }

    $this->output()->writeln(sprintf('Pushing file "%s" in locale "%s"', $file, $language));

    $this->locoPush->setApiClientFromConfig();

    $response = $this->locoPush->fromFileToLoco($file, $language);

    $this->output()->writeln($response['message']);
  }

}
