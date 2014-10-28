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
            event.preventDefault();
            mason_settings.delete_mason_subelement($(this).closest('.mason_block_element'));
            return false;
        });
        
        var dirty_flag = function() {
            var $this = $(this);
            
            // Save the original value for the field - only do this if this is the first time
            // the user interacts with this field
            if(!$this.attr('data-old-val')) {
                $this.attr('data-old-val', $this.val());
            }

            // Create an id for the dirty flag
            //console.log($this.attr('name'));
            var hash_pattern = /^content_element\[mason\]\[(.*?)\]/;
            var match = hash_pattern.exec($this.attr('name'));
            //console.log(match);
            if(!match) return;
            var hash_key = match[1];
            
            // ID format is field_dirty__array_array_array, for instance:
            // field_dirty__field_settings_2_height
            // The double underscore comes from the fact that we include an extra one in the prefix here, and the fact that elements are all arrays
            var id = 'field_dirty_'+$this.attr('name').replace('content_element[mason]['+hash_key+']', '').replace(/\[/g, '_').replace(/\]/g, '');
            //console.log(id);
            
            // Get the flag (if it exists already)
            var $dirty = $('#'+id);
            
            // If the saved original value is not the current value, set the dirty flag to true
            if($this.attr('data-old-val') != $this.val()) {
                // Set a visual indication
                //$this.css('border', '1px solid red');
                
                // If there is no dirty flag element yet, make it with it's value set to true
                if($dirty.length == 0) {
                    // We want the name attribute to end up like this, for the given source element:
                    //  content_element[mason][kUXM4SJbyIJoTd6L][field_settings][2][height]    ->    content_element[mason][kUXM4SJbyIJoTd6L][field_dirty][field_settings_2_height]
                    $this.after($('<input type="hidden" name="content_element[mason]['+hash_key+']['+id.replace('__', '][')+']" id="'+id+'" value="1" />'));
                } else {
                    // Set existing flag to true
                    $dirty.val('1');
                }
            } else {
                // If the flag was created before, set it to false now
                if($dirty.length > 0) {
                    $dirty.val('0');
                }
            }
            //console.log($dirty);
        }
        
        // Bind dirty_flag check to input elements, and turn off autocomplete so that the dirty flag isn't different from displayed values
        // when pressing browser back button
        $('.mason_block_element :input').keydown(dirty_flag).keyup(dirty_flag).change(dirty_flag).attr('autocomplete', 'off');
        $('.mason_block_element select').mousedown(dirty_flag).each(dirty_flag).attr('autocomplete', 'off');

        
    },
    make_name: function(s) {
        var r = s.toLowerCase();
        r = r.replace(' ', '_');
        r = r.replace(/['".,`!?]+/g, '');
        r = r.replace(/[^a-zA-Z0-9]+/g, '_');
        return r;
    },
    delete_mason_subelement: function(element) {
        // Mark subelement for deletion when the mason element is saved
        element.find('input.mason_command').val('delete');
        // element.find('input').attr("disabled", "disabled");
        element.hide(500);
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

// $('div.pageContents > form').submit(function() { 
//     $(this).ajaxSubmit();
//     return false;
// }); 


////////////////////////////////////////////////////////////////////////////////
var options = { 
        target:        'div.pageContents > form',   // target element(s) to be updated with server response 
        beforeSubmit:  showRequest,  // pre-submit callback 
        success:       showResponse  // post-submit callback 
 
        // other available options: 
        //url:       url         // override for form's 'action' attribute 
        //type:      type        // 'get' or 'post', override for form's 'method' attribute 
        //dataType:  null        // 'xml', 'script', or 'json' (expected server response type) 
        //clearForm: true        // clear all form fields after successful submit 
        //resetForm: true        // reset the form after successful submit 
 
        // $.ajax options can be used here too, for example: 
        //timeout:   3000 
    }; 

// $('.mason_add_subelement').click(function(event) {
//     event.preventDefault();
//     $('.mason_block_element.last').prev().clone().insertBefore('.mason_block_element.last');
//     $('div.pageContents > form').ajaxSubmit(options);

//     return false;
// });
// pre-submit callback 
function showRequest(formData, jqForm, options) { 
    // formData is an array; here we use $.param to convert it to a string to display it 
    // but the form plugin does this for you automatically when it submits the data 
    var queryString = $.param(formData); 
 
    // jqForm is a jQuery object encapsulating the form element.  To access the 
    // DOM element for the form do this: 
    // var formElement = jqForm[0]; 
 
    alert('About to submit: \n\n' + queryString); 
 
    // here we could return false to prevent the form from being submitted; 
    // returning anything other than false will allow the form submit to continue 
    return true; 
} 
 
// post-submit callback 
function showResponse(responseText, statusText, xhr, $form)  { 
    // for normal html responses, the first argument to the success callback 
    // is the XMLHttpRequest object's responseText property 
 
    // if the ajaxSubmit method was passed an Options Object with the dataType 
    // property set to 'xml' then the first argument to the success callback 
    // is the XMLHttpRequest object's responseXML property 
 
    // if the ajaxSubmit method was passed an Options Object with the dataType 
    // property set to 'json' then the first argument to the success callback 
    // is the json data object returned by the server 
 
    alert('status: ' + statusText + '\n\nresponseText: \n' + responseText + 
        '\n\nThe output div should have already been updated with the responseText.'); 
} 


////////////////////////////////////////////////////////////////////////////////
$(".content_element_add").click(function() {
    setTimeout('mason_settings.bind_events();', 500);
});

$(document).ready(function() {
    mason_settings.bind_events();
});