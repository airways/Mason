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
                This extension is a required helper that is used by the mason
                element to gather it's data prior to Content Elements
                processing.
  Written for : PHP 5.2+, ExpressionEngine 2.5.3+, Content Elements 1.1.0+
  Usage       : Install under this location in the third party directory:
                system/expressionengine/third_party/mason/
                You must also install the Mason element within the Content
                Elements directory.
  Called by   : ExpressioneEngine
  Calls       : Nothing
 -----------------------------------------------------------------------------*/

class Mason_ext {
    
    public $settings        = array();
    public $description     = 'Helper for Mason Content Element';
    public $docs_url        = '';
    public $name            = 'Mason';
    public $settings_exist  = 'n';
    public $version         = '1.0';
    
    private $EE;
    
    public function __construct($settings = '')
    {
        $this->EE =& get_instance();
        $this->settings = $settings;
    }

    public function activate_extension()
    {
        // Setup custom settings in this array.
        $this->settings = array();
        
        $data = array(
            'class'	    => __CLASS__,
            'method'    => 'sessions_end',
            'hook'      => 'sessions_end',
            'settings'  => serialize($this->settings),
            'version'   => $this->version,
            'enabled'   => 'y',
            'priority'  => '5'
        );

        $this->EE->db->insert('extensions', $data);
        
    }

    public function sessions_end()
    {
        // Strip data from the post for sub-elements
        if(isset($_GET['C']) && $_GET['C'] == 'content_publish' &&
           isset($_GET['M']) && $_GET['M'] == 'entry_form' &&
           isset($_POST['mason']))
        {
            /*
            echo '<b>RAW POST:</b><br/>';
            var_dump($_POST);
            echo 'running...';
            exit;
            // */
            
            
            foreach($_POST['mason'] as $mason_id => $mason_config)
            {
                foreach($mason_config['sub_elements'] as $sub_element_hash => $sub_element_type)
                {
                    foreach($_POST as $field => $array)
                    {
                        if(is_array($array) && isset($array[$sub_element_hash]) && isset($array[$sub_element_hash]['mason_id']))
                        {
                            $mason_id = $array[$sub_element_hash]['mason_id'];
                            $element_settings = unserialize(base64_decode($_POST[$field][$sub_element_hash]['element_settings']));
                            $_POST['mason'][$mason_id]['sub_elements'][$sub_element_hash] = $_POST[$field][$sub_element_hash];
                            unset($_POST[$field][$sub_element_hash]);
                        }
                    }
                    
                }
            }
        }
        
        /*
        
        if(isset($_GET['C']) && $_GET['C'] == 'admin_content' &&
           isset($_GET['M']) && $_GET['M'] == 'field_edit' &&
           isset($_POST['content_element']))
        {
            $field_id = $_POST['field_id'];
            $query = $this->EE->db->where('field_id', $field_id)->get('channel_fields');
            $row = $query->row();
            $field_settings = unserialize(base64_decode($row->field_settings));
            
            $content_elements = unserialize($field_settings['content_elements']);
            echo '<pre>';
            //var_dump($_POST);
            
            foreach($content_elements as $i => $element)
            {
                $type = $element['type'];
                if($type != 'mason') continue;
                
                $eid = $element['settings']['eid'];
                $mason_elements = $element['settings']['mason_elements'];
                
                foreach($mason_elements as $j => $subelement)
                {
                    echo "<b>found subelement</b>\n";
                    var_dump($subelement);
                    
                    $subelement_type = $subelement['type'];
                    $subelement_eid = $subelement['settings']['eid'];
                    $subelement_settings = $subelement['settings'];
                    
                    if(!isset($_POST['content_element']['mason'][$eid]['field_eid'][$j+1]))
                    {
                        $_POST['content_element']['mason'][$eid]['field_eid'][$j+1] = $subelement_eid;
                    }
                    
                    foreach($subelement_settings as $setting => $value)
                    {
                        if(!isset($_POST['content_element']['mason'][$eid]['field_settings'][$j+1][$setting]))
                        {
                            echo "preserve settings from db ".$setting .'='.$value."\n";
                            $_POST['content_element']['mason'][$eid]['field_settings'][$j+1][$setting] = $value;
                        }
                        
                        //if(!isset($_POST['content_element']['mason'][$eid][$setting][$j+1]))
                        //{
                        //    //echo $setting .'='.$value."\n";
                        //    $_POST['content_element']['mason'][$eid][$setting][$j+1] = $value;
                        //}
                    }
                    
                    
                }
                
                
            }
            
            
            
            echo '<pre>post=';
            var_dump($_POST['content_element']['mason']);
            exit;
            */
            
            /*
            $str = $_POST['mason_settings'];
            if($str[0] == '{' && $str[strlen($str)-1] == '}')
            {
                //echo "<pre>parsing\n";
                $mason_settings = json_decode($_POST['mason_settings']);
                // TODO: Need to parse the keys of this into actual arrays within $_POST so that the rest of the
                // code will continue to function normally
                foreach($mason_settings as $key => $value)
                {
                    // Parse the key pattern content_elements[mason][*][*][*]
                    //echo $key.'<br/>';
                    if(preg_match('#content_element'.str_repeat('\[(.*?)\]', substr_count($key, '[')).'#', $key, $matches))
                    {
                        for($i = 1; $i < count($matches); $i++)
                        {
                            echo $matches[$i]."\n";
                            if($i < count($matches) - 1)
                            {
                                
                            }
                        }
                    }
                }
            }
            
            //exit;
            

            //echo '<b>RAW POST:</b><br/>';
            //var_dump($_POST);
            //echo 'running...';
            //exit;

        }
        */
        /*
        echo '<pre>';
        echo 'IN EXTENSION:';
        print_r($_POST);
        echo '</pre>';
        // */
        
    }
    
    function _set_post_value($depth, $key, $value)
    {
        
    }

    function disable_extension()
    {
        $this->EE->db->where('class', __CLASS__);
        $this->EE->db->delete('extensions');
    }

    function update_extension($current = '')
    {
        if ($current == '' OR $current == $this->version)
        {
            return FALSE;
        }
    }
    
    // ----------------------------------------------------------------------
}

/* End of file ext.mason.php */
/* Location: /system/expressionengine/third_party/mason/ext.mason.php */