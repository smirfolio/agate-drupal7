<?php

/**
 * @file
 * AgateClient class
 */


/**
 * Class AgateClient
 */
class AgateClient {

  const SET_COOKIE_HEADER = 'Set-Cookie';

  const OBIBA_COOKIE = 'obibaid';

  private $agateUrl;

  private $applicationName;

  private $applicationKey;

  private $lastResponse;

  /**
   * Check if the user was authenticated by Agate.
   * @return bool
   */
  public static function hasTicket() {
    return isset($_SESSION[self::OBIBA_COOKIE]);
  }

  /**
   * AgateClient constructor: if no parameters are provided, default are extracted from Drupal variables.
   * @param $agate_url
   * @param $application_name
   * @param $application_key
   */
  public function __construct($agate_url = NULL, $application_name = NULL, $application_key = NULL) {
    $this->agateUrl = (isset($agate_url) ? $agate_url : variable_get_value('agate_url')) . '/ws';
    $this->applicationName = (isset($application_name) ? $application_name : variable_get_value('agate_application_name'));
    $this->applicationKey = (isset($application_key) ? $application_key : variable_get_value('agate_application_key'));
  }

  /**
   * Get the last response (if any).
   * @return mixed
   */
  public function getLastResponse() {
    return $this->lastResponse;
  }

  /**
   * Get the last response headers (if any).
   * @return array
   */
  public function getLastResponseHeaders() {
    if ($this->lastResponse != NULL) {
      $result = array();
      foreach (explode("\r\n", $this->lastResponse->headers) as $header) {
        $h = explode(":", $header, 2);
        if (count($h) == 2) {
          if (!array_key_exists($h[0], $result)) {
            $result[$h[0]] = array();
          }
          array_push($result[$h[0]], trim($h[1]));
        }
      }
      return $result;
    }
    return '';
  }

  /**
   * Get the header value(s) from the last response.
   * @param $header_name
   * @return array
   */
  public function getLastResponseHeader($header_name) {
    $headers = $this->getLastResponseHeaders();

    if (array_key_exists($header_name, $headers)) {
      return $headers[$header_name];
    }
    return array();
  }


  /**
   * Get form registration from agate
   * @return Json form
   * @throws Exception
   */
  public function get_config_form_join() {
    $this->lastResponse = NULL;
    $url = $this->agateUrl . '/config/join';
    $request = new HttpClientRequest($url, array(
      'method' => HttpClientRequest::METHOD_GET,
      'headers' => array(
        'Accept' => array('application/json'),
        'X-App-Auth' => array($this->authorizationHeader())
      ),
    ));

    $client = $this->client();
    try {
      $body = $client->execute($request);
      $this->lastResponse = $client->lastResponse;
      return json_decode($body, TRUE);
    }
    catch (HttpClientException $e) {
      watchdog('Agate Client', 'Request failed, @code: @message',
        array('@code' => $e->getCode(), '@message' => $e->getMessage()), WATCHDOG_WARNING);
      return array();
    }
  }

  /**
   * Get form registration from agate
   * @return Json form
   * @throws Exception
   */
  public function get_recaptcha_key_config_form_join() {
    $this->lastResponse = NULL;
    $url = $this->agateUrl . '/config/client';
    $request = new HttpClientRequest($url, array(
      'method' => HttpClientRequest::METHOD_GET,
      'headers' => array(
        'Accept' => array('application/json'),
        'X-App-Auth' => array($this->authorizationHeader())
      )
    ));

    $client = $this->client();
    try {

      $body = $client->execute($request);
      $this->lastResponse = $client->lastResponse;
      $response_google = json_decode($body);
      return $response_google->reCaptchaKey;
    }
    catch (HttpClientException $e) {
      watchdog('Agate Client', 'Request failed, @code: @message',
        array('@code' => $e->getCode(), '@message' => $e->getMessage()), WATCHDOG_WARNING);
      return array();
    }
  }

  /**
   * Append defaults user role
   * @return string
   */
  public function append_auto_roles($username) {
    $groups = '';
    if (!empty($username)) {
      $account = user_external_load($username);
      if (!empty($account->roles)) {
        foreach ($account->roles as $role) {
          if (!empty($role) && strstr($role, 'mica')) {
            $groups .= '&group=' . $role;
          }
        }
        if (!empty($groups) && $groups != '') {
          return $groups;
        }
      }
    }
    $db_roles = user_roles();
    foreach (variable_get('obiba_auto_assigned_role', array('mica-user')) as $group) {
      if (!empty($group) && !empty($db_roles[$group]) && strstr($db_roles[$group], 'mica')) {
        $groups .= '&group=' . $db_roles[$group];
      }
      //default group user mica-user
      else{
        $groups .= '&group=' . 'mica-user';
      }
    }
    return $groups;
  }

