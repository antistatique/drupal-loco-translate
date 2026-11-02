<?php

namespace Drupal\loco_translate\Hook;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Implements hook_help().
 *
 * Hook to generate help page.
 */
#[Hook('help')]
final class HelpHook {
  use StringTranslationTrait;

  /**
   * Constructs a new HelpHook object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   * @param \Drupal\Component\Plugin\PluginManagerInterface|null $filterManager
   *   The filter plugin manager, NULL when the Filter module is not installed.
   */
  public function __construct(
    private readonly ModuleHandlerInterface $moduleHandler,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly ?PluginManagerInterface $filterManager = NULL,
  ) {
  }

  /**
   * Render the module README as help page.
   *
   * @param string $route_name
   *   The router name the help is being requested for.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   *
   * @return string|null
   *   The help text, or NULL when the route is not handled.
   */
  public function __invoke(string $route_name, RouteMatchInterface $route_match): ?string {
    if ($route_name !== 'help.page.loco_translate') {
      return NULL;
    }

    $readme = dirname(__DIR__, 2) . '/README.md';
    $text = file_get_contents($readme);
    $output = '';

    // If the Markdown module is installed, use it to render the README.
    if ($text && $this->filterManager !== NULL && $this->moduleHandler->moduleExists('markdown') === TRUE) {
      $settings = $this->configFactory->get('markdown.settings')->getRawData();
      $config = ['settings' => $settings];
      $filter = $this->filterManager->createInstance('markdown', $config);
      $output = $filter->process($text, 'en');
    }
    // Else the Markdown module is not installed output the README as text.
    elseif ($text) {
      $output = '<pre>' . $text . '</pre>';
    }

    // Add a link to the Drupal.org project.
    $output .= '<p>';
    $output .= $this->t('Visit the <a href=":project_link">Loco Translate project page</a> on Drupal.org for more information.', [
      ':project_link' => 'https://www.drupal.org/project/loco_translate',
    ]);
    $output .= '</p>';

    return $output;
  }

}
