<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @package Mason
 * @author Isaac Raway (MetaSushi, LLC) <isaac.raway@gmail.com>
 *
 * Copyright (c)2009, 2010, 2011, 2012, 2013. Isaac Raway and MetaSushi, LLC.
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

 **//*--------------------------------------------------------------------------
  Programmer  : Isaac Raway       Date: 31.Jan.2013
  Description : Mason is an element for the Content Elements fieldtype that
                binds together other elements into reusable "content blocks",
                which may be used to create pages by assembling blocks as
                needed.
  Written for : PHP 5.2+, ExpressionEngine 2.5.3+, Content Elements 1.1.0+
  Usage       : Install the Mason element within the Content Elements directory:
                system/expressionengine/third_party/content_elements/elements/mason/
  Called by   : Content Elements
  Calls       : Nothing
 -----------------------------------------------------------------------------*/

class Mason_element { 

    public $info = array(
        'name'    => 'Mason',
        'version'    => '1.0.0'
    );
    
    public $settings = array();
    public $cache    = array();
    public $elements = array();
    
    public function __construct()
    {
        $this->EE = &get_instance();
        $this->info["name"] = $this->EE->lang->line('mason_element_name');
        $this->CE = &$this->EE->api_channel_fields->field_types['content_elements'];
    }
    
    function save_element($data)
    {
        /* Loop through configured elements, saving each one and packing the data into a single array */
        
        $save_data = array(
            'element_data' => array(),
        );

        // Extract field name and mason id from the field name which is expected to be in the format field_name[mason_id][data]
        preg_match('/([^\]]*)\[([^\]]*)\].*/', $this->field_name, $matches);

        $field_name = $matches[1];
        $mason_id = $matches[2];
        
        if($mason_id && isset($this->settings['mason_elements']))
        {
            // var_dump($matches);

            foreach($this->settings['mason_elements'] as $element_config)
            {
                
                $element_name = $element_config['name'];
                $element_type = $element_config['type'];
                $element_eid = $element_config['eid'];
                $element_settings = $element_config['settings'];
                
                //echo 'Try to save:';
                //var_dump($element_eid);
                
                // If a field is left blank, it will not be present in POST data
                if (isset($_POST['mason'][$mason_id]['sub_elements']))
                {
                    foreach($_POST['mason'][$mason_id]['sub_elements'] as $new_eid => $sub_element_data)
                    {
                        // If the subelement data is not an array or eid doesn't match that of the mason element, skip over it
                        if(!is_array($sub_element_data)) continue;
                        if($sub_element_data['field_eid'] != $element_eid) continue;
                        
                        if($element_eid != '__hash_key__' && method_exists($this->EE->elements->$element_type->handler, 'save_element'))
                        {
                            //echo 'save <b>'.$element_eid.'</b><br/>';
                            //var_dump($sub_element_data);
                            $this->EE->elements->$element_type->handler->settings = $element_settings;
                            $save_data['element_data'][$element_eid] = $this->EE->elements->$element_type->handler->save_element($sub_element_data['data']);
                            //var_dump('done');
                        } else {
                            $save_data['element_data'][$element_eid] = $sub_element_data['data'];
                            //var_dump('No save_element method or whatever');
                            //var_dump($save_data['element_data'][$element_eid]);
                        }
                    }
                }
            }
        } else {
            echo 'Missing mason id or no mason_elements defined.<br/>';
        }
        
        $out = base64_encode(serialize($save_data));
        return $out;
    }
    