  /**
   * Add user to Agate
   * @param $user_data
   * @return TRUE
   * @throws Exception
   */
  public function add_agate_user($user_data) {
    $this->lastResponse = NULL;
    $url = $this->agateUrl . '/users/_join';
    $data = http_build_query($user_data);
    $roles = $this->append_auto_roles(empty($user_data['username']) ? null : $user_data['username']);
    $request = new HttpClientRequest($url, array(
      'method' => HttpClientRequest::METHOD_POST,
      'headers' => array(
        'Accept' => array('application/json'),
        'Content-Type' => array('application/x-www-form-urlencoded'),
        'X-App-Auth' => array($this->authorizationHeader())
      ),
      'data' => $data . $roles,
    ));
    $client = $this->client();
    try {
      $body = $client->execute($request);
      $this->lastResponse = $client->lastResponse;
      $this->setLastResponseCookies();
      return TRUE;
    }
    catch (HttpClientException $e) {
      watchdog('Agate Client', 'Request failed, @code: @message',
        array('@code' => $e->getCode(), '@message' => $e->getMessage()), WATCHDOG_WARNING);
      return array('codeError' => $e->getCode(), 'message' => $e->getResponse()->body);
    }
  }

  /**
   * Get form registration from agate.
   *
   * @return Json form
   * @throws Exception
   */
  public function password_request($data) {
    $data = http_build_query($data);
    $this->lastResponse = NULL;
    $url = $this->agateUrl . '/users/_forgot_password';
    $request = new HttpClientRequest($url, array(
      'method' => HttpClientRequest::METHOD_POST,
      'headers' => array(
        'Accept' => array('application/json'),
        'Content-Type' => array('application/x-www-form-urlencoded'),
        'X-App-Auth' => array($this->authorizationHeader())
      ),
      'data' => $data,
    ));
    $client = $this->client();
    try {
      $body = $client->execute($request);
      if (!empty($body)) {
        $this->lastResponse = $client->lastResponse;
      }
      return json_decode($body, TRUE);
    }
    catch (HttpClientException $e) {
      watchdog('Agate Client', 'Request failed, @code: @message',
        array('@code' => $e->getCode(), '@message' => $e->getMessage()), WATCHDOG_WARNING);
      return array();
    }
  }

  /**
   * Confirm user registration.
   *
   * @return Json form
   * @throws Exception
   */
  public function send_password($data, $request_resource='confirm') {
    $data = http_build_query($data);
    $this->lastResponse = NULL;
    $url = $this->agateUrl . '/users/_'.$request_resource;
    $request = new HttpClientRequest($url, array(
      'method' => HttpClientRequest::METHOD_POST,
      'headers' => array(
        'Accept' => array('application/json'),
        'Content-Type' => array('application/x-www-form-urlencoded'),
        'X-App-Auth' => array($this->authorizationHeader())
      ),
      'data' => $data,
    ));
    $client = $this->client();
    try {
      $body = $client->execute($request);
      if (!empty($body)) {
        $this->lastResponse = $client->lastResponse;
      }
      return json_decode($body, TRUE);
    }
    catch (HttpClientException $e) {
      $body_response_message = json_decode($e->getResponse()->body);
      watchdog('Agate Client', 'Request failed, @code: @message',
        array(
          '@code' => $body_response_message->code,
          '@message' => $body_response_message->message
        ), WATCHDOG_WARNING);
      return array('code' => $body_response_message->code, 'message' => $body_response_message->message);
    }
  }


  /**
   * Perform authentication by user name and forwards any cookie from Agate to the drupal client.
   * @param $username
   * @param $password
   * @return bool
   * @throws Exception
   */
  public function authenticate($username, $password) {
    $this->lastResponse = NULL;
    $url = $this->agateUrl . '/tickets';
    $data = 'username=' . $username . '&password=' . $password;
    $request = new HttpClientRequest($url, array(
      'method' => HttpClientRequest::METHOD_POST,
      'headers' => array(
        'Accept' => array('application/json'),
        'Content-Type' => array('application/x-www-form-urlencoded'),
        'X-App-Auth' => array($this->authorizationHeader())
      ),
      'data' => $data,
    ));

    $client = $this->client();
    try {
      $body = $client->execute($request);
      $this->lastResponse = $client->lastResponse;
      $this->setLastResponseCookies();

      return TRUE;
    }
    catch (HttpClientException $e) {
      watchdog('Agate Client', 'Request failed, @code: @message',
        array('@code' => $e->getCode(), '@message' => $e->getMessage()), WATCHDOG_WARNING);
      return FALSE;
    }
  }

