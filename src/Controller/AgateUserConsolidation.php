<?php


namespace Drupal\obiba_agate\Controller;

use Drupal\obiba_agate\Controller\AgateUserManager;

class AgateUserConsolidation {

    /**
     * Consolidate the Drupal users by make it external if exist on Agate
     * @param $uids
     * @param $credentials
     * @param $context
     */
    public static function consolidateUser($uids,$credentials, &$context){
        $agateUserManager = \Drupal::service('obiba_agate.controller.agateusermanager');
        $agateClient = \Drupal::service('obiba_agate.server.agateclient');

        // Override the default basic auth key in the AgateClient Object
        $agateClient->basicAgateAuth($credentials['agate-login'], base64_decode($credentials['agate-password']));

        // Initialize the sandbox context
        if (!isset($context['sandbox']['progress'])) {
            $context['sandbox']['progress'] = 0;
            $context['sandbox']['current_user'] = 0;
            $context['sandbox']['max'] = count($uids);
        }

        // Safely process 5 Users at a time without a timeout.
        $limit = 10;
        $database = \Drupal::database();
        $query  = $database->query("SELECT uid, name, mail, status FROM {users} INNER JOIN {users_field_data} USING (uid) WHERE uid IN (:ids[])",
            [':ids[]' => array_slice($uids, $context['sandbox']['current_user'], $limit)]);
        $resultUsers = $query ->fetchAll();

        foreach ($resultUsers as $key => $user) {
            // Check for external users with Drupal active status and not administrator
            if(!self::isExternalUser($agateUserManager, $user->name) && ($user->status && $user->name != 'administrator')){
                // if not external user check if exist in Agate
                $agateUser = self::isAgateUser($agateClient, $user->mail);
                // Check the found agate user exist and is active
                if(!empty($agateUser) && $agateUser->status == 'ACTIVE'){
                    // If Agate user and not yet external user make it external user
                    \Drupal::service('obiba_agate.controller.agateusermanager')->
                    externalAuth->linkExistingAccount($agateUser->name, 'obiba_agate', user_load_by_name($user->name));
                }
            }

            // Store some result for post-processing in the finished callback.
            $context['results'][] = $user->uid;
            // Update our progress information.
            $context['sandbox']['progress']++;
            $context['sandbox']['current_user']++;
            $context['message'] = t('Now processing @uid', array('@uid' => $user->uid));

        }
        if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
            $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
        }
    }

    public static function consolidateUserFinishedCallback($success, $results, $operations){
        // The 'success' parameter means no fatal PHP errors were detected. All
        // other error management should be handled using 'results'.

        if ($success) {
            // Here we do something meaningful with the results.
            $message = t('@count Users successfully processed:', array('@count' => count($results)));
            $message .=  implode(',', $results);
            \Drupal::messenger()->addMessage($message);
        }
        else {
            // An error occurred.
            // $operations contains the operations that remained unprocessed.
            $error_operation = reset($operations);
            $message = t('An error occurred while processing %error_operation with arguments: @arguments', array('%error_operation' => $error_operation[0], '@arguments' => print_r($error_operation[1], TRUE)));
            \Drupal::messenger()->addError($message);
        }
    }

    private  static function isExternalUser($agateUserManager, $name){
        return $agateUserManager->isExternalUser($name);
    }

    private  static function isAgateUser(object $agateClient, string $email){
        return $agateClient->getUserByEmail($email);
    }

}