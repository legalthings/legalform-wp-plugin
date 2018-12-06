<div id="legalforms-plugin" class="container-fluid container">
    <div class="row" id="doc-form">
        <h1 id="legalforms-name"></h1>
        <div class="progress">
            <div class="progress-bar"></div>
        </div>
        <div id="doc">
            <div id="doc-help">
            </div>
        </div>
        <div class="alert alert-danger hidden d-none" role="alert" id="doc-email-error">Ongeldige inloggegevens</div>
        <div class="alert alert-danger hidden d-none" role="alert" id="doc-email-exists">E-mailadres bestaat al</div>
        <div class="alert alert-danger hidden d-none" role="alert" id="doc-error">Er is iets fout gegaan</div>
        <div class="alert alert-success hidden d-none" role="alert" id="doc-email-send">E-mail verzonden</div>

        <div id="doc-wizard" class="wizard"></div>

        <div id="doc-wizard-actions" class="wizards-actions">
            <button data-target="#doc-wizard" data-toggle="wizard" data-step="prev" class="btn btn-default btn-rounded btn-outline pull-left wizard-hide">Vorige</button>
            <button data-target="#doc-wizard" data-toggle="wizard" data-step="next" class="btn btn-primary btn-rounded btn-outline pull-right wizard-hide">Volgende</button>
            <button data-target="#doc-wizard" data-toggle="wizard" data-step="done" class="btn btn-success btn-rounded btn-outline pull-right wizard-hide">Voltooien</button>
            <button class="btn btn-default btn-rounded btn-outline doc-save pull-right">Bewaar voor later</button>
        </div>

        <div id="doc-wizard-register" class="wizard <?php if ($attrs['material'] !== 'false') { echo 'material'; }?>" style="display:none">
            <div class="wizard-step">
                <h3>Maak gratis account aan</h3>
                <form id="form-register" class="form navmenu-form" novalidate="true" action="javascript:void(0);">
                    <div class="form-group" data-role="wrapper">
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
                    <?php if ($this->config['terms_url']): ?>
                        <div class="checkbox form-check">
                            <label>
                                <input type="checkbox" name="terms" id="legalforms-terms" required="" />
                                Ik ga akkoord met de <a href="<?php echo $this->config['terms_url']; ?>">algemene voorwaarden</a>.
                            </label>
                        </div>
                    <?php endif; ?>
                </form>
                <div class="wizards-actions">
                    <button class="btn btn-default btn-secondary pull-left" data-target="#doc-wizard-register" data-toggle="wizard" data-step="previous">
                        Vorige
                    </button>
                    <button class="btn btn-success btn-raised pull-right" data-target="#doc-wizard-register" data-toggle="wizard" data-step="register">
                        Ga door
                        <div class="loader hidden d-none"></div>
                    </button>
                    <button class="btn btn-info btn-raised btn-outline pull-right" id="switch-login">
                        Ik heb al een account
                    </button>
                </div>
            </div>
        </div>
        <div id="doc-wizard-login" class="wizard <?php if ($attrs['material'] !== 'false') { echo 'material'; }?>" style="display:none">
            <div class="wizard-step">
                <h3>Login</h3>
                <form id="form-login" class="form navmenu-form" novalidate="true" action="javascript:void(0);">
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
                    <button class="btn btn-default btn-secondary pull-left" data-target="#doc-wizard-login" data-toggle="wizard" data-step="previous">
                        Vorige
                    </button>
                    <button class="btn btn-success btn-raised pull-right" data-target="#doc-wizard-login" data-toggle="wizard" data-step="login">
                        Login
                        <div class="loader hidden d-none"></div>
                    </button>
                    <button class="btn btn-info btn-raised btn-outline pull-right" id="switch-forgot">
                        Wachtwoord vergeten
                    </button>
                </div>
            </div>
        </div>
        <div id="doc-wizard-forgot" class="wizard <?php if ($attrs['material'] !== 'false') { echo 'material'; }?>" style="display:none">
            <div class="wizard-step">
                <h3>Wachtwoord vergeten</h3>
                <form id="form-forgot" class="form navmenu-form" novalidate="true" action="javascript:void(0);">
                    <div class="form-group" data-role="wrapper">
                        <label for="field:account.forgot_email">
                            E-mailadres
                            <span class="required">*</span>
                        </label>
                        <input type="email" class="form-control" name="account.email" required="" id="field:account.forgot_email" value="">
                    </div>
                </form>
                <div class="wizards-actions">
                    <button class="btn btn-success btn-raised pull-right" data-target="#doc-wizard-forgot" data-toggle="wizard" data-step="forgot">
                        Verstuur
                        <div class="loader hidden d-none"></div>
                    </button>
                </div>
            </div>
        </div>
        <div id="doc-wizard-email" class="wizard <?php if ($attrs['material'] !== 'false') { echo 'material'; }?>" style="display:none">
            <div class="wizard-step">
                <h3>Geef uw contactgegevens op</h3>
                <form id="form-email" class="form navmenu-form" novalidate="true" action="javascript:void(0);">
                    <div class="form-group" data-role="wrapper">
                        <label for="field:account.user_name">
                            Naam
                            <span class="required">*</span>
                        </label>
                        <input type="text" class="form-control" name="account.user_name" required="" id="field:account.user_name" value="">
                    </div>
                    <div class="form-group" data-role="wrapper">
                        <label for="field:account.user_email">
                            E-mailadres
                            <span class="required">*</span>
                        </label>
                        <input type="email" class="form-control" name="account.user_email" required="" id="field:account.user_email" value="">
                    </div>
                </form>
                <div class="wizards-actions">
                    <button class="btn btn-success btn-raised pull-right" data-target="#doc-wizard-email" data-toggle="wizard" data-step="done">
                        Verstuur
                        <div class="loader hidden d-none"></div>
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
            </div>
        </div>
    </div>
</div>
