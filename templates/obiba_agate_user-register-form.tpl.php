<?php
// dpm($variables);
?>
<script src="https://www.google.com/recaptcha/api.js?onload=vcRecaptchaApiLoaded&render=explicit" async defer></script>

<div class="row">
  <div class="col-md-6">
    <div class="obiba-bootstrap-user-register-form-wrapper">

      <div ng-app="ObibaAgate" ng-controller="RegisterFormController">
        <alert ng-if="alert.message" type="{{alert.type}}" close="closeAlert($index)">{{alert.message}}</alert>

        <form id="obiba-user-register" name="theForm" ng-submit="submit(form)">
          <div sf-schema="schema" sf-form="form" sf-model="model"></div>

          <div vc-recaptcha
               theme="'light'"
               key="config.key"
               on-create="setWidgetId(widgetId)"
               on-success="setResponse(response)"></div>

          <div class="md-top-margin">
            <button type="submit" class="btn btn-primary" ng-click="onSubmit(theForm)">
              <span translate><?php print t('Join') ?></span>
            </button>

            <a href="#/" type="button" class="btn btn-default" ng-click="onCancel(theForm)">
              <span translate><?php print t('Cancel') ?></span>
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


