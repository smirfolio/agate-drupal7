<?php

/**
 * @file
 * Contains \Drupal\obiba_agate\Authentication\Provider\ObibaAgateAuthenticationProvider.
 */

namespace Drupal\obiba_agate\Authentication\Provider;

use Drupal\Core\Authentication\AuthenticationProviderInterface;
use Symfony\Component\HttpKernel\Exception;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use RuntimeException;
use \Drupal\user\Controller\UserAuthenticationController;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

use Drupal\obiba_agate\Controller\AgateUserManager;

class ObibaAgateAuthenticationProvider extends UserAuthenticationController implements AuthenticationProviderInterface {

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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
    public function __construct(LoggerInterface $logger,  AgateUserManager $agateUserManager, EntityTypeManagerInterface $entityTypeManager) {
      $this->entityTypeManager = $entityTypeManager->getStorage('user');
      $this->agateUserManager = $agateUserManager;
      $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function authenticate(Request $request) {
    // TODO if current user have Ticket and have valid session in Agate it can process current page, else access denied
    // See D7 obiba_agate_boot()
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(Request $request) {
    //ToDo if current user is externAuth aplies authentication
    // As Obiba agate is enabled it applies the Obiba Agate Authentication
    if($request->get('form_id') === 'user_login_form'){
        return TRUE;
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