    function display_element($data)
    {
        /* Loop through configured elements, unpacking each one's data and it for the backend form */

        preg_match('/([^\]]*)\[([^\]]*)\].*/', $this->field_name, $matches);
        $field_name = $matches[1];
        $mason_id = $matches[2];
        
        $this->_load_asset('publish.js');

        $result = '<div class="mason_container">';
        $result .= '<input type="hidden" name="mason_entry_form" value="1" />';        
        $result .= '<input type="hidden" name="'.$field_name.'['.$mason_id.'][data]" value="'.$mason_id.'" />';
        
        $load_data = unserialize(base64_decode($data));
        
        if(isset($this->settings['mason_elements']))
        {
            if(isset($load_data['element_data']) && is_array($load_data['element_data']))
            {
                $load_data['element_meta'] = array();
                
                foreach($load_data['element_data'] as $key => $data)
                {
                    if(!is_array($data) && substr($data, 0, 2) === 'a:')
                    {
                        $load_data['element_data'][$key] = unserialize($data);
                        $load_data['element_meta'][$key.':was_serialized'] = TRUE;
                    } else {
                        // $load_data['element_data'][$key] = $data;  // This line seems redundant
                        $load_data['element_meta'][$key.':was_serialized'] = FALSE;
                    }
                }
            }

            $first_loop = TRUE;
            foreach($this->settings['mason_elements'] as $element_config)
            {
                //var_dump($element_config);
                $element_name = $element_config['name'];
                $element_type = $element_config['type'];
                $element_eid = $element_config['eid'];
                $element_settings = $element_config['settings'];
                
                if(isset($this->EE->elements->$element_type) && method_exists($this->EE->elements->$element_type->handler, 'display_element'))
                {
                    // Replace mason id with element eid in the content element's field name and set the settings
                    if($mason_id)
                    {
                        $this->EE->elements->$element_type->handler->field_name = str_replace($mason_id, $element_eid, $this->field_name);
                    }
                    $this->EE->elements->$element_type->handler->settings = $element_settings;
                    
                    if(@$load_data['element_meta'][$element_eid.':was_serialized'])
                    {
                        $data = serialize(@$load_data['element_data'][$element_eid]);
                    } else {
                        $data = @$load_data['element_data'][$element_eid];
                    }
                    
                    $element_result = $this->EE->elements->$element_type->handler->display_element($data, true);
                    
                    $element_config['element_type'] = $element_config['type'];
                    
                    $element_result = form_hidden($field_name.'['.$element_eid.'][element_settings]', base64_encode(serialize($element_config)))
                        . form_hidden('mason['.$mason_id.'][sub_elements]['.$element_eid.']',  $element_config['type'])
                        . form_hidden($field_name.'['.$element_eid.'][element_type]',  $element_config['type'])
                        . form_hidden($field_name.'['.$element_eid.'][mason_id]',  '__mason_id__')
                        . form_hidden($field_name.'['.$element_eid.'][field_eid]',  '__field_eid__')
                        . $element_result;
                    
                    $element_result = str_replace('__element_name__', $field_name, $element_result);
                    $element_result = str_replace('__index__', $element_eid, $element_result);

                    if(!is_null($load_data['element_data']))
                    {
                        // Existing entry - not a template - make up a temporary ID and replace it into the result
                        $new_eid = $this->random_string();
                        $element_result = str_replace($element_eid, $new_eid, $element_result);
                        
                        $element_result = str_replace('__mason_id__', $mason_id, $element_result);
                        $element_result = str_replace('__field_eid__', $element_eid, $element_result);
                    }

                    // Insert space between subelements
                    $first_loop = $first_loop ? FALSE : !$result .= '<br />'; //<br />';

                    $result .= '<div class="mason_field" data-element-type="'.$element_type.'" data-eid="'.$element_eid.'">';
                    $result .= '<b>'.$element_config['title'].'</b><br />';
                    $result .= $element_result; // the subelement itself preceded by the necessary hidden elements
                    $result .= '</div>'; // for class="mason_field"
                }
            }
        }
        
        $result .= '</div>'; // for class="mason_container"

        return $result;
    }
    
