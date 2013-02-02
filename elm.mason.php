<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @package Mason
 * @author Isaac Raway (MetaSushi, LLC) <isaac.raway@gmail.com>
 *
 * Copyright (c)2009, 2010, 2011. Isaac Raway and MetaSushi, LLC.
 * All rights reserved.
 *
 * This source is commercial software. Use of this software requires a
 * site license for each domain it is used on. Use of this software or any
 * of its source code without express written permission in the form of
 * a purchased commercial or other license is prohibited.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE.
 *
 * As part of the license agreement for this software, all modifications
 * to this source must be submitted to the original author for review and
 * possible inclusion in future releases. No compensation will be provided
 * for patches, although where possible we will attribute each contribution
 * in file revision notes. Submitting such modifications constitutes
 * assignment of copyright to the original author (Isaac Raway and
 * MetaSushi, LLC) for such modifications. If you do not wish to assign
 * copyright to the original author, your license to  use and modify this
 * source is null and void. Use of this software constitutes your agreement
 * to this clause.
 *
 **/


class Mason_element { 

    var $info = array(
        'name'	=> 'Mason',
        'version'	=> '1.0.0'
    );
    
    var $settings = array();
    var $cache    = array();
    var $elements = array();
    
    public function __construct()
    {
        $this->EE = &get_instance();
        $this->info["name"] = $this->EE->lang->line('mason_element_name');
        $this->CE = &$this->EE->api_channel_fields->field_types['content_elements'];
    }
    
    function validate_element($data)
    {
        /*
        // Strip data from the post for sub-elements
        if(isset($_POST['mason']['sub_elements']))
        {
            foreach($_POST['mason']['sub_elements'] as $sub_element_hash => $sub_element_type)
            {
                foreach($_POST as $field => $array)
                {
                    if(is_array($array) && isset($array[$sub_element_hash]))
                    {
                        unset($_POST[$field][$sub_element_hash]);
                    }
                }
                
            }
        }
        */
    }
    
    function save_element($data)
    {
        /* Loop through configured elements, saving each one and packing the data into a single array */
        
        $save_data = array(
            'element_data' => array(),
        );
        //var_dump($_POST);
        //var_dump($data);
        
        
        $mason_id = $data;
        
        if($mason_id && isset($this->settings['mason_elements']))
        {
            // Find our sub-element data
            $sub_element_data = array();
            foreach($_POST as $field => $array)
            {
                if(is_array($array) && isset($array[$mason_id]))
                {
                    // This array should contain all of our sub-element data,
                    // we need to figure out which of these are sub-elements and
                    // which are normal elements in the same CE field.
                    foreach($array as $element_id => $element)
                    {
                        if(is_array($element) && isset($element['mason_id']) && $element['mason_id'] == $mason_id)
                        {
                            
                            $settings = unserialize(base64_decode($element['element_settings']));
                            $sub_element_data[$settings['name']] = $element;
                            
                            unset($_POST[$field][$element_id]);
                        }
                    }
                    break;
                }
            }
            
            foreach($this->settings['mason_elements'] as $element_config)
            {
                $element_name = $element_config['name'];
                $element_type = $element_config['type'];
                if(method_exists($this->EE->elements->$element_type->handler, 'save_element'))
                {
                    $save_data['element_data'][$element_name] = $this->EE->elements->$element_type->handler->save_element($sub_element_data[$element_name]);
                }
            }
        }
        
        return serialize($save_data);
    }
    
