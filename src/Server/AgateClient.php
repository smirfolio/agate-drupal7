<?php
/**
 * @file
 * Contains \Drupal\obiba_agate\Server\AgateClient
 */

namespace Drupal\obiba_agate\Server;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\obiba_agate\Form\AgateServerSettings;


use GuzzleHttp\ClientInterface;
//use http\Exception;

class AgateClient   implements AgateClientInterface{

  const OBIBA_COOKIE = 'obibaid';
  const OBIBA_COOKIE_OBJECT = 'obibaid_object';
  protected $agateUrl;
  protected $config;
  protected $httpClient;
  protected $entityTypeManager;

  public $user;

  /**
   * AgateClient constructor.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(ClientInterface $httpClient, EntityTypeManagerInterface $entityTypeManager) {
    $this->httpClient = $httpClient;
    $this->config = \Drupal::config(AgateServerSettings::AGATE_SERVER_SETTINGS);
    $this->agateUrl = $this->config->get(AgateServerSettings::CONFIG_PREFIX . 'url') . '/ws';
    $this->entityTypeManager = $entityTypeManager->getStorage('user');
  }

  /**
   * @param $userName
   * @param $password
   *
   * @return \Psr\Http\Message\ResponseInterface
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function authenticate($userName, $password) {
    try{
      $appName = 'drupal';
      $keyName = $this->config->get(AgateServerSettings::CONFIG_PREFIX . 'application_key');
      $headers = [
        'Accept' => [ 'application/json' ],
        'Content-Type' => [ 'application/x-www-form-urlencoded' ],
        'X-App-Auth' => [
          'Basic ' . base64_encode($appName. ':' . $keyName),
        ]
      ];
      return $this->httpClient->request(
        'POST',
        $this->agateUrl .  '/tickets',
        [
          'headers' => $headers,
          'form_params' => [
            'username' => $userName,
            'password' => $password
          ]
        ]

      );
    }catch (\Exception $e){
      watchdog_exception('Agate Exception', $e);
      return FALSE;
    }
  }

}