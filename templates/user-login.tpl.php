<?php
/**
 * @file
 * Code for login block.
 */

?>

<p><?php print render($intro_text); ?></p>

<div class="row">
  <div class="col-md-6"  <?php !empty($providers) ? print("style=\"border-right: #2B81AF solid 2px\"") : ''?> >
    <?php print drupal_render_children($form) ?>
    <div class="md-top-margin">
      <?php print l(t('Forgot your password?'), 'user/password') ?>
      <div>
        <?php if (empty(variable_get_value('access_signup_button_disabled')) || !variable_get_value('access_signup_button_disabled')): ?>
        <?php $register_url = (module_exists('obiba_agate') ? 'agate' : 'user') . '/register/';
        $option_sign_up = [];
          if (module_exists('obiba_agate')) {
            $option_sign_up = array('fragment' => 'join');
          }
        print l(t('Not a member? Join now'), $register_url, $option_sign_up) ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php if (!empty($providers)): ?>
    <div class="col-md-6">
            <?php foreach ($providers as $provider_key=> $provider): ?>
        <div class="md-top-margin" >
              <?php print l(t($provider['title']), variable_get_value('agate_url') . '/auth/signin/' . $provider['name'] . '?redirect=http://localhost/drupal/' . $_GET['destination'], array(
                'attributes' => array('class' => 'btn btn-default')
              )); ?>
        </div>
            <?php endforeach;?>
    </div>
  <?php endif; ?>
</div>
