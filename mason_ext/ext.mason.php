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

require_once PATH_THIRD.'mason/config.php';

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

    public function sessions_end($sess)
    {
        $this->EE->load->helper('url');
        
        $this->EE->session = $sess;
        if($mason_redirect = $this->EE->session->flashdata('mason_redirect'))
        {
            list($field_id, $mason_id) = explode('|', $mason_redirect);
            $mason_id = 'element_modified_type';
            $this->set_base();
            
            //$this->EE->functions->redirect(BASE.AMP.'C=admin_content'.AMP.'M=field_edit'.AMP.'field_id='.$field_id.'#'.$mason_id);
            //echo 'cp_url='.cp_url('cp/admin_content/field_edit', array('field_id' => $field_id)).'#'.$mason_id;
            //exit;
            //header('Location: '.cp_url('cp/admin_content/field_edit', array('field_id' => $field_id)).'#'.$mason_id);
            //exit;
            $this->EE->functions->redirect(cp_url('cp/admin_content/field_edit', array('field_id' => $field_id)).'#'.$mason_id);
            
        }
        
        // Strip data from the post for sub-elements
        if(isset($_POST['mason_entry_form']) && isset($_POST['mason']))
        {   
            //echo '<hr/><pre><b>POST</b> '.__FILE__.':'.__LINE__.PHP_EOL;
            //print_r($_POST);
            foreach($_POST['mason'] as $mason_id => $mason_config)
            {
                foreach($mason_config['sub_elements'] as $sub_element_hash => $sub_element_type)
                {
                    foreach($_POST as $field => $array)
                    {
                        if(is_array($array) && isset($array[$sub_element_hash]) && isset($array[$sub_element_hash]['mason_id']))
                        {
                            $mason_id = $array[$sub_element_hash]['mason_id'];
                            // This variable is never referenced again....
                            // $element_settings = unserialize(base64_decode($_POST[$field][$sub_element_hash]['element_settings']));
                            //echo '<hr/><pre><b>mason_ext</b> '.__FILE__.':'.__LINE__.PHP_EOL;
                            //print_r(array('sub_element_hash' => $sub_element_hash, 'field' => $field, 'sub_element_data' => $_POST[$field][$sub_element_hash]));
                            $_POST['mason'][$mason_id]['sub_elements'][$sub_element_hash] = $_POST[$field][$sub_element_hash];
                            unset($_POST[$field][$sub_element_hash]);
                        }
                    }
                    
                }
            }
        }
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
    
    function set_base()
    {
        if(defined('BASE')) return BASE;
        
		$s = 0;

		switch ($this->EE->config->item('admin_session_type'))
		{
			case 's'	:
				$s = $this->EE->session->userdata('session_id', 0);
				break;
			case 'cs'	:
				$s = $this->EE->session->userdata('fingerprint', 0);
				break;
		}

		define('BASE', SELF.'?S='.$s.'&amp;D=cp'); // cp url
		return BASE;
	}

}

/* End of file ext.mason.php */
/* Location: /system/expressionengine/third_party/mason/ext.mason.php */