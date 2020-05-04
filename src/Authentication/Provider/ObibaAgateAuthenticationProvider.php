<?php

/**
 * @file
 * Contains \Drupal\obiba_agate\Authentication\Provider\ObibaAgateAuthenticationProvider.
 */

namespace Drupal\obiba_agate\Authentication\Provider;
use Drupal\obiba_agate\ObibaAgate;
use Drupal\obiba_agate\Controller\AgateUserManager;

use Drupal\externalauth\Authmap;

use Drupal\Core\Authentication\AuthenticationProviderInterface;
use Drupal\obiba_agate\Server\AgateClient;
use Drupal\user\Controller\UserAuthenticationController;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;





class ObibaAgateAuthenticationProvider extends UserAuthenticationController implements AuthenticationProviderInterface {

  /**
   * @var bool
   */
  protected $isAgateUser;

  protected $currentUserSession;
  /**
   * The logger service for OAuth.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * An authenticated user object.
   *
   * @var \Drupal\user\UserBCDecorator
   */
  protected $user;

  protected $agateUserManager;

    /**
     * @var \Drupal\externalauth\Authmap
     */
  protected $externalAuth;

  /**
   * ObibaAgateAuthenticationProvider constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   * @param \Drupal\obiba_agate\Controller\AgateUserManager $agateUserManager
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
    public function __construct(LoggerInterface $logger,
                                AgateUserManager $agateUserManager,
                                Authmap $externalAuth) {
        $this->agateUserManager = $agateUserManager;
        $this->externalAuth = $externalAuth;
        $this->logger = $logger;
        $this->isAgateUser =FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function authenticate(Request $request) {
    // TODO if current user have Ticket and have valid session in Agate it can process current page, else access denied
    // See D7 obiba_agate_boot()
      $userName = $request->get('name');
      if(isset($userName)){
          if($this->externalAuth->getUid($userName, ObibaAgate::AGATE_PROVIDER)){
              if($this->isAgateUser){
                  return $this->agateUserManager->agateLogin();
              }
          }
          else{
              return $this->agateUserManager->agateLoginRegister();
          }
      }
      return NULL;
  }

    /**
     * {@inheritdoc}
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
  public function applies(Request $request) {
      $userName = $request->get('name');
      $password = $request->get('pass');
    if($request->get('form_id') === 'user_login_form'){
        // check if it's a Agate User
        $this->isAgateUser = $this->agateUserManager->agateClient->authentication($userName, $password);
        if($this->isAgateUser){
            return TRUE;
        }
    }
    // Force Authentication if already existing valid Cookies (Single signOn)
      elseif($this->agateUserManager->agateClient::hasCookiesTicket() && !$this->agateUserManager->agateClient::hasTicket()){
          //Validate the cookies
          $user = $this->agateUserManager->agateClient->getSubjectNoAuth($_COOKIE[$this->agateUserManager->agateClient::OBIBA_COOKIE]);
          $_SESSION[$this->agateUserManager->agateClient::OBIBA_COOKIE] = $_COOKIE[$this->agateUserManager->agateClient::OBIBA_COOKIE];
          $this->agateUserManager->agateLogin($user);
          return FALSE;
      }
    // Force logout if no existing obibaid cookies or session and current user is an Agate User
      elseif(!$this->agateUserManager->agateClient::hasCookiesTicket() && !$this->agateUserManager->agateClient::hasTicket()){
        $currentUser = \Drupal::currentUser();
        if($currentUser->isAuthenticated() && $this->agateUserManager->externalAuth->load(\Drupal::currentUser()->getAccountName(), 'obiba_agate'))
          $this->agateUserManager->agateClient->logout();
          return FALSE;
      }
    return FALSE;
  }

    /**
     * {@inheritdoc}
     */
    public function cleanup(Request $request) {}

    /**
     * {@inheritdoc}
     */
    public function handleException(GetResponseForExceptionEvent $event) {
        return FALSE;
    }


}