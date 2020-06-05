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
  const ROLE_MICA_CLIENT = 'mica-user';
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
     * Check if ticket cookie already set.
     *
     * @param null $coockieName
     * @return bool
     */
    public static function hasCookiesTicket($coockieName = NULL) {
        return isset($_COOKIE[($coockieName ? $coockieName : self::OBIBA_COOKIE)]);
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
            $this->logError($e, __LINE__, __FILE__);
            $this->invalidateSession();
            user_logout();
            return FALSE;
        }
    }

    /**
     * @param String $user
     * @return array|mixed
     */
    public function createUser(String $user){
        try{
            $headers = [
                'Accept' => ['application/json'],
                'Content-Type' => 'application/x-www-form-urlencoded' ,
                'X-App-Auth' => $this->basicAgateAuth,
            ];

            $response = $this->httpClient->request(
                'POST',
                $this->agateUrl .  '/users/_join',
                [
                    'headers' => $headers,
                    'body' => $user,
                ]
            );

            return json_decode($response->getBody()->getContents());
        }catch (\Exception $e){
            $this->logError($e, __LINE__, __FILE__);
            return self::parseServerErrorCode($e);
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
              'body' => http_build_query([
                  'username' => $userName,
                  'password' => $password
              ]),
              ''
          ]
      );
      if($response->getStatusCode() == 201) {
          $this->setCookies($response->getHeader('Set-Cookie'));
          return TRUE;
        }
    }catch (\Exception $e){
        $this->logError($e, __LINE__, __FILE__);
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
                $this->logError($e, __LINE__, __FILE__);
            }
        }
        $this->invalidateSession();
        $this->redirectDrupal();
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
            $this->logError($e, __LINE__, __FILE__);
            return ;
        }
    }

    /**
     * Get The recaptcha client key
     *
     * @return mixed|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getServerRecaptcha(){
        try{
            $headers = [
                'Accept' => 'application/json' ,
                'X-App-Auth' => $this->basicAgateAuth,
            ];
            $response = $this->httpClient->request(
                'GET',
                $this->agateUrl .  '/config/client',
                [
                    'headers' => $headers,
                ]
            );
            return json_decode($response->getBody()->getContents());
        }catch (\Exception $e){
            $this->logError($e, __LINE__, __FILE__);
            return ;
        }
    }

    public function sendPassword($password, $resourceRequest = 'confirm'){
        try{
            $headers = [
                'Accept' => ['application/json'],
                'Content-Type' => 'application/x-www-form-urlencoded' ,
                'X-App-Auth' => $this->basicAgateAuth,
            ];

            $response = $this->httpClient->request(
                'POST',
                $this->agateUrl .  '/users/_' . $resourceRequest,
                [
                    'headers' => $headers,
                    'body' => http_build_query($password),
                ]
            );

            return [
                'message' => 'Password Sent',
                'code' => $response->getStatusCode()
            ];
        }catch (\Exception $e){
            $this->logError($e, __LINE__, __FILE__);
            return self::parseServerErrorCode($e);
        }
    }
    /**
     * Set Agate User cookies
     *
     * @param array $cookies
     */
    public function setCookies(array $cookies){
        $cookieParser = new CookieParser;
        $cookieObject = \stdClass::class;
        foreach ($cookies as $cookie){
            $cookieObject = $cookieParser->fromString($cookie);
            $_SESSION[$cookieObject->getName()] = $cookieObject->getValue();
            if(!$this->hasCookiesTicket($cookieObject->getName())){
                setcookie($cookieObject->getName(), $cookieObject->getValue(),
                    $cookieObject->getExpires(),$cookieObject->getPath(),$cookieObject->getDomain(),$cookieObject->getSecure());
            }
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
     * Parse the server error
     */
    protected static function parseServerErrorCode($serverError){
        preg_match('/(?<=\{)(.*)(?=\})/m', $serverError->getMessage(), $message);
        return [
            'code' => $serverError->getCode(),
            'message' =>  $message ? json_decode('{' . $message[0] . '}')->message : $serverError->getMessage(),
        ];
    }

    /**
     * Drupal Log  Errors
     * @param \Exception $e
     * @param $line
     * @param $file
     */
    private function logError(\Exception $e, $line, $file):void {
        //Todo add debug mode config to the agate module soo errors wil be logged
        \Drupal::logger('obiba_agate')->error('Agate Server -- Client Error Code:@code, Message: @message.
             In Line:@line , File:@file',
            [
                '@code' => $e->getCode(),
                '@message' => $e->getMessage(),
                '@line' => $line,
                '@file' => $file,
            ]);
    }
}