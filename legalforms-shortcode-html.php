
<div class="row">
    <h1 id="legalforms-name"></h1>
    <div class="alert alert-danger hidden" role="alert" id="email-error">Ongeldige inloggegevens</div>
    <div id="doc-wizard" class="wizard"></div>
    <div id="doc-wizard-login" class="wizard" style="display:none">
        <div class="wizard-step active">
            <h3>Accountgegevens</h3>
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
