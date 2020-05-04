<?php


namespace Drupal\obiba_agate\Form;


use Drupal\Console\Bootstrap\Drupal;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\obiba_agate\ObibaAgate;

class AgateServerUserFieldsMapping extends ConfigFormBase{
    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() {
        return [
            ObibaAgate::AGATE_SERVER_SETTINGS,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return ObibaAgate::OBIBA_AGATE_FORM_USER_FIELDS_MAPPING_SETTINGS;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $userFields = \Drupal::service('obiba_agate.server.agateclient')->getConfigFormJoin()['schema']['properties'];
        unset($userFields['locale']);
        unset($userFields['username']);
        unset($userFields['email']);
        //unset($userFields['realm']);
        $config = $this->config(ObibaAgate::AGATE_SERVER_SETTINGS);
        $form = parent::buildForm($form, $form_state);
        $form['user_fields'] = [
            '#type' => 'details',
            '#title' => $this->t('The mapping field Drupal/Agate synchronize Drupal users'),
            '#description' => $this->t('Please enter Drupal field machine name to mapped to Agate users field profile'),
            '#open' => TRUE,
        ];
        $form['user_fields']['table-row'] = [
            '#type' => 'table',
            '#header' => [
                '',
                $this->t('Agate Field'),
                $this->t('Drupal Field'),
                $this->t('Enable/Disable To Import'),
            ],
            '#empty' => $this->t('Sorry, There are no items!'),
        ];
  foreach ($userFields as $field => $row){
      // Some table columns containing raw markup.
      $filedConfig = (($field == 'firstname') ? 'firstName' : (($field == 'lastname') ? 'lastName' : $field));
      $form['user_fields']['table-row'][$filedConfig]['agate_profile_field'] = [
          '#type' => 'hidden',
          '#default_value' => $field,
      ];
      $form['user_fields']['table-row'][$filedConfig]['agate_profile_field_markup'] = [
          '#markup' => $row['title'],
          '#default_value' => $config->get(ObibaAgate::CONFIG_PREFIX_USER_FIELDS_MAPPING . '.' . 'agate_profile_field.' . $filedConfig),
      ];
      $form['user_fields']['table-row'][$filedConfig]['drupal_profile_field'] = [
          '#type' => 'textfield',
          '#default_value' => $config->get(ObibaAgate::CONFIG_PREFIX_USER_FIELDS_MAPPING . '.' . 'drupal_profile_field.' . $filedConfig),
      ];
      $form['user_fields']['table-row'][$filedConfig]['enabled_import'] = [
          '#type' => 'checkbox',
          '#default_value' => $config->get(ObibaAgate::CONFIG_PREFIX_USER_FIELDS_MAPPING . '.' . 'enabled_import.' . $filedConfig),
      ];
  }
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $submission = $form_state->getValue('table-row');
            parent::submitForm($form, $form_state);
            $config = $this->config(ObibaAgate::AGATE_SERVER_SETTINGS);
        foreach ($submission as $id => $item) {
            $config->set(ObibaAgate::CONFIG_PREFIX_USER_FIELDS_MAPPING . '.' . 'agate_profile_field.' . $id,
                $submission[$id]['agate_profile_field']);
            $config->set(ObibaAgate::CONFIG_PREFIX_USER_FIELDS_MAPPING . '.' . 'drupal_profile_field.' . $id,
                $submission[$id]['drupal_profile_field']);
            $config->set(ObibaAgate::CONFIG_PREFIX_USER_FIELDS_MAPPING . '.' . 'enabled_import.' . $id,
                $submission[$id]['enabled_import']);
        }
        $config->save();
    }

}