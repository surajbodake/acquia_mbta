<?php

declare(strict_types=1);

namespace Drupal\acquia_mbta\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Exception\RequestException;
use Drupal\Component\Serialization\Json;

/**
 * Controller for displaying the MBTA routes.
 */
class MbtaRouteController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The HTTP Client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Creates a MbtaRouteController object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client')
    );
  }

  /**
   * Get the routes from the API.
   *
   * @return array|null
   *   A render array as expected by drupal_render().
   */
  public function getRoutes() {
    try {
      $api_url = 'https://api-v3.mbta.com/routes';
      $api_key = '44a90ee707d1416a96a2584b925dc12b';
      $param = [
        'api_key' => $api_key,
      ];
      $response = $this->httpClient->request('GET', $api_url, $param);

      if ($response->getStatusCode() != 200) {
        return;
      }

      $content = Json::decode($response->getBody()->getContents());
      $data = $content->data;
      foreach ($data as $details) {
        $rows[] = [
          'data' => [
            [
              'data' => $details->attributes->long_name,
              'style' => 'color:#' . $details->attributes->text_color,
            ],
            [
              'data' => $details->attributes->description,
              'style' => 'color:#' . $details->attributes->text_color,
              'class' => ['mbta-col-sec'],
            ],
          ],
          'no_striping' => TRUE,
          'class' => 'mbta-tr',
          'style' => 'background-color:#' . $details->attributes->color,
        ];
      }

      return [
        '#theme' => 'table',
        '#header' => [
          [
            'data' => 'Name',
          ],
          [
            'data' => 'Description',
            'class' => ['mbta-col-sec'],
          ],
        ],
        '#rows' => $rows,
        '#attributes' => [
          'class' => 'mbta-table',
        ],
        '#attached' => [
          'library' => [
            'acquia_mbta/acquia_mbta.table',
          ],
        ],
      ];
    }
    catch (RequestException $e) {
      return $e->getMessage();
    }
  }

}
