<?php


namespace Drupal\obiba_agate\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\obiba_agate\ObibaAgate;


/**
 * Form Reset / Activation password process
 *
 * Class PasswordProcess
 * @package Drupal\obiba_agate\Form
 */
class PasswordProcess extends FormBase {

    public function getFormId()
    {
    return ObibaAgate::AGATE_USER_PASSWORD_ACTIVATION_FORM;
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['password'] = array(
            '#type' => 'password',
            '#title' => t('Enter your password'),
            '#required' => TRUE,
            '#default_value' => "",
            '#description' => t('Please enter your password'),
            '#size' => 20,
            '#maxlength' => 20,
            '#attributes' => array(
                'id' => 'type-password',
            ),
        );
        $form['repassword'] = array(
            '#type' => 'password',
            '#title' => t('Re-type your password'),
            '#required' => TRUE,
            '#default_value' => "",
            '#description' => t('Please retype your password'),
            '#size' => 20,
            '#maxlength' => 20,
            '#attributes' => array(
                'id' => 'verif-password',
            ),

        );
        $form['confirmed_password'] = array(
            '#type' => 'hidden',
            '#required' => TRUE,
            '#default_value' => "",
            '#attributes' => array(
                'id' => 'password',
            ),
        );
        $form['key'] = array(
            '#type' => 'hidden',
            '#required' => TRUE,
            '#default_value' => "",
            '#attributes' => array(
                'id' => 'key',
            ),
        );
        $form['action'] = array(
            '#type' => 'hidden',
            '#required' => TRUE,
            '#default_value' => "",
            '#attributes' => array(
                'id' => 'action',
            ),
        );
        $form['submit'] = [
            '#type' => 'submit',
            '#value' => 'Send Password',
        ];
        $form['#attached']['library'][] = 'obiba_agate/password-validation';
        return $form;
    }

    /**
     * Ajax callback process Password form
     *
     * @param array $form
     * @param FormStateInterface $form_state
     */
    public function submitForm(array &$form, FormStateInterface $form_state){

        $response = \Drupal::service('obiba_agate.server.agateclient')->sendPassword(
            [
                'password' => $form_state->getValue('confirmed_password'),
                'key' => $form_state->getValue('key')
            ],
            $form_state->getValue('action')
        );
        if($response['code'] == 200){
            $this->messenger()->addStatus($this->t('Password sent', []));
            $form_state->setRedirect('<front>');
        }
        else{
            $this->messenger()->addError($this->t('Server Error Code :%code, %message', [
                '%code' => $response['code'],
                '%message' => $response['message']
            ]));
        }
    }


}