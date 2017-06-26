(function($) {

    $('#legalforms-name').html(legalforms.name);
    var builder = new LegalForm();

    var template = builder.build(legalforms.definition);
    var options = builder.calc(legalforms.definition);

    var ractive = new RactiveLegalForm({
        el: $('#doc-wizard'),
        template: template,
        validation: new LegalFormValidation(),
        defaults: options.defaults,
        computed: options.computed,
        meta: options.meta,
        locale: 'nl',
        resolveInstanceMembers: false //prevent autocreation of `data` value, containing all ractive values
    });

    var values;

    var helptext = builder.buildHelpText(legalforms.definition);

    new Ractive({
        el: $('#doc-help-'+legalforms.id)[0],
        template: helptext
    });
    if (legalforms.useMaterial === 'true') {
        $('#doc-wizard').toMaterial();
    }

    window.ractive = ractive;

    $(document).on('click', '#doc-wizard button[data-step="done"]', function() {
        ractive.refreshListItems('remove');
        values = ractive.get();
        delete values.$;
        delete values.today;
        delete values.vandaag;
        delete values.meta;
        for (var key in values) {
            if (key.indexOf('\\') > -1) {
                delete values[key];
            }
        }

        $('#doc-wizard').hide();
        $('#doc-wizard-login').show();
    });

    $(document).on('click', '#doc-wizard-login button[data-step="login"]', function() {
        $('#doc-wizard-login ')
        var account = {
            name: $('#doc-wizard-login [name="account.name"]').val(),
            email: $('#doc-wizard-login [name="account.email"]').val(),
            password: $('#doc-wizard-login [name="account.password"]').val(),
        }

        $.ajax({
              url: legalforms.base_url + '/service/iam/users',
              type: 'POST',
              crossDomain: true,
              dataType: 'json',
              contentType: 'application/json',
              data: JSON.stringify(account)
          }).always(function(user) {
              $.ajax({
                  url: legalforms.base_url + '/service/iam/sessions',
                  type: 'POST',
                  crossDomain: true,
                  dataType: 'json',
                  contentType: 'application/json',
                  data: JSON.stringify(account)
              }).done(function(session) {
                  $.ajax({
                      url: legalforms.base_url + '/service/flow/processes',
                      type: 'POST',
                      crossDomain: true,
                      dataType: 'json',
                      contentType: 'application/json',
                      headers: {'X-Session': session.id},
                      data: JSON.stringify({
                        scenario: legalforms.flow,
                        data: {
                            values: values,
                            template: legalforms.template,
                            name: legalforms.template,
                            organization: session.user.employment[0].organization.id
                        }
                      })
                  }).done(function(data) {
                      window.top.location.href = legalforms.base_url + '/processes/' + data.id;
                  });
              }).fail(function(data) {
                  $('#email-error').removeClass('hidden');
              });
          });
    });
})(jQuery);