    function replace_element_tag($data, $params = array(), $tagdata)
    {
        // echo '<h3>replace_element_tag for mason element</h3>';
        // echo '<h4>data:</h4>';
        // var_dump($data);
        // echo '<h4>settings:</h4>';
        // var_dump($this->settings);
        // echo '<h4>params:</h4>';
        // var_dump($params);
        // echo '<h4>tagdata:</h4>';
        // var_dump($tagdata);
        // exit;






        /* Loop through configured elements, replacing each one for the frontend */
        
        $load_data = unserialize(base64_decode($data));
        // var_dump($load_data);
        if(isset($load_data['element_data']) && is_array($load_data['element_data']))
        {
            foreach($load_data['element_data'] as $key => $data)
            {
                //if(!is_array($data) && substr($data, 0, 2) === 'a:')
                //{
                //    $load_data['element_data'][$key] = unserialize($data);
                //} else {
                    $load_data['element_data'][$key] = $data;
                //}
            }
        }
        
        $result = '';

        $tagdata = str_replace(LD.'block_name'.RD, $this->element_name, $tagdata);
        
        if(isset($this->settings['mason_elements']))
        {
        
            $row_result = $tagdata;
            
            foreach($this->settings['mason_elements'] as $element_config)
            {
                $element_name = $element_config['name'];
                $element_type = $element_config['type'];
                $element_eid = $element_config['eid'];
                $element_settings = $element_config['settings'];
                
                if(isset($this->EE->elements->$element_type->handler))
                {
                    $this->EE->elements->$element_type->handler->element_name = $element_name;
                    
                    $block = false;
                    $match = false;
                    
                    // If the user is not using a closing tag, they just want the value - turn it into
                    // a pair with {value} inbetween
                    if(($pos = strpos($row_result, LD.'/'.$element_name.RD)) === FALSE)
                    {
                        $row_result = str_replace(LD.$element_name.RD, LD.$element_name.RD.'{value}'.LD.'/'.$element_name.RD, $row_result);
                    }
                    
                    // Find the block for this field
                    if($count = preg_match($pattern = '#'.LD.$element_name.RD.'(.*?)'.LD.'/'.$element_name.RD.'#s', $row_result, $matches))
                    {
                        $match = $matches[0];
                        $block = $matches[1];
                    }
                    
                    // If there is a parsing method, call it - otherwise just set our result to the text data value
                    if(method_exists($this->EE->elements->$element_type->handler, 'replace_element_tag'))
                    {
                        // var_dump($load_data['element_data']);
                        // var_dump($element_eid);
                        $parse_result = $this->EE->elements->$element_type->handler->replace_element_tag($load_data['element_data'][$element_eid], $params, $block);
                    } else {
                        $parse_result = $load_data['element_data'][$element_eid];
                    }
                    
                    // Replace the entire matched block including the tag pair with the parse results
                    $row_result = str_replace($match, $parse_result, $row_result);
                }
                
            }
            $result .= $row_result;
        }
        
        return $result;
    }
    
