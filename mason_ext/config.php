<?php

// @version 1.12

define('MASON_VERSION', '1.12');
define('MASON_NAME', 'Mason');
define('MASON_CLASS', 'Mason'); // must match module class name
define('MASON_DESCRIPTION', 'Mason Content Block for Content Elements.');
define('MASON_DOCSURL', 'http://metasushi.com/documentation/mason');
define('MASON_DEBUG', TRUE);


// EE 2.5.5 or less not officially supported anymore,
// but keeping this for backwards compatibility.
if (version_compare(APP_VER, '2.6', '<') && !function_exists('ee'))
{
    function ee()
    {
        static $EE;
        if ( ! $EE) $EE = get_instance();
        return $EE;
    }
}

// EE 2.8 cp_url function is now used to generate URLs - need to provide it if
// we are on a version prior to EE 2.8
if (version_compare(APP_VER, '2.8', '<') && !function_exists('cp_url'))
{
    function cp_url($path, $qs = '')
    {
    	$path = trim($path, '/');
    	$path = preg_replace('#^cp(/|$)#', '', $path);
        
        $segments = explode('/', $path);
        $result = BASE.AMP.'C='.$segments[0].AMP.'M='.$segments[1];
        
    	if (is_array($qs))
    	{
    		$qs = AMP.http_build_query($qs, AMP);
    	}
    	
    	$result .= $qs;
    
    	return $result;
    }
}
