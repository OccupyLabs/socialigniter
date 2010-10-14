<?php
/**
* MY Text Helper
*
* @package		Social Igniter
* @subpackage	Form Helper
* @author		Brennan Novak
* @link			http://social-igniter.com
*
* @access	public
* @param	string
* @return	boolean
*
* Offers more file & folder options than the normal File Helper
*/

function form_submit_publish($publish, $save)
{
    if ($publish == "Publish")
    {
    	$status = "P"; 
    }
    elseif ($save == "Save Draft") 	
    {
    	$status = "S";		
	}
	else
	{
		$status = "S";	        
	}

	return $status;
}