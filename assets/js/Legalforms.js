(function($) { 
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
})(jQuery);
    