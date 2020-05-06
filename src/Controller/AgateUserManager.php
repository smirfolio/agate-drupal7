<?php
/**
 * @file
 * Contains \Drupal\obiba_agate\Server\AgateUserManager
 */

namespace Drupal\obiba_agate\Controller;
use Drupal\obiba_agate\ObibaAgate;
use Drupal\obiba_agate\Server\AgateClient;

use Drupal\externalauth\ExternalAuth;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;

use Drupal\user\Entity\Role;

class AgateUserManager extends ControllerBase{
  public $externalAuth;
  protected $user ;
  protected $entityUser;
  public $agateClient;

  public function __construct(AgateClient $agateClient, ExternalAuth $external_auth, EntityTypeManagerInterface $entityTypeManager) {
    $this->entityUser = $entityTypeManager->getStorage('user');
    $this->agateClient = $agateClient;
    $this->externalAuth = $external_auth;
  }

   /**
    * @param null $userToLog
    * @return bool|void
    * @throws \Drupal\Core\Entity\EntityStorageException
    */
    public function agateLogin($userToLog = NULL){
        $user = $userToLog ? $userToLog : $this->agateClient->getSubject();
        if($user){
            $externalUserAccount =  $this->externalAuth->login($user->username, ObibaAgate::AGATE_PROVIDER);
            if($externalUserAccount){
                return $this->updateDrupalExternalUser($externalUserAccount, $user);
            }
        }
        return FALSE;
    }

    /**
     * @return bool|\Drupal\user\UserInterface
     * @throws \Drupal\Core\Entity\EntityStorageException
     */
    public function agateLoginRegister(){
        $user_info = $this->agateClient->getSubject();
        if($user_info){
            $drupalUserAccount =  $this->externalAuth->loginRegister($user_info->username, ObibaAgate::AGATE_PROVIDER, $this->normalizeAgateUserAttributes($user_info));
            $this->updateDrupalRoles($user_info->groups, $drupalUserAccount);
            return $drupalUserAccount;
        }
        return FALSE;
    }

    /**
     * Update Drupal User
     *
     * @param $externalUserAccount
     * @param $AgateUserAccount
     * @throws \Drupal\Core\Entity\EntityStorageException
     */
    private function updateDrupalExternalUser($externalUserAccount, $AgateUserAccount){
        $drupalUser = current($this->entityUser->loadByProperties(['name' => $externalUserAccount->getAccountName()]));
        $this->updateDrupalRoles($AgateUserAccount->groups, $drupalUser);
        $drupalUserToUpdate = $this->normalizeAgateUserAttributes($AgateUserAccount);
        foreach ($drupalUserToUpdate as $field => $valueField){
            if(!in_array($field, ['name', 'mail'])){
                $drupalUser->set($field, $valueField);
            }
        }
         $drupalUser->save();
    }

    /**
     * Normalize the Agate User attribute to save
     *
     * @param $agateUserProfile
     * @return mixed
     */
  private function normalizeAgateUserAttributes($agateUserProfile){
      $config = $this->config(ObibaAgate::AGATE_SERVER_SETTINGS);
      $drupalConfig = $config->get(ObibaAgate::CONFIG_PREFIX_USER_FIELDS_MAPPING . '.' . 'drupal_profile_field');
      $agateConfig = $config->get(ObibaAgate::CONFIG_PREFIX_USER_FIELDS_MAPPING . '.' . 'agate_profile_field');
      $enableImportConfig = $config->get(ObibaAgate::CONFIG_PREFIX_USER_FIELDS_MAPPING . '.' . 'enabled_import');
    $user_info['name'] =  $agateUserProfile->username;
    $user_info['mail'] = $this->getAgateUserAttribute($agateUserProfile->attributes, 'email');
    $user_info['preferred_langcode'] = $this->getAgateUserAttribute($agateUserProfile->attributes, 'locale');
    foreach ($drupalConfig as $field => $drupalField){
        switch ($field){
            case 'firstname':
                if($enableImportConfig[$field]){
                    $user_info[$drupalField] = $this->getAgateUserAttribute($agateUserProfile->attributes, 'firstName');
                }
                break;
            case 'lastname':
                if($enableImportConfig[$field]) {
                    $user_info[$drupalField] = $this->getAgateUserAttribute($agateUserProfile->attributes, 'lastName');
                }
                break;
            default:
                if($enableImportConfig[$field]) {
                    $user_info[$drupalField] = $this->getAgateUserAttribute($agateUserProfile->attributes, $agateConfig[$field]);
                }
        }
    }

    return $user_info;
  }

    /**
     * Update Drupal User roles
     *
     * @param array $groups
     * @param $drupalUserAccount
     * @throws \Drupal\Core\Entity\EntityStorageException
     */
  public function updateDrupalRoles(array $groups, $drupalUserAccount){
      // Revoke drupal user roles
      foreach($drupalUserAccount->getRoles() as $drupalRole){
           if(!in_array($drupalRole, $groups)){
               $drupalUserAccount->removeRole($drupalRole);
           }
      }
      // Update Drupal user Roles
      foreach ($groups as $group){
              if(!Role::load($group)){
                 Role::create(['id' => $group, 'label' => $group])->save();
              }
          $drupalUserAccount->addRole($group);
      }
      $drupalUserAccount->save();
  }

    /**
     * Get User attribute
     *
     * @param $agateUserProfileAttributes
     * @param $attribute
     * @return mixed
     */
  private function getAgateUserAttribute($agateUserProfileAttributes, $attribute){
      return current(array_filter($agateUserProfileAttributes, function ($arrayAttribute) use($attribute){
          return $arrayAttribute->key == $attribute;
      }))->value;
  }

  public function createAgateUser($userEntity){
    return $this->agateClient->createUser($this->normalizeDrupalUserAttributes($userEntity));
  }

    /**
     * @param array $user
     */
  public function updateAgateUser($userEntity){

  }

    /**
     * Normalize the Agate User attribute to save
     *
     * @param $agateUserProfile
     * @return mixed
     */
    private function normalizeDrupalUserAttributes($drupalUserEntity){
        $config = \Drupal::config(ObibaAgate::AGATE_SERVER_SETTINGS);
        $user_field_mapping = $config->get(ObibaAgate::CONFIG_PREFIX_USER_FIELDS_MAPPING);
        $agate_user_profile['username'] = current($drupalUserEntity->name->getValue()[0]);
        $agate_user_profile['email'] = current($drupalUserEntity->mail->getValue()[0]);
        $agate_user_profile['local'] = current($drupalUserEntity->langcode->getValue()[0]);
        $agate_user_profile['g-recaptcha-response'] = $_POST['g-recaptcha-response'];
        foreach ($user_field_mapping[ObibaAgate::AGATE_PROFILE_FIELD] as $field => $agate_field){
            if($user_field_mapping[ObibaAgate::DRUPAL_ENABLED_FILED_IMPORT][$field]){
                $agate_user_profile[$field] = current($drupalUserEntity->{$user_field_mapping[ObibaAgate::DRUPAL_PROFILE_FIELD][$field]}->getValue()[0]);
            }
        }
        return $agate_user_profile;
    }
}