    function display_element_settings($data)
    {
        /* Display backend settings to configured elements that make up this block */

        $_SESSION['mason_old_settings_'.$this->EE->input->get_post('field_id')] = $data;
        
        $this->_load_asset('settings.js');
        $this->_load_asset('jquery.form.js');
        $this->_load_asset('screen.css');
        
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
        
        $label_width = 'width="150"';
            
        // Load settings for each of the configured elements
        $i = 0;
        
        if(isset($data['mason_elements']))
        {
            foreach($data['mason_elements'] as $element_config)
            {
                //var_dump($element_config);
                $settings_block = array();
                $i++;
                $settings_block[] = array(
                        $label_width => lang('mason_title'), // . ' ' . $i,
                        form_hidden('field_eid]['.$i, $element_config['settings']['eid']) .
                        pl_form_hidden('field_order]['.$i, $i, false, 'mason_element_order') .
                        pl_form_hidden('field_command]['.$i, '', false, 'mason_command') .
                        form_input('field_title]['.$i, $element_config['title'], 'class="field_title"'),
                    );
                $settings_block[] = array(
                        $label_width => lang('mason_name'), // . ' ' . $i,
                        form_input('field_name]['.$i, $element_config['name'], 'class="field_name"'),
                    );
                $settings_block[] = array(
                        $label_width => lang('mason_type'),
                        form_dropdown('field_type]['.$i, $element_options, $element_config['type'], 'class="field_type"'),
                    );
                
                if(!isset($element_config['settings']))
                {
                    $element_config['field_settings'] = array();
                }
                
                
                // Load settings for this element
                $element_type = $element_config['type'];
                
                if(method_exists($this->EE->elements->$element_type->handler, 'display_element_settings'))
                {
                    //$this->EE->elements->$element_type->handler->settings = $element_settings;
                    $element_settings = $this->EE->elements->$element_type->handler->display_element_settings(
                        $this->_exclude_setting_system_fields($element_config['settings']));
                    
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
                            $settings_block[] = $element_setting;
                        }
                       
                        //echo htmlspecialchars(print_r($settings, true));
                        //exit;
                    }
                }
                
                
                $settings[] = $settings_block;
            }
        }
        
        // Index for new subelement
        $i++;
        
        // Add blank settings fields to be used to add a new element
        $settings[] = array(
            array(
                'colspan="3"' => '<h4>'.lang('mason_add_heading').'</h4>'
            ),
            array(
                $label_width => lang('mason_title'),
                form_hidden('field_eid]['.$i, '__eid__') .
                form_input('field_title]['.$i, '', 'class="field_title"'),
            ),
            array(
                $label_width => lang('mason_name'),
                form_input('field_name]['.$i, '', 'class="field_name"'),
            ),
            array(
                $label_width => lang('mason_type'),
                form_dropdown('field_type]['.$i, $element_options, 'text_field',  'class="field_type"') . ' (Save to see Element options)',
            ),
            array(
                $label_width => '',
                form_submit('add_subelement', lang('mason_add_subelement'), 'class="submit mason_add_subelement"')
            ),
        );
        
        $vars = array(
            'settings' => $settings,
            'field_types_changed' => isset($data['field_types_changed']) ? $data['field_types_changed'] : false
        );
        
        $settings = $this->EE->load->view('../elements/mason/views/settings_table', $vars, TRUE);
        return $settings;
    }
    
    function save_element_settings($data)
    {
        /* Compose parallel element configuration arrays into a single array of arrays */

        // echo '<h3>save_element_settings</h3>';
        // echo '<h4>data before processing</h4>';
        // var_dump($data);
        // exit;
        $data['mason_name'] = isset($this->element_name) ? $this->element_name : '';
        $data['mason_elements'] = array();
        // var_dump($data['field_name']);
        // var_dump($data['field_settings']);
        foreach($data['field_name'] as $i => $field_name)
        {
            if(!$field_name) continue;
            
            $field_command = isset($data['field_command'][$i]) ? $data['field_command'][$i] : '';

            if($field_command == 'delete') continue;

            $field_title = $data['field_title'][$i];
            $field_type = $data['field_type'][$i];

            // If being saved for the first time, the setting will not be present
            $field_settings = (isset($data['field_settings']) && is_array($data['field_settings']) && isset($data['field_settings'][$i])) ? $data['field_settings'][$i] : array('test' => 'this is');
            echo 'field settings = ';
            var_dump($field_settings);
            $field_eid = $data['field_eid'][$i];
            $field_order = $data['field_order'][$i];
            
            if($field_eid == '__eid__')
            {
                $field_eid = $this->random_string();
            }
            
            $field_settings['title'] = $field_title;
            $field_settings['element'] = $field_type;
            $field_settings['eid'] = $field_eid;
            
            if($field_name && $field_type)
            {
                $data['mason_elements'][] = array(
                    'title' => $field_title,
                    'name' => $field_name,
                    'type' => $field_type,
                    'order' => $field_order,
                    'settings' => $field_settings,
                    'eid' => $field_eid
                );
            }
        }
        
        $data['settings'] = array(
            'name' => isset($this->element_name) ? $this->element_name : '',
            'title' => isset($data['title']) ? $data['title'] : ''
        );
        
        // Remove parallel arrays from data to be saved
        unset($data['field_title']);
        unset($data['field_name']);
        unset($data['field_type']);
        unset($data['field_settings']);
        
        unset($data['field_command']);
        unset($data['field_order']);

        $old_data = $_SESSION['mason_old_settings_'.$this->EE->input->get_post('field_id')];
        $data['field_types_changed'] = $this->field_types_changed($old_data, $data);

        // echo '<h4>data after processing</h4>';
        // var_dump($data);
        // exit;

        return $data;
    }
    
    function field_types_changed($old_data, $data)
    {
        $result = false;
        
        // If a new field has been added, return true
        // if(count($old_data) != count($data))
        // {
        //     return true;
        // }
        
        foreach(array_keys($data) as $k)
        {
            if(isset($data[$k]) && isset($old_data[$k]))
            {
                if($k == 'type' && (!isset($old_data[$k]) || $old_data[$k] != $data[$k]))
                {
                    $field_id = $this->EE->input->get_post('field_id');
                    $field_id = $this->EE->session->set_flashdata('mason_redirect', $field_id.'|');
                    return true;
                }
                
                if(is_array($data[$k]))
                {
                    $result = $result || $this->field_types_changed($old_data[$k], $data[$k]);
                }
            }
        }
        
        return $result;
    }
    
    function preview_element($data)
    {
        /* Loop through configured elements, previewing each one for the backend */
        
        $result = '';
        
        $data = unserialize(base64_decode($data));
        
        if(isset($this->settings['mason_elements']))
        {
            foreach($this->settings['mason_elements'] as $element_config)
            {
                $element_name = $element_config['name'];
                $element_type = $element_config['type'];
                $element_eid = $element_config['eid'];
                $element_settings = $element_config['settings'];
                
                if(method_exists($this->EE->elements->$element_type->handler, 'preview_element'))
                {
                    if(isset($data['element_data'][$element_eid]))
                    {
                        // replaced $element_config['settings'] with $element_settings
                        $this->prep_handler($element_type, $element_settings); 
                        $this->EE->elements->$element_type->handler->settings = $element_settings;
                        $result .= $this->EE->elements->$element_type->handler->preview_element($data['element_data'][$element_eid]);
                    }
                }
            }
        }
        
        return $result;
    }
    
    function prep_handler($element_type, $settings)
    {
        $this->EE->elements->$element_type->handler->element_name  = $settings["title"];
        $this->EE->elements->$element_type->handler->element_title  = $settings["title"];
        $this->EE->elements->$element_type->handler->element_id  = $settings["eid"];
    }
    
    function random_string($length = 16)
    {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $str;
    }
    
    private function _display_content_element_settings($element, $settings = array())
    {
        /* This method is private in Content Elements, so we need a copy */
    
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
        
        preg_match_all('/name\s*=\s*["|\']([^"\']*?)["|\']/', $data, $matches);
        
        if (isset($matches[0]) && is_array($matches[0])) foreach ($matches[0] as $k=>$pattern)
        {    
            if (isset($settings["eid"]))
            {
                $replacement     = str_replace($matches[1][$k],'content_element['.$element.']['.$settings["eid"].']['.$matches[1][$k].']', $matches[0][$k]);
            }
            else
            {
                $replacement     = str_replace($matches[1][$k],'content_element['.$element.'][__index__]['.$matches[1][$k].']', $matches[0][$k]);
            }
            $data = str_replace($pattern, $replacement, $data);
        }

        $vars = array(
            "title"        => @$settings['title'],          //element title
            "eid"          => (@$settings['eid'])?$settings['eid']:'__index__',
            "element"      => $element,                       //element type
            "data"         => $data,
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
    
    private function _load_asset($asset)
    {
        if(!isset($this->cache['assets_loaded'][$asset]))
        {
            $theme_url = rtrim($this->EE->config->item('theme_folder_url'),'/').'/third_party/content_elements/elements/mason/';
            if(substr($asset, -2, 2) == 'js')
            {
                $this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$theme_url.$asset.'"></script>');         
            } else {
                $this->EE->cp->add_to_foot('<link rel="stylesheet" href="'.$theme_url.$asset.'" type="text/css" media="screen" />');            
            }
            $this->cache['assets_loaded'][$asset] = TRUE;
        }
    }
    
}

if(!function_exists('pl_form_hidden')) 
{ 
    function pl_form_hidden($name, $value = '', $id = false, $class = false) 
    { 
        if (!is_array($name)) 
        { 
            return '<input type="hidden" id="'.($id ? $id : $name).'" class="'.($class ? $class : '').'" name="'.$name.'" value="'.form_prep($value).'" />'; 
        } 
        $form = ''; 
        foreach ($name as $name => $value) 
        { 
            $form .= "\n"; $form .= '<input type="hidden" id="'.($id ? $id : $name).'" name="'.$name.'" value="'.form_prep($value).'" />'; 
        } 
        return $form; 
    }
}

function array_dump($array, $separators = array(' ')) {
    // If separators is not an array make it one
    if(!is_array($separators)) $separators = array($separators);

    if(!is_array($array))
    {
        echo $array;
    } else {
        echo 'array<br />(';
        echo '<ul>';
        foreach($array as $k => $v)
        {
            echo '<li>';
            echo $k.' = ';
            array_dump($v);
            echo '</li>';
        }
        echo '</ul>';
        echo ')';
    }
}
