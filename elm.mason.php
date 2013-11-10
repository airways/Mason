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

    var $info = array(
        'name'    => 'Mason',
        'version'    => '1.0.0'
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
    
    /*function validate_element($data)
    {
        
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
        
        
        //var_dump($_POST);exit;
    }
    */
    
    function save_element($data)
    {
        /* Loop through configured elements, saving each one and packing the data into a single array */
        
        $save_data = array(
            'element_data' => array(),
        );
        
        /*
        echo '<pre>';
        echo 'IN ELEMENT:';
        print_r($_POST);
        echo '</pre>';
        exit;
        // */
        
        preg_match('/([^\]]*)\[([^\]]*)\].*/', $this->field_name, $matches);

        $field_name = $matches[1];
        $mason_id = $matches[2];
        
        //$mason_id = $data;
        
        
        if($mason_id && isset($this->settings['mason_elements']))
        {
            // Find our sub-element data
            /*
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
            }*/
            //echo '<b>settings[mason_elements]:</b>';
            //var_dump($this->settings['mason_elements']);
            //echo '<b>POST:</b>';
            //var_dump($_POST);
            foreach($this->settings['mason_elements'] as $element_config)
            {
                $element_name = $element_config['name'];
                $element_type = $element_config['type'];
                $element_eid = $element_config['eid'];
                
                //echo 'Try to save:';
                //var_dump($element_eid);
                
                foreach($_POST['mason'][$mason_id]['sub_elements'] as $new_eid => $data)
                {
                    if(!is_array($data)) continue;
                    if($data['field_eid'] != $element_eid) continue;
                    
                    
                    if($element_eid != '__hash_key__' && method_exists($this->EE->elements->$element_type->handler, 'save_element'))
                    {
                        //echo 'save <b>'.$element_eid.'</b><br/>';
                        //var_dump($data);
                        $save_data['element_data'][$element_eid] = $this->EE->elements->$element_type->handler->save_element($data['data']);
                        //var_dump('done');
                    } else {
                        $save_data['element_data'][$element_eid] = $data['data'];
                        //var_dump('No save_element method or whatever');
                        //var_dump($save_data['element_data'][$element_eid]);
                    }
                }
            }
        } else {
            echo 'Missing mason id or no mason_elements defined.<br/>';
        }
        //var_dump($_POST);
        
        //echo '<b>FINAL SAVE:</b>';
        //var_dump($save_data);
        
        $out = base64_encode(serialize($save_data));
        //echo $out.'</br>';
        //echo 'hash: '.md5($out).'<br/>';
        //exit;
        return $out;
    }
    
    function display_element($data)
    {
        /* Loop through configured elements, unpacking each one's data and it for the backend form */
        
        $result = '<div class="mason_container">';
        
        preg_match('/([^\]]*)\[([^\]]*)\].*/', $this->field_name, $matches);
        
        $field_name = $matches[1];
        $mason_id = $matches[2];
        
        //if($mason_id == '__index__') $mason_id = '';
        //if($field_name == '__element_name__') $field_name = '';
        
        
        
        //$result .= form_hidden('__element_name__[__index__][settings]', base64_encode(serialize($this->settings)));
        
        $this->_load_asset('publish.js');
        
        $result .= '<input type="hidden" name="'.$field_name.'['.$mason_id.'][data]" value="'.$mason_id.'" />';
        
        
        //echo 'Load data:';
        //echo $data.'<br/>';
        //echo 'hash: '.md5($data).'<br/>';
        $load_data = unserialize(base64_decode($data));
        
        //var_dump($load_data);
        //var_dump($data);
        //var_dump($this->settings);
        if(isset($this->settings['mason_elements']))
        {
            if(isset($load_data['element_data']) && is_array($load_data['element_data']))
            {
                foreach($load_data['element_data'] as $key => $data)
                {
                    if(!is_array($data) && substr($data, 0, 2) === 'a:')
                    {
                        $load_data['element_data'][$key] = unserialize($data);
                    } else {
                        $load_data['element_data'][$key] = $data;
                    }
                }
            }
            //echo '<pre>';
            //print_r($load_data);
            //echo '</pre>';
            
            $i = 0;
            //var_dump($this->settings['mason_elements']);
            
            //echo 'Element data:';
            //var_dump($load_data['element_data']);
            
            foreach($this->settings['mason_elements'] as $element_config)
            {
                $i++;
                //var_dump($element_config);
                $element_name = $element_config['name'];
                $element_type = $element_config['type'];
                $element_eid = $element_config['eid'];
                $element_settings = $element_config['settings'];
                
                if(method_exists($this->EE->elements->$element_type->handler, 'display_element'))
                {
                    
                    //echo 'DISPLAY <b>'.$element_eid.'</b><br/>';
                    
                    if($mason_id)
                    {
                        $this->EE->elements->$element_type->handler->field_name = str_replace($mason_id, $element_eid, $this->field_name);
                    }
                    
                    $this->EE->elements->$element_type->handler->settings = $element_settings;
                    $element_result = $this->EE->elements->$element_type->handler->display_element(@$load_data['element_data'][$element_eid], true);
                    
                    /*
                    if(preg_match_all('/name="([^"]*)"/', $element_result, $matches))
                    {
                        foreach($matches[1] as $match_i => $input_name)
                        {
                            //echo $input_name.'<br/>';
                            //$element_result = str_replace('name="'.$input_name.'"', 'name="'.$input_name.'['.$i.']"', $element_result);
                        }
                        
                    }
                    */
                    //echo $field_name;
                    
                    
                    if($i > 1) $result .= '<br/><br />';
                    $result .= '<div class="mason_field" data-element-type="'.$element_type.'" data-eid="'.$element_eid.'">';
                    $element_config['element_type'] = $element_config['type'];
                    
                    //echo 'element_settings:';
                    //var_dump($element_config);
                    
                    
                    /*
                    '<input type="hidden" name="mason[__mason_id__][sub_elements]['+eid+']" value="'+sub_type+'">'
                       + '<input type="hidden" name="'+field_name+'['+eid+'][element_type]" value="'+sub_type+'">'
                       + '<input type="hidden" name="'+field_name+'['+eid+'][mason_id]" value="__mason_id__">'
                       + '<input type="hidden" name="'+field_name+'['+eid+'][field_eid]" value="'+eid+'">'
                    */
                    
                    
                    $element_result = form_hidden($field_name.'['.$element_eid.'][element_settings]', base64_encode(serialize($element_config)))
                        . form_hidden('mason['.$mason_id.'][sub_elements]['.$element_eid.']',  $element_config['type'])
                        . form_hidden($field_name.'['.$element_eid.'][element_type]',  $element_config['type'])
                        . form_hidden($field_name.'['.$element_eid.'][mason_id]',  '__mason_id__')
                        . form_hidden($field_name.'['.$element_eid.'][field_eid]',  '__field_eid__')
                        . $element_result;
                    
                    $element_result = str_replace('__element_name__', $field_name, $element_result);
                    $element_result = str_replace('__index__', $element_eid, $element_result);
                    //$element_result = str_replace($mason_id, $element_eid, $element_result);
                    
                    if(!is_null($load_data['element_data']))
                    {
                        // Existing entry - not a template - make up a temporary ID and replace it into the result
                        $new_eid = $this->random_string();
                        $element_result = str_replace($element_eid, $new_eid, $element_result);
                        
                        $element_result = str_replace('__mason_id__', $mason_id, $element_result);
                        $element_result = str_replace('__field_eid__', $element_eid, $element_result);
                    }
                    
                    //$element_result = str_replace('__field_eid__', $element_eid, $element_result);
                    
                    
                    
                    //$result .= form_hidden('__element_name__[__index__][element_settings]', base64_encode(serialize($element_config)));
                    $result .= '<b>'.$element_config['title'].'</b><br/>';
                    $result .= $element_result;
                    $result .= '</div>';
                    
                    //echo $this->field_name.'<br/>';
                    //echo htmlspecialchars($element_result);exit;
                }
            }
        }
        
        $result .= '</div>';
        
        return $result;
    }
    
    function replace_element_tag($data, $params = array(), $tagdata)
    {
        /* Loop through configured elements, replacing each one for the frontend */
        $load_data = unserialize(base64_decode($data));
        
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
                
                //var_dump($element_name);
                
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
                    $parse_result = $this->EE->elements->$element_type->handler->replace_element_tag($load_data['element_data'][$element_eid], $params, $block);
                } else {
                    $parse_result = $load_data['element_data'][$element_eid];
                }
                
                // Replace the entire matched block including the tag pair with the parse results
                $row_result = str_replace($match, $parse_result, $row_result);
                
                //echo "<pre>Input for ".$element_name.":\n";
                //echo htmlspecialchars($block);
                //echo "<pre>Output for ".$element_name.":\n";
                //echo htmlspecialchars($row_result);exit;

 
                
            }
            $result .= $row_result;
        }
        
        return $result;
    }
    
    function display_element_settings($data)
    {
        /* Display backend settings to configured elements that make up this block */
        
        $this->_load_asset('settings.js');
        
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
        
        if(isset($data['mason_elements']))
        {

            foreach($data['mason_elements'] as $element_config)
            {
                //var_dump($element_config);
                $i++;
                $settings[] = array(
                        lang('mason_title') . ' ' . $i,
                        form_hidden('field_eid]['.$i, $element_config['settings']['eid']) .
                        form_input('field_title]['.$i, $element_config['title'], 'class="field_title"'),
                    );
                $settings[] = array(
                        lang('mason_name') . ' ' . $i,
                        form_input('field_name]['.$i, $element_config['name'], 'class="field_name"'),
                    );
                $settings[] = array(
                        lang('mason_type'),
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
                form_hidden('field_eid]['.$i, '__eid__') .
                form_input('field_title]['.$i, '', 'class="field_title"'),
            );
        $settings[] = array(
                lang('mason_name'),
                form_input('field_name]['.$i, '', 'class="field_name"'),
            );
        $settings[] = array(
                lang('mason_type'),
                form_dropdown('field_type]['.$i, $element_options, 'text_field',  'class="field_type"') . ' (Save to see Element options)',
            );
        
        return $settings;
    }
    
    function save_element_settings($data)
    {
        /* Compose parallel element configuration arrays into a single array of arrays */
        
        /*
        echo '<pre>';
        var_dump($data);
        
                
        echo '<b>element_settings=</b>';
        var_dump($this->settings);
        // */
        
        $data['mason_name'] = isset($this->element_name) ? $this->element_name : '';
        $data['mason_elements'] = array();
        
        foreach($data['field_name'] as $i => $field_name)
        {
            if(!$field_name) continue;
            
            $field_title = $data['field_title'][$i];
            $field_type = $data['field_type'][$i];
            $field_settings = (isset($data['field_settings']) && is_array($data['field_settings'])) ? $data['field_settings'][$i] : array();
            $field_eid = $data['field_eid'][$i];
            
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
                    'settings' => $field_settings,
                    'eid' => $field_eid
                );
            }
        }
        
        $data['settings'] = array(
            'name' => isset($this->element_name) ? $this->element_name : '',
            'title' => isset($data['title']) ? $data['title'] : ''
        );
        
        // Remove parallel arrays
        unset($data['field_title']);
        unset($data['field_name']);
        unset($data['field_type']);
        unset($data['field_settings']);
        
        /*
        var_dump($data);
        exit;
        */
        return $data;
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
                
                if(method_exists($this->EE->elements->$element_type->handler, 'preview_element'))
                {
                    if(isset($data['element_data'][$element_eid]))
                    {
                        $this->prep_handler($element_type, $element_config['settings']);
                        $result .= $this->EE->elements->$element_type->handler->preview_element($data['element_data'][$element_eid]);
                    }
                }
            }
        }
        return $result;
        //return '';
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
                $replacement     = str_replace($matches[1][$k],'content_element['.$element.']['.$settings["eid"].']['.$matches[1][$k].']', $matches[0][$k]);
            }
            else
            {
                $replacement     = str_replace($matches[1][$k],'content_element['.$element.'][__index__]['.$matches[1][$k].']', $matches[0][$k]);
            }
            $data = str_replace($pattern, $replacement, $data);
        }
        
        //display

        $vars = array(
            "title"        => @$settings['title'],          //element title
            "eid"        => (@$settings['eid'])?$settings['eid']:'__index__',
            "element"    => $element,                       //element type
            "data"        => $data,
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
        if(!isset($this->cache['assets_loaded']))
        {
            $theme_url = rtrim($this->EE->config->item('theme_folder_url'),'/').'/third_party/content_elements/elements/mason/';
            $this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$theme_url.$asset.'"></script>');			
            $this->cache['assets_loaded'] = TRUE;
        }
    }
    
}
