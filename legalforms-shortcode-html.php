
<div class="row">
    <h1 id="legalforms-name"></h1>
    <div class="alert alert-danger hidden" role="alert" id="doc-email-error">Ongeldige inloggegevens</div>
    <div id="doc-wizard" class="wizard"></div>
    <div id="doc-wizard-login" class="wizard <?php if ($attrs['material'] !== 'false') { echo 'material'; }?>" style="display:none">
        <div class="wizard-step active">
            <form class="form navmenu-form" novalidate="true">
                <div class="form-group has-error has-danger" data-role="wrapper">
                    <label for="field:account.name">
                        Naam
                        <span class="required">*</span>
                    </label>
                    <input type="text" class="form-control" name="account.name" required="" id="field:account.name" value="">
                </div>
                <div class="form-group" data-role="wrapper">
                    <label for="field:account.email">
                        E-mailadres
                        <span class="required">*</span>
                    </label>
                    <input type="email" class="form-control" name="account.email" required="" id="field:account.email" value="">
                </div>
                <div class="form-group" data-role="wrapper">
                    <label for="field:account.password">
                        Wachtwoord
                        <span class="required">*</span>
                    </label>
                    <input type="password" class="form-control" name="account.password" required="" id="field:account.password" value="">
                </div>
            </form>
            <div class="wizards-actions">
              <button class="btn btn-success btn-rounded btn-outline pull-right" data-target="#doc-wizard-login" data-toggle="wizard" data-step="login">
                  Login
              </button>
          </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" id="doc-saved-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Opgeslagen invoer gevonden</h4>
      </div>
      <div class="modal-body">
        <p>Verder gaan met opgeslagen invoer?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal" id="doc-saved-discard">Begin opnieuw</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal" id="doc-saved-continue">Ga door</button>
      </div>
    </div><
  </div>
</div>
