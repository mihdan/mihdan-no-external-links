(function( $ ) {
    'use strict';

    $(function() {

        var masking_type = $('input[name="wp_noexternallinks_masking_type"]'),
            redirect_time = $("input#wp_noexternallinks_redirect_time"),
            mask_links = $('input[name="wp_noexternallinks_mask_links"]'),
            mask_links_inputs = $("div.list-tree input"),
            link_structure = $('input[name="wp_noexternallinks_link_structure"]'),
            link_structure_default = $('input#wp_noexternallinks_link_structure_default'),
            link_structure_custom = $('input#wp_noexternallinks_link_structure_custom'),
            link_separator = $('input[name="wp_noexternallinks_separator"]'),
            link_separator_display = $('.link-separator'),
            link_encoding = $('input[name="wp_noexternallinks_link_encoding"]'),
            link_shortening = $('input[name="wp_noexternallinks_link_shortening"]'),
            enable_logging = $('input#wp_noexternallinks_logging'),
            log_duration = $('input#wp_noexternallinks_log_duration'),
            enable_anonymize_links = $('input#wp_noexternallinks_anonymize_links'),
            anonymous_link_provider = $('input#wp_noexternallinks_anonymous_link_provider'),
            bot_targeting = $('input#wp_noexternallinks_bot_targeting'),
            bots_selector = $('select#wp_noexternallinks_bots_selector');

        masking_type.on("change", function() {
            var masking_type_value = $(this).val();
            "javascript" === masking_type_value ? redirect_time.prop("readonly", !1) : redirect_time.prop("readonly", !0)
        });

        mask_links.on("change", function() {
            var mask_links_value = $(this).val();
            "specific" === mask_links_value ? mask_links_inputs.prop("disabled", !1) : mask_links_inputs.prop("disabled", !0);
            mask_links_inputs.prop("checked", !0);
        });

        link_separator.on("focus", function() {
            link_structure_default.prop("checked", 0);
            link_structure_custom.prop("checked", 1);

            link_shortening.prop("checked", 0);
            link_shortening.first().prop("checked", 1);
        });

        link_separator.on("keyup", function() {
            var separator_val = link_separator.val();
            link_separator_display.text(separator_val);
        });

        link_structure.on("change", function() {
            var link_structure_value = $(this).val();

            if ("custom" === link_structure_value) {
                link_shortening.prop("checked", 0);
                link_shortening.first().prop("checked", 1);
            }
        });

        link_structure_default.on("change", function() {
            link_separator.val('');
            link_separator_display.text('goto');
        });

        link_encoding.on("change", function() {
            var link_encoding_value = $(this).val();

            if ("none" !== link_encoding_value) {
                link_shortening.prop("checked", 0);
                link_shortening.first().prop("checked", 1);
            }
        });

        link_shortening.on("change", function() {
            link_structure_default.prop("checked", 1);
            link_structure_custom.prop("checked", 0);

            link_encoding.prop("checked", 0);
            link_encoding.first().prop("checked", 1);

            link_separator.val('');
            link_separator_display.text('goto');
        });

        enable_logging.on("change", function() {
            enable_logging.is(':checked') ? log_duration.prop("readonly", !1) : log_duration.prop("readonly", !0);
        });

        enable_anonymize_links.on("change", function() {
            enable_anonymize_links.is(':checked') ? anonymous_link_provider.prop("readonly", !1) : anonymous_link_provider.prop("readonly", !0);
        });

        bot_targeting.on("change", function() {
            var bot_targeting_value = $(this).val();
            "specific" === bot_targeting_value ? bots_selector.prop("disabled", !1) : bots_selector.prop("disabled", !0);
        });

    });

})( jQuery );