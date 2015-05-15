<?php
//dpm($variables);
?>

<div class="outer">
  <div class="innerdivs">
    <div class="row">
      <div class="col-md-6 text-center "><?php print l('Login', 'user/login') ?></div>
      <div class="col-md-6 text-center bg-primary"><?php print t('Create an account') ?></div>
    </div>

    <div class="obiba-bootstrap-user-register-form-wrapper">

      <div ng-app="ObibaAuth" ng-controller="RegisterFormController">

        <alert ng-if="alert.message" type="{{alert.type}}" close="closeAlert($index)">{{alert.message}}</alert>

        <form id="obiba-user-register" name="theForm" ng-submit="onSubmit(theForm)">
          <div sf-schema="schema" sf-form="form" sf-model="model"></div>
        </form>

      </div>
    </div>
  </div>
</div>