    function display_element($data)
    {
        /* Loop through configured elements, unpacking each one's data and it for the backend form */
        
        $result = '<div class="mason_container">';
        
        //$result .= form_hidden('__element_name__[__index__][settings]', base64_encode(serialize($this->settings)));
        $result .= <<<END
                <input type="hidden" name="__element_name__[__index__][data]" value="__hash_key__" />
            <script>
                
                ContentElements.bind('mason', 'display', function(data) {
                    // Create new hashes for each subelement
                    var hash_pattern = /(.*)\[(.*)\]\[.*\]/;
                    var match = hash_pattern.exec(data.find('input[type=hidden][value=mason]').attr('name'));
                    var field_name = match[1];
                    var hash_key = match[2];
                    if(!field_name || !hash_key) return;
                    
                    data.html(data.html().replace('__hash_key__', hash_key));
                    
                    data.find('.mason_field').each(function(i, element) {
                        var new_hash_key = ContentElements.randomString();
                        var \$this = $(this);
                        var sub_type = \$this.attr('data-element-type');
                        
                        var html =   '<input type="hidden" name="mason[__hash_key__][sub_elements]['+new_hash_key+']" value="'+sub_type+'">'
                                   + '<input type="hidden" name="'+field_name+'['+new_hash_key+'][element_type]" value="'+sub_type+'">'
                                   + '<input type="hidden" name="'+field_name+'['+new_hash_key+'][mason_id]" value="__hash_key__">'
                                   + \$this.html();
                        
                        html = html.replace(new RegExp(hash_key, 'g'), new_hash_key);
                        // Need to associate some things back to the main mason field hash key
                        html = html.replace(new RegExp('__hash_key__', 'g'), hash_key);
                        \$this.html(html);
                        
                        console.log(\$this.html());
                        
                        // Dispatch event to subelements
                        ContentElements.callback('display', sub_type, \$this);
                    });
                });
            </script>
END;
        
        $load_data = unserialize($data);
        //var_dump($this->settings);
        if(isset($this->settings['mason_elements']))
        {
            $i = 0;
            foreach($this->settings['mason_elements'] as $element_config)
            {
                $i++;
                $element_name = $element_config['name'];
                $element_type = $element_config['type'];
                if(method_exists($this->EE->elements->$element_type->handler, 'display_element'))
                {
                    $element_result = $this->EE->elements->$element_type->handler->display_element($load_data['element_data'][$element_name]);
                    
                    if(preg_match_all('/name="([^"]*)"/', $element_result, $matches))
                    {
                        foreach($matches[1] as $match_i => $input_name)
                        {
                            //echo $input_name.'<br/>';
                            //$element_result = str_replace('name="'.$input_name.'"', 'name="mason_data['.$i.']['.$input_name.']"', $element_result);
                        }
                        
                    }
                    
                    if($i > 1) $result .= '<br/><br/>';
                    $result .= '<div class="mason_field" data-element-type="'.$element_type.'">';
                    //var_dump($element_config);
                    $result .= form_hidden('__element_name__[__index__][element_settings]', base64_encode(serialize($element_config)));
                    $result .= '<b>'.$element_config['title'].'</b><br/>';
                    $result .= $element_result;
                    $result .= '</div>';
                }
            }
        }
        
        $result .= '</div>';
        
        return $result;
    }
    
    function replace_element_tag($data, $params = array(), $tagdata)
    {
        /* Loop through configured elements, replacing each one for the frontend */
        
        $result = $tagdata;
        
        if(isset($this->settings['mason_elements']))
        {
            foreach($this->settings['mason_elements'] as $element_config)
            {
                $element_type = $element_config['type'];
                if(method_exists($this->EE->elements->$element_type->handler, 'replace_element_tag'))
                {
                    $result = $this->EE->elements->$element_type->handler->replace_element_tag($data, $params, $result);
                }
            }
        }
        
        return $result;
    }
    
    function display_element_settings($data)
    {
        /* Display backend settings to configured elements that make up this block */
        
        // Get a list of installed elements
        $content_elements = $this->EE->elements->fetch_avaiable_elements();
        $element_options = array();
        foreach($content_elements as $element_name)
        {
            if($element_name != 'mason')
            {
                $element_options[$element_name] = $this->EE->lang->line($element_name.'_element_name');
            }
        }
        
        $settings = array();
        
        // Load settings for each of the configured elements
        $i = 0;
        //var_dump($data);exit;
        if(isset($data['mason_elements']))
        {
            foreach($data['mason_elements'] as $element_config)
            {
                $i++;
                $settings[] = array(
                        lang('mason_title') . ' ' . $i,
                        form_input('field_title]['.$i, $element_config['title']),
                    );
                $settings[] = array(
                        lang('mason_name') . ' ' . $i,
                        form_input('field_name]['.$i, $element_config['name']),
                    );
                $settings[] = array(
                        lang('mason_type'),
                        form_dropdown('field_type]['.$i, $element_options, $element_config['type']),
                    );
                
                if(!isset($element_config['settings']))
                {
                    $element_config['field_settings'] = array();
                }
                
                // Load settings for this element
                $element_type = $element_config['type'];
                if(method_exists($this->EE->elements->$element_type->handler, 'display_element_settings'))
                {
                    $element_settings = $this->EE->elements->$element_type->handler->display_element_settings($this->_exclude_setting_system_fields($element_config['settings']));
                    if (is_array($element_settings))
                    {
                        //echo '<pre>';
                        foreach($element_settings as $element_setting)
                        {
                            foreach($element_setting as $key => $value)
                            {
                                if(preg_match_all('/name="([^"]*)"/', $value, $matches))
                                {
                                    foreach($matches[1] as $match_i => $input_name)
                                    {
                                        $value = str_replace('name="'.$input_name.'"', 'name="field_settings]['.$i.']['.$input_name.'"', $value);
                                    }
                                    
                                    //echo htmlspecialchars(print_r($matches[1]), true)."\n\n";
                                }
                                $element_setting[$key] = $value;
                            }
                            $settings[] = $element_setting;
                        }
                       
                        //echo htmlspecialchars(print_r($settings, true));
                        //exit;
                    }
                }
                
                $settings[] = array('<hr/>', '<hr/>');
            }
        }
        
        // Index for new subelement
        $i++;
        
        // Add blank settings fields to be used to add a new element
        $settings[] = array(
                lang('mason_title') . ' (New)',
                form_input('field_title]['.$i),
            );
        $settings[] = array(
                lang('mason_name'),
                form_input('field_name]['.$i),
            );
        $settings[] = array(
                lang('mason_type'),
                form_dropdown('field_type]['.$i, $element_options, 'text_field') . ' (Save to see Element options)',
            );
        
        return $settings;
    }
    
