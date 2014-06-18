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
  function hasTicket() {
    return isset($_SESSION[self::OBIBA_COOKIE]);
  }

  /**
   * AgateClient constructor: if no parameters are provided, default are extracted from Drupal variables.
   * @param $agate_url
   * @param $application_name
   * @param $application_key
   */
  public function __construct($agate_url = null, $application_name = null, $application_key = null) {
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
    if ($this->lastResponse != null) {
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
   * Perform authentication by user name and forwards any cookie from Agate to the drupal client.
   * @param $username
   * @param $password
   * @return bool
   * @throws Exception
   */
  public function authenticate($username, $password) {
    $this->lastResponse = null;
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
    } catch (HttpClientException $e) {
      return FALSE;
    }
  }

  /**
   * Get the user name matching the Agate token.
   * @param $token
   * @return mixed|null
   */
  public function validate($token) {
    $this->lastResponse = null;
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
    } catch (HttpClientException $e) {
      return null;
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
    $this->lastResponse = null;
    $url = $this->agateUrl . '/ticket/' . $_SESSION[self::OBIBA_COOKIE] . '/subject';
    $request = new HttpClientRequest($url, array(
      'method' => HttpClientRequest::METHOD_GET,
      'headers' => array('Accept' => array('application/json'), 'X-App-Auth' => array($this->authorizationHeader())),
    ));

    $client = $this->client();
    try {
      $body = $client->execute($request);
      $this->lastResponse = $client->lastResponse;
      return json_decode($body, TRUE);
    } catch (HttpClientException $e) {
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
      'headers' => array('Accept' => array('application/json'), 'X-App-Auth' => array($this->authorizationHeader())),
    ));

    $client = $this->client();
    try {
      $body = $client->execute($request);
      $this->lastResponse = $client->lastResponse;
      $this->setLastResponseCookies();
      unset($_SESSION[self::OBIBA_COOKIE]);
    } catch (HttpClientException $e) {
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
      $expire = isset($cookie['Max-Age']) ? time() + intval($cookie['Max-Age']) : 0;
      $path = isset($cookie['Path']) ? $cookie['Path'] : '/';
      $domain = isset($cookie['Domain']) ? $cookie['Domain'] : null;

      setcookie($name, $value, $expire, $path, $domain);
      $_SESSION[$name] = $value;
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
      CURLOPT_SSLVERSION => 3,
      CURLOPT_SSL_VERIFYHOST => FALSE,
      CURLOPT_SSL_VERIFYPEER => FALSE
    );

    return $client;
  }

  private function authorizationHeader() {
    return 'Basic ' . base64_encode($this->applicationName . ':' . $this->applicationKey);
  }
}