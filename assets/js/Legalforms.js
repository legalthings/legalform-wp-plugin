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
        locale: 'en',
        resolveInstanceMembers: false //prevent autocreation of `data` value, containing all ractive values
    });

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
        var values = ractive.get();
        delete values.meta;
        delete values.$;
        delete values.today;
        delete values.vandaag;
        console.log(values);

        $.ajax({
            url: legalforms.ajaxurl,
            type: 'POST',
            data: {
                action: 'legalforms_apply_form',
                form_referense: legalforms.id,  
                response_url: legalforms.response_url,
                data: {
                    values: values,
                    step: 'finished'
                }
            }
        }).done(function(data) {
            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            } else if (legalforms.redirect_page) {
                window.location.href = data.redirect_page;
            }
        }).fail(function(data) {
            
        });
    });
})(jQuery);
    