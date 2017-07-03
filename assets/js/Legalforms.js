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

    var helptext = builder.buildHelpText(legalforms.definition);

    new Ractive({
        el: $('#doc-help-'+legalforms.id)[0],
        template: helptext
    });
    if (legalforms.material !== 'false') {
        $('#doc-wizard').toMaterial();
    }

    window.ractive = ractive;

    var storedValues = localStorage.getItem('values');
    if (storedValues) {
        storedValues = JSON.parse(storedValues);
        setTimeout(useSaved, 2000);
    }

    function useSaved() {
        $('#doc-saved-modal').modal({
            backdrop: true
        });

        $('#doc-saved-continue').on('click', function() {
            ractive.set(storedValues);

            /* Manually set all selectize fields */
            var els = jQuery('select.selectized');
            for (var i = 0; i < els.length; i++) {
                var id = els.eq(i).attr('id').replace('field:', '');
                var val = storedValues[id.split('.')[0]][id.split('.')[1]];
                els.eq(i).selectize()[0].selectize.setValue(val);
            }
            $('.form-control').each(function () {
                if ($(this).val()) {
                    $(this).parents('.form-group').removeClass('is-empty');
                }
            })
        })

        $('#doc-saved-discard').on('click', function() {
            localStorage.removeItem('values');
        })
    }

    function getValues() {
        ractive.refreshListItems('remove');
        var values = ractive.get();
        delete values.$;
        delete values.today;
        delete values.vandaag;
        delete values.meta;
        for (var key in values) {
            if (key.indexOf('\\') > -1) {
                delete values[key];
            }
        }
        return values;
    }

    $(document).on('click', '#doc-wizard button[data-step="done"]', function() {
        $('#doc-wizard').hide();
        $('#doc-wizard-login').show();
    });

    $(document).on('click', '#doc-wizard-login button[data-step="login"]', function() {
        var values = getValues();
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
                            name: legalforms.name,
                            organization: session.user.employment[0].organization.id
                        }
                      })
                  }).done(function(data) {
                      window.top.location.href = legalforms.base_url + '/processes/' + data.id + '?auto_open=true&hash=' + session.id;
                  });
              }).fail(function(data) {
                  $('#doc-email-error').removeClass('hidden');
              });
        });
    });

    $(document).on('click', '#doc-save', function() {
        var values = getValues();
        localStorage.setItem('values', JSON.stringify(values));
        $('#doc-save-alert').removeClass('hidden');
    });
})(jQuery);
