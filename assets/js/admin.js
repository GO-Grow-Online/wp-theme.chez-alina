jQuery(function($) {
    dynamic_title_repeater_accordion('card_rep', 'title');
    
    function dynamic_title_repeater_accordion(repeater_name, field_name) {
        var information_tabs = $("div[data-name='" + repeater_name + "']");
        console.log(information_tabs);
        if (information_tabs.length) {
            var selector = "tr:not(.acf-clone) td.acf-fields .acf-accordion-content div[data-name='" + field_name + "'] input";
    
            // add lister
            $(information_tabs).on('input', selector, function() {
                var me = $(this);
                me.closest('td.acf-fields').find('.acf-accordion-title label').text(me.val());
            });
    
            // trigger the function on load
            information_tabs.find(selector).trigger('input');
    
        }
    }
});

