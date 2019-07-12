<div class="modal-content">

  <div class="modal-header">
    <button type="button" class="close" aria-hidden="true" ng-click="cancel()">&times;</button>
    <h4 class="modal-title">
      <span translate>{{provider}}</span>
    </h4>
  </div>

  <div class="modal-body">
    <div class="form-group">
      <label translate>login.form.username</label>
      <input type="text" class="form-control" ng-model="username" ng-disabled="cameWithUsername">
    </div>

    <div class="form-group">
      <label translate="login.form.password">Password</label>
      <input type="password" class="form-control" ng-model="password">
    </div>
  </div>

  <div class="modal-footer">
    <button type="button" class="btn btn-default" ng-click="cancel()">
      <span translate>cancel</span>
    </button>

    <button type="button" class="btn btn-primary" ng-click="test()">
      <span translate>realm.test</span>
    </button>
  </div>

</div>
