<?php

declare(strict_types=1);

namespace Drupal\acquia_mbta\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\acquia_mbta\Controller\MbtaRouteController;

/**
 * Provides a MBTA routes block.
 *
 * @Block(
 *   id = "mbta_route_block",
 *   admin_label = @Translation("MBTA route Block"),
 *   category = @Translation("Custom"),
 * )
 */
class MbtaRouteBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The class resolver service.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface
   */
  protected $classResolver;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('class_resolver'),
      $container->get('renderer')
    );
  }

  /**
   * Creates a MbtaRouteBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $class_resolver
   *   The class resolver service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ClassResolverInterface $class_resolver, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->classResolver = $class_resolver;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the instance of MbtaRouteController class.
    $controller = $this->classResolver->getInstanceFromDefinition(MbtaRouteController::class);
    $routeTable = $controller->getRoutes();

    // Render the content from the getRoutes method.
    $renderedContent = $this->renderer->renderRoot($routeTable);

    // Return the output.
    return [
      '#markup' => $renderedContent,
      '#attached' => [
        'library' => [
          'acquia_mbta/acquia_mbta.table',
        ],
      ],
    ];
  }

}
