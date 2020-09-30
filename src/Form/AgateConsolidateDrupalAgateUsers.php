<?php


namespace Drupal\obiba_agate\Form;

use Drupal\obiba_agate\ObibaAgate;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class AgateConsolidateDrupalAgateUsers extends ConfigFormBase {

    protected function getEditableConfigNames() {
        return [
            ObibaAgate::AGATE_SERVER_SETTINGS,
        ];
    }

    public function getFormId() {
        return ObibaAgate::AGATE_DRUPAL_USERS_CONSOLIDATION;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        // Login page.
        $form['consolidation'] = [
            '#type' => 'details',
            '#title' => $this->t('OBiBa Agate Users Consolidation'),
            '#description' => $this->t('This action should be done once after Migration Drupal7 Web site to Drupal 8, and it consist of making migrated users as Agate users if already exist in Agate server. This action require administrator Agate Server credentials'),
            '#open' => TRUE,
        ];
        $form['consolidation']['agate-login'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Agate login'),
            '#description' => $this->t('Please provide the agate administrator login'),
            '#required' => FALSE,
            '#size' => 40,
            '#maxlength' => 40,
            '#attributes' => array(
                'placeholder' => $this->t('administrator')
            )
        );
        $form['consolidation']['agate-password'] = array(
            '#type' => 'password',
            '#title' => $this->t('Agate password'),
            '#description' => $this->t('Please provide the agate administrator password'),
            '#required' => FALSE,
            '#size' => 40,
            '#maxlength' => 40,
            '#attributes' => array(
                'placeholder' => $this->t('Administrator password')
            ),
        );

        $form['consolidation']['consolidate_users'] = [
            '#type' => 'submit',
            '#value' => $this->t('Consolidate Users'),
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        // Generate Users uid to pass to the batch operations
        $uids = \Drupal::entityQuery('user')
            ->execute();
        $array = array_values($uids);
        $credentials = [
            'agate-login' => $form_state->getValue('agate-login'),
            'agate-password' => base64_encode($form_state->getValue('agate-password')),
        ];
        // create the batch operations
        $batch = [
            'title' => t('Check for Agate USers'),
            'operations' => [
                [
                    '\Drupal\obiba_agate\Controller\AgateUserConsolidation::consolidateUser', [$array, $credentials]
                ]
            ],
            'finished' => '\Drupal\obiba_agate\Controller\AgateUserConsolidation::consolidateUserFinishedCallback',
        ];

        batch_set($batch);
    }
}