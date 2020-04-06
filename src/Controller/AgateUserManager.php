<?php
/**
 * @file
 * Contains \Drupal\obiba_agate\Server\AgateUserManager
 */

namespace Drupal\obiba_agate\Controller;
use Drupal\obiba_agate\Server\AgateClient;

use Drupal\externalauth\ExternalAuth;
use Drupal\Core\Controller\ControllerBase;

class AgateUserManager extends ControllerBase{
  protected $externalAuth;
  protected $user ;
  protected $agateclient;

  /**
   * @var string
   */
  private $authProvider = 'obiba_agate';

  public function __construct(AgateClient $agateClient, ExternalAuth $external_auth) {
    $this->agateclient = $agateClient;
    $this->externalAuth = $external_auth;
  }


  public function agateLogin($userName, $password){
    if($this->agateclient->agateAuthentication($userName, $password)){
     // $this->user =$this->externalAuth->load($userName, $password);
      //is external user -> update external user -> return user
      //Not an external user -> Create external user -> return user
    }
  return FALSE;
  }

}