var mason_settings = {
    original: false,
    bind_events: function() {
        /*
        if(mason_settings.original == false)
        {
            mason_settings.original = $('.ce_settings_mason').find(':input').serializeArray();
        }
        
        $('.pageContents form').unbind('submit').submit(mason_settings.submit_handler);
        */
        var update_field_label = function() {
            $(this).closest('tr').next().find('.field_name').val(mason_settings.make_name($(this).val()));
        }
        $('input.field_title').unbind('keydown').unbind('change').keydown(update_field_label).keyup(update_field_label).change(update_field_label);


        $('.ce_settings_mason').disableSelection().sortable({
			handle: '.mason_block_handle',
			helper: function(e, ui) {			
				return ui;
			},
			axis: "y",
			items: '.mason_block_element',
			tolerance: 'pointer',
			opacity: 0,
			cursor: 'move',
			forcePlaceholderSizeType: true,
			stop: function(e, ui) {
				//restore all ck_editors data in the tile	
				$('.ce_settings_wrapper').removeAttr('style');
			}
		}); 
		
		$('.mason_button_remove').unbind('click').click(function(event) {
		    $(this).closest('.mason_block_element').remove();
		    event.preventDefault();
		    return false;
		});
		
    },
    make_name: function(s) {
        var r = s.toLowerCase();
        r = r.replace(' ', '_');
        r = r.replace(/['".,`!?]+/g, '');
        r = r.replace(/[^a-zA-Z0-9]+/g, '_');
        return r;
    }
    
    /*
    ,
    hashDiff: function(h1, h2) {
        var result = {};
        for (k in h2) {
            if (h1[k] !== h2[k] || k.indexOf('eid') > 0) result[k] = h2[k];
        }
        return result;
    },
    arrayToHash: function(a) {
        var result = {}; 
        for (var i = 0; i < a.length; i++) { 
            result[a[i].name] = a[i].value;
        }
        return result;
    },
    submit_handler: function(event) {
        var current = $('.ce_settings_mason').find(':input:not([type=hidden])').serializeArray();
        //$('.ce_settings_mason').find(':input').remove();
        
        var delta = mason_settings.hashDiff(
                        mason_settings.arrayToHash(mason_settings.original),
                        mason_settings.arrayToHash(current));
        
        $('.ce_settings_mason').find(':input:not([type=hidden])').each(function(i, element) {
            var name = $(element).attr('name');
            if(name.indexOf('field_type') > 0) continue;
            if(delta[name] == undefined)
            {
                $(element).remove();
            }
        });
        
        
        //settings_value = $('<input type="text" name="mason_settings" />');
        //settings_value.val(JSON.stringify(delta));
        //$('.pageContents form').append(settings_value);
        
        
        
        
        //event.preventDefault();
        //return false
        
    }
    */
};

$(".content_element_add").click(function() {
    setTimeout('mason_settings.bind_events();', 500);
});

$(function() {
    mason_settings.bind_events();
});