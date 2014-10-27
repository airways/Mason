function ce_mason_init(data) {
    data.each(function(no, container) {
        var $container = $(container);
        
        // Create new hashes for each subelement
        var hash_pattern = /(.*)\[(.*)\]\[.*\]/;
        //console.log($container.find('input[type=hidden]'));
        var match = hash_pattern.exec($container.find('input[type=hidden][value=mason]').attr('name'));
        if(!match) return;
        var field_name = match[1];
        var hash_key = match[2];
        if(!field_name || !hash_key) return;
        
        $container.html($container.html().replace('__hash_key__', hash_key));
        
        $container.find('.mason_field').each(function(i, element) {
            
            var $this = $(this);
            var sub_type = $this.attr('data-element-type');
            //var eid = $this.attr('data-eid');
            var old_eid = $this.attr('data-eid');
            
            
            //if(eid == '__hash_key__') eid = ContentElements.randomString();
            eid = ContentElements.randomString();
            
            var html =   /*'<input type="hidden" name="mason[__mason_id__][sub_elements]['+eid+']" value="'+sub_type+'">'
                       + '<input type="hidden" name="'+field_name+'['+eid+'][element_type]" value="'+sub_type+'">'
                       + '<input type="hidden" name="'+field_name+'['+eid+'][mason_id]" value="__mason_id__">'
                       + '<input type="hidden" name="'+field_name+'['+eid+'][field_eid]" value="__field_eid__">'
                       + */$this.html();
            
            console.log('replace ' + hash_key + ' with ' + eid);
            html = html.replace(new RegExp(hash_key, 'g'), eid);
            html = html.replace(new RegExp('__eid__', 'g'), eid);
            html = html.replace(new RegExp(old_eid, 'g'), eid);
            // Need to associate some things back to the main mason field hash key
            html = html.replace(new RegExp('__index__', 'g'), hash_key);
            html = html.replace(new RegExp('__hash_key__', 'g'), eid);
            html = html.replace(new RegExp('__mason_id__', 'g'), hash_key);
            html = html.replace(new RegExp('__field_eid__', 'g'), old_eid);
            
            
            $this.html(html);
            
            //console.log($this.html());
            
            // Dispatch event to subelements
            ContentElements.callback('display', sub_type, $this);
        });
    });
}

ContentElements.bind('mason', 'display', function(data) {
    ce_mason_init($(data));
});

$(window).ready(function()
{
    //ce_mason_init($('.mason_container'));
    // Trigger display event on existing element's fields
    $('.mason_field').each(function(i, element) {
        var $this = $(this);
        var sub_type = $this.attr('data-element-type');
        if(sub_type != 'wysiwyg') { // Built in Editor fieldtype does it's own display init
            ContentElements.callback('display', sub_type, $this);
        }
    });
});
