<?php
/**
 * @file
 * Contains \Drupal\obiba_agate\Server\AgateClient
 */

namespace Drupal\obiba_agate\Server;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\obiba_agate\ObibaAgate;

use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\SetCookie as CookieParser;


class AgateClient   implements AgateClientInterface{

  const OBIBA_COOKIE = 'obibaid';
  const OBIBA_COOKIE_OBJECT = 'obibaid_object';
  protected $agateUrl;
  protected $config;
  protected $httpClient;
  protected $entityTypeManager;
  protected $basicAgateAuth;

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
    $this->config = \Drupal::config(ObibaAgate::AGATE_SERVER_SETTINGS);
    $appName = $this->config->get(ObibaAgate::CONFIG_PREFIX_SERVER . '.' . 'application_name');;
    $keyName = $this->config->get(ObibaAgate::CONFIG_PREFIX_SERVER . '.' . 'application_key');
    $this->agateUrl = $this->config->get(ObibaAgate::CONFIG_PREFIX_SERVER . '.' . 'url') . '/ws';
    $this->entityTypeManager = $entityTypeManager->getStorage('user');
    $this->basicAgateAuth = ['Basic ' . base64_encode($appName . ':' . $keyName)];
  }

    /**
     * Check if the user was authenticated by Agate.
     *
     * @return bool
     */
    public static function hasTicket() {
        return isset($_SESSION[self::OBIBA_COOKIE]) && isset($_SESSION[self::OBIBA_COOKIE_OBJECT]);
    }

    /**
     * Check if the user was authenticated by Agate.
     *
     * @return bool
     */
    public static function hasCookiesTicket() {
        return isset($_COOKIE[self::OBIBA_COOKIE]);
    }

    /**
     * Get the subject from the current Agate ticket.
     *
     * User needs to have been authenticated first.
     *
     * @return array
     *   The Subject of the current ticket.
     */
    public function getSubject() {
        if (!self::hasTicket()) {
            return FALSE;
        }
        return $this->getSubjectNoAuth();
    }

    /**
     * Get the user subject from agate by already stored cookies
     *
     * @param null $session
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSubjectNoAuth($session = NULL){
        try{
            $headers = [
                'Accept' => ['application/json'],
                'X-App-Auth' => $this->basicAgateAuth,
            ];

            $response = $this->httpClient->request(
                'GET',
                $this->agateUrl .  '/ticket/' . ($session ? $session : $_SESSION[self::OBIBA_COOKIE]) . '/subject',
                [
                    'headers' => $headers,
                ]
            );

            return json_decode($response->getBody()->getContents());
        }catch (\Exception $e){
            watchdog_exception('Agate Exception', $e);
            $this->invalidateSession();
            user_logout();
            return FALSE;
        }
    }


  /**
   * @param $userName
   * @param $password
   *
   * @return boolean
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function authentication($userName, $password) {
    try{
      $headers = [
        'Accept' => 'application/json' ,
        'Content-Type' => 'application/x-www-form-urlencoded' ,
        'X-App-Auth' => $this->basicAgateAuth,
      ];
      $response = $this->httpClient->request(
        'POST',
        $this->agateUrl .  '/tickets',
          [
              'headers' => $headers,
              'form_params' => [
                  'username' => $userName,
                  'password' => $password
              ],
              ''
          ]
      );
      if($response->getStatusCode() == 201) {
          $this->setCookies($response->getHeader('Set-Cookie'));
          return TRUE;
        }
    }catch (\Exception $e){
      watchdog_exception('Agate Exception', $e);
    }
      return FALSE;
  }

    /**
     * Logout from agate
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function logout(){
        if(self::hasTicket()){
            try{
                $headers = [
                    'Accept' => 'application/json' ,
                    'X-App-Auth' => $this->basicAgateAuth,
                ];
                $this->httpClient->request(
                    'DELETE',
                    $this->agateUrl .  '/ticket/' .  $_SESSION[self::OBIBA_COOKIE],
                    [
                        'headers' => $headers,
                        ''
                    ]
                );

            }catch (\Exception $e){
                watchdog_exception('Agate Exception', $e);
            }
        }
        $this->invalidateSession();
        $this->redirectDrupal();
    }

    /**
     * Set Agate User cookies
     *
     * @param array $cookies
     */
    protected function setCookies(array $cookies){
        $cookieParser = new CookieParser;
        $cookieObject = \stdClass::class;
        foreach ($cookies as $cookie){
            $cookieObject = $cookieParser->fromString($cookie);
            $_SESSION[$cookieObject->getName()] = $cookieObject->getValue();
            setcookie($cookieObject->getName(), $cookieObject->getValue(),
                $cookieObject->getExpires(),$cookieObject->getPath(),$cookieObject->getDomain(),$cookieObject->getSecure());
        }
        $_SESSION[self::OBIBA_COOKIE_OBJECT] = $cookieObject;
    }

    /**
     * Invalidate user sessions
     */
  private function invalidateSession(){
      $this->invalidateObibaCookies();
      $session_manager = \Drupal::service('session_manager');
      $session_manager->delete(\Drupal::currentUser()->id());
  }

    /**
     * Invalidate Cookies
     */
    public function invalidateObibaCookies(){
        $expire = \Drupal::time()->getRequestTime() - 3600; // expires with drupal session
        $path = empty($cookie['Path']) ? '/' : $cookie['Path'];
        $domain = empty($cookie['Domain']) ? NULL : $cookie['Domain'];
        $secure = empty($cookie['Secure']) ? FALSE : $cookie['Secure'];
        setrawcookie(self::OBIBA_COOKIE, '', $expire, $path, $domain, $secure);
    }

    /**
     * Redirect To home page
     */
    protected function redirectDrupal(){
        $url = Url::fromRoute('<front>')->toString();
        $response = new RedirectResponse($url);
        $response->send();
    }

    /**
     * Get fields to use in drupal
     *
     * @return mixed|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getConfigFormJoin(){
        try{
            $headers = [
                'Accept' => 'application/json' ,
                'X-App-Auth' => $this->basicAgateAuth,
            ];
            $response = $this->httpClient->request(
                'GET',
                $this->agateUrl .  '/config/join',
                [
                    'headers' => $headers,
                ]
            );
            return json_decode($response->getBody()->getContents(), TRUE);
        }catch (\Exception $e){
            watchdog_exception('Agate Exception', $e);
            return ;
        }
    }

}