  /**
   * Get the user name matching the Agate token.
   * @param $token
   * @return mixed|null
   */
  public function validate($token) {
    $this->lastResponse = NULL;
    $url = $this->agateUrl . '/ticket/' . $token . '/username';
    $request = new HttpClientRequest($url, array(
      'method' => HttpClientRequest::METHOD_GET,
      'headers' => array(
        'Accept' => array('text/plain', 'application/json'),
        'X-App-Auth' => array($this->authorizationHeader())
      ),
    ));

    $client = $this->client();
    try {
      $body = $client->execute($request);
      $this->lastResponse = $client->lastResponse;
      $_SESSION[self::OBIBA_COOKIE] = $token;
      return $body;
    }
    catch (HttpClientException $e) {
      watchdog('Agate Client', 'Request failed, @code: @message',
        array('@code' => $e->getCode(), '@message' => $e->getMessage()), WATCHDOG_WARNING);
      return NULL;
    }
  }

  /**
   * Get the subject from the current Agate ticket (user needs to have been authenticated first).
   * @return array
   * @throws Exception
   */
  public function getSubject() {
    if (!AgateClient::hasTicket()) {
      return array();
    }
    $this->lastResponse = NULL;
    $url = $this->agateUrl . '/ticket/' . $_SESSION[self::OBIBA_COOKIE] . '/subject';
    $request = new HttpClientRequest($url, array(
      'method' => HttpClientRequest::METHOD_GET,
      'headers' => array(
        'Accept' => array('application/json'),
        'X-App-Auth' => array($this->authorizationHeader())
      ),
    ));

    $client = $this->client();
    try {
      $body = $client->execute($request);
      $this->lastResponse = $client->lastResponse;
      return json_decode($body, TRUE);
    }
    catch (HttpClientException $e) {
      watchdog('Agate Client', 'Request failed, @code: @message',
        array('@code' => $e->getCode(), '@message' => $e->getMessage()), WATCHDOG_WARNING);
      return array();
    }
  }

  /**
   * Send a logout request to Agate and clean drupal client cookies.
   */
  public function logout() {
    if (!AgateClient::hasTicket()) {
      return;
    }

    $url = $this->agateUrl . '/ticket/' . $_SESSION[self::OBIBA_COOKIE];
    $request = new HttpClientRequest($url, array(
      'method' => HttpClientRequest::METHOD_DELETE,
      'headers' => array(
        'Accept' => array('application/json'),
        'X-App-Auth' => array($this->authorizationHeader())
      ),
    ));

    $client = $this->client();
    try {
      $body = $client->execute($request);
      $this->lastResponse = $client->lastResponse;
      $this->setLastResponseCookies();
      unset($_SESSION[self::OBIBA_COOKIE]);
    }
    catch (HttpClientException $e) {
      // ignore
    }
  }

  /**
   * Forwards the 'Set-Cookie' directive to the drupal client.
   */
  private function setLastResponseCookies() {
    foreach ($this->getLastResponseHeader(self::SET_COOKIE_HEADER) as $cookie_str) {
      $cookie = $this->parseCookie($cookie_str);
      $keys = array_keys($cookie);
      $name = $keys[0];
      $value = $cookie[$name];
      watchdog('Agate Client', 'Cookie: name=@name, value=@value',
        array('@name' => $name, '@value' => $value), WATCHDOG_DEBUG);
      if(empty($value)) {
        if (!empty($_SESSION[$name])) unset($_SESSION[$name]);
      } else {
        $_SESSION[$name] = $value;
      }
    }
  }

  /**
   * Explode a cookie string in a array.
   * @param $cookie_str
   * @return array
   */
  private function parseCookie($cookie_str) {
    $cookie = array();
    foreach (explode(';', $cookie_str) as $entry_str) {
      $entry = explode('=', $entry_str);
      $cookie[$entry[0]] = $entry[1];
    }
    return $cookie;
  }

  /**
   * @return HttpClient
   */
  private function client() {
    $client = new HttpClient($formatter = FALSE);

    if (!isset($client->options['curlopts'])) {
      $client->options['curlopts'] = array();
    }

    $client->options['curlopts'] += array(
      CURLOPT_SSL_VERIFYHOST => FALSE,
      CURLOPT_SSL_VERIFYPEER => FALSE
    );

    return $client;
  }

  private function authorizationHeader() {
    return 'Basic ' . base64_encode($this->applicationName . ':' . $this->applicationKey);
  }
}