    function save_element_settings($data)
    {
        /* Compose parallel element configuration arrays into a single array of arrays */
        
        $data['mason_elements'] = array();
        
        foreach($data['field_name'] as $i => $field_name)
        {
            $field_title = $data['field_title'][$i];
            $field_type = $data['field_type'][$i];
            $field_settings = $data['field_settings'][$i];
            
            if($field_name && $field_type)
            {
                $data['mason_elements'][] = array(
                    'title' => $field_title,
                    'name' => $field_name,
                    'type' => $field_type,
                    'settings' => $field_settings,
                );
            }
        }
        
        $data['settings'] = array(
            'title' => $data['title']
        );
        
        // Remove parallel arrays
        unset($data['field_title']);
        unset($data['field_name']);
        unset($data['field_type']);
        unset($data['field_settings']);
        
        return $data;
    }
    
    function preview_element($data)
    {
        /* Loop through configured elements, previewing each one for the backend */
        
        $result = '';
        
        if(isset($this->settings['mason_elements']))
        {
            foreach($this->settings['mason_elements'] as $element_config)
            {
                $element_type = $element_config['type'];
                if(method_exists($this->EE->elements->$element_type->handler, 'preview_element'))
                {
                    $result .= $this->EE->elements->$element_type->handler->preview_element($data);
                }
            }
        }
        return $result;
    }
    
    
    /* This method is private in Content Elements, so we need a copy */
    private function _display_content_element_settings($element, $settings = array())
    {
        /*------------------------------------
        ======================================
            DISPLAY_ELEMENT_SETTINGS
        ======================================
        -------------------------------------*/
    
        if (method_exists($this->EE->elements->$element->handler, 'display_element_settings'))
        {
            $data = $this->EE->elements->$element->handler->display_element_settings($this->_exclude_setting_system_fields($settings));			
            
            if (is_array($data))
            {
                $data = $this->EE->load->view('layout/settings_table', array("settings" => $data), TRUE);
            }	
        }
        else
        {
            $data = '';
        } 
        
        //replace name="xxx" -> name="content_elements[element][__index__][var_name]"
        
        preg_match_all('/name\s*=\s*["|\']([^"\']*?)["|\']/', $data, $matches);
        
        if (isset($matches[0]) && is_array($matches[0])) foreach ($matches[0] as $k=>$pattern)
        {	
            if (isset($settings["eid"]))
            {
                $replacement 	= str_replace($matches[1][$k],'content_element['.$element.']['.$settings["eid"].']['.$matches[1][$k].']', $matches[0][$k]);
            }
            else
            {
                $replacement 	= str_replace($matches[1][$k],'content_element['.$element.'][__index__]['.$matches[1][$k].']', $matches[0][$k]);
            }
            $data = str_replace($pattern, $replacement, $data);
        }
        
        //display

        $vars = array(
            "title"		=> @$settings['title'],		//element title
            "eid"		=> (@$settings['eid'])?$settings['eid']:'__index__',
            "element"	=> $element,				//element type
            "data"		=> $data,
        );
        
        return $this->EE->load->view('layout/settings_wrapper', $vars, TRUE);
    }
    
    private function _exclude_setting_system_fields($settings)
    {
        if (isset($settings["title"]))
        {
            unset($settings["title"]);
        }
        if (isset($settings["eid"]))
        {
            unset($settings["eid"]);
        }
        return $settings;
    }
    
}
