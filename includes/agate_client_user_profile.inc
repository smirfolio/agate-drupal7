<?php

/**
 * @file
 * UserProfile class
 */

/**
 * Class UserProfile
 */
class UserProfile extends AgateClient {


  /**
   * UserProfile constructor
   */
  public function __construct() {
    parent::__construct();
  }

  /**
   * get user profile details
   */
  public function get_user_profile() {
    $this->lastResponse = NULL;
    $url = $this->agateUrl . '/ticket/' . $_SESSION[self::OBIBA_COOKIE] . '/profile';
    $request = new HttpClientRequest($url, array(
      'method' => HttpClientRequest::METHOD_GET,
      'headers' => array(
        'Accept' => array('application/json'),
        AgateClient::AUTHORIZATION_HEADER_AGATE => array($this->authorizationHeader())
      )
    ));
    $client = $this->client();
    try {
      $body = $client->execute($request);
      $this->lastResponse = $client->lastResponse;
      return json_decode($body);
    }
    catch (HttpClientException $e) {
      dpm('error_profile ' . $e->getMessage());
      watchdog('Agate Client', 'Request failed, @code: @message',
        array('@code' => $e->getCode(), '@message' => $e->getMessage()), WATCHDOG_WARNING);
      return array();
    }

  }

  public function update_user_profile($user_data) {
    $this->lastResponse = NULL;
    $url = $this->agateUrl . '/ticket/' . $_SESSION[self::OBIBA_COOKIE] . '/profile';
    $request = new HttpClientRequest($url, array(
      'method' => HttpClientRequest::METHOD_PUT,
      'headers' => array(
        'Accept' => array(parent::HEADER_JSON),
        'Content-Type' => array(parent::HEADER_JSON),
        AgateClient::AUTHORIZATION_HEADER_AGATE => array($this->authorizationHeader())
      ),
      'data' => $user_data

    ));
    $client = $this->client();
    try {
      $client->execute($request);
      $this->lastResponse = $client->lastResponse;
      return TRUE;
    }
    catch (HttpClientException $e) {
      watchdog('Agate Client', 'Request failed, @code: @message',
        array('@code' => $e->getCode(), '@message' => $e->getMessage()), WATCHDOG_WARNING);
      return array('errorServer' => t('Server error : ') . $e->getMessage());
    }
  }

  public function update_user_password($login, $current_password, $new_password) {
    $this->lastResponse = NULL;
    $url = $this->agateUrl . '/user/_current/password';
    $request = new HttpClientRequest($url, array(
      'method' => HttpClientRequest::METHOD_PUT,
      'headers' => array(
        'Content-Type' => array('application/x-www-form-urlencoded'),
        'Accept' => array('application/json'),
        AgateClient::AUTHORIZATION_HEADER => array('Basic ' . base64_encode($login . ':' . $current_password))
      ),
      'data' => 'password=' . $new_password
    ));

    $client = $this->client();
    try {
      $client->execute($request);
      $this->lastResponse = $client->lastResponse;
      return TRUE;
    }
    catch (HttpClientException $e) {
      $errorMessage = NULL;
      $error_response = $e->getResponse();
      if ($e->getCode() != 403) {
        $errorMessage = t(drupal_json_decode($error_response->body)['message']);
      }
      else {
        $errorMessage = t('Forbidden');
      }

      watchdog('Agate Client update password', 'Request failed, @code: @message',
        array('@code' => $e->getCode(), '@message' => $errorMessage), WATCHDOG_WARNING);
      return array('errorServer' => $errorMessage);
    }

  }

}