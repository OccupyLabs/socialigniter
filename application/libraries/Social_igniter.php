<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Social Igniter Library
*
* @package		Social Igniter
* @subpackage	Social Igniter Library
* @author		Brennan Novak
* @link			http://social-igniter.com
*
* runs all basic social connections to external sites
*/
 
class Social_igniter
{
	public $social_connections;

	function __construct()
	{
		$this->ci =& get_instance();

		$this->ci->load->model('settings_model');
		$this->ci->load->model('site_model');		
		$this->ci->load->model('pages_model');
 		$this->ci->load->model('activity_model');
 		$this->ci->load->model('content_model');		
		
		$this->site_id = config_item('site_id'); 			
	}	
	
	
    // Profile Picture	
	function profile_image($user_id, $image, $email=NULL, $size='normal')
	{
		$picture 	 = '';	
		$nopicture	 = base_url().config_item('profile_images').$size.'_'.config_item('profile_nopicture');
		
		if ($image)
		{
			$picture = base_url().config_item('profile_images').$user_id.'/'.$size.'_'.$image;
		}
		elseif (config_item('site_gravatar_enabled') == 'TRUE')
		{
			$this->ci->load->helper('gravatar');
			$picture = gravatar($email, "X", config_item('profile_normal_width'), $nopicture);
		}
		else
		{
			$picture = $nopicture;
		}
				
		return $picture;
	}

	// Page Title 
    function title($sub_title, $page_title=FALSE, $site_title=FALSE)
    {
    	$title = NULL;
	
    	if($sub_title != '')
    	{
    		$title .= $sub_title.' '.config_item('site_title_delimiter').' ';
    	}

    	if($page_title != '')
    	{
    		$title .= $page_title.' '.config_item('site_title_delimiter').' ';
    	}	
    	    	
    	return $title.$site_title;
    }	
    
    // Generate Item
    function render_item($verb, $type, $data)
    {
    	$object 	= json_decode($data);
    	$has_url	= property_exists($object, 'url');
    	$has_title	= property_exists($object, 'title');
    	
    	$item 		= $this->render_item_status($verb, $type, $object, $has_url, $has_title);
    	
    	if ($type != 'status')
    	{
    		$item .= $this->render_item_content($type, $object);
    	}
   
    	return $item;
    }
    
    // Generate Status
    function render_item_status($verb, $type, $object, $has_url, $has_title)
    {
    	// Status
    	if ($type == 'status')
    	{
    		return $this->get_content($object->content_id)->content;
		}
				
		// Has Status
    	$has_status = property_exists($object, 'status');

		if ($has_status)
		{
			return $object->status;
		}
		// Makes 'posted an article'
       	else
    	{
    		$verb		= item_verb($this->ci->lang->line('verbs'), $verb);
    		$article	= item_type($this->ci->lang->line('object_articles'), $type);
    		$type		= item_type($this->ci->lang->line('object_types'), $type);
    		$action 	= '<span class="item_verb">'.$verb.' '.$article.'</span> ';
    		
    		// Has Title
    		if ($has_title)
    		{
    			$title 	= $object->title;
    		}
    		else
    		{
    			$title 	= $type;
    			$type	= '';
    		}
    		
    		// Has URL
    		if ($has_url)	$link = ' <a href="'.$object->url.'">'.$title.'</a>';
    		else			$link = $title;    		
    		
    		return '<span class="item_verb">'.$verb.' '.$article.' '.$type.' '.$link.'</span>';
    	}    	
    }

    // Generate Content
    function render_item_content($type, $object)
    {
        $has_thumb	= property_exists($object, 'thumb');
    
		$render_function = 'render_item_'.$type;
		$callable_method = array($this, $render_function);
		   
		// Custom Render Exists    		    		
		if (is_callable($callable_method, false, $callable_function))
		{
			$content = $this->$render_function($object, $has_thumb);
		}
		else
		{
			$content = $this->render_item_default($object, $has_thumb);
		}
    	
    	return '<span class="item_content">'.$content.'</span>';
    }
    
    /* Item Types */
    function render_item_default($object, $has_url, $has_thumb)
    {
	    // Has Thumbnail
		if ($has_thumb) 
		{
			$content = '<a href="'.$url.'"><img src="'.$object->thumb.'" border="0"></a>';
		}
		else
		{
			$content = '<a href="'.$url.'">'.$object->title.'</a>';
		}
	    
    	return $content;
    }
    
    function render_item_page($object, $has_thumb)
    {
    	return '<span class="item_content_detail">"'.$object->description.'"</span>';
    }
    
    function render_item_photo($object, $has_thumb)
    {    
    	return '<a href="'.$object->url.'"><img src="'.$object->thumb.'" border="0"></a>';
    }
    
    function render_item_event($object, $has_thumb)
    {
    	$thumb	 = NULL;
    	$title	 = NULL;
    	$details = NULL;
			
	    // Has Thumbnail
		if ($has_thumb) 
		{
			$thumb = '<a href="'.$object->url.'"><img class="item_content_thumb" src="'.$object->thumb.'" border="0"></a>';
		}
		
		$title = '<span class="item_content_detail_sm"><a href="'.$object->url.'">'.$object->title.'</a></span>';
		
		// Location
		if (property_exists($object, 'location'))
		{
			$details = '<span class="item_content_detail_sm">Location: <span class="color_black">'.events_location($object->location, array('name','locality','region')).'</span></span>';
		}

		// Date
		if (property_exists($object, 'date_time'))
		{
			$details .= '<span class="item_content_detail_sm">Time: <span class="color_black">'.format_datetime('SIMPLE_TIME', $object->date_time).'</span></span>';
		}

		// Description
		if (property_exists($object, 'description'))
		{		
			$details .= $object->description;
    	}
    	    
    	return $thumb.$title.$details;    
    }
   
		
	/* Social */
	function get_social_logins($html_start, $html_end)
	{
		$social_logins 		= NULL;
		$get_social_logins	= $this->get_settings_type('social_login');
	
		foreach ($get_social_logins as $login)
		{
			if (($login->value == 'TRUE') && (config_item($login->module.'_enabled')) == 'TRUE')
			{
				$data['assets']	   = base_url().'application/modules/'.$login->module.'/assets/';
				$social_logins 	  .= $html_start.$this->ci->load->view('../modules/'.$login->module.'/views/partials/social_login.php', $data, true).$html_end;
			}
		}
		return $social_logins;
	}

	function get_social_post($user_id)
	{
		$post_to 			= NULL;
		$user_connections	= $this->ci->social_auth->get_connections_user($user_id);

		foreach ($this->ci->social_post as $social)
		{
			foreach($user_connections as $exists)
			{
				if ($exists->module == $social)
				{
					$post_to .= '<li><input type="checkbox" value="1" id="post_'.$social.'" checked="checked" name="post_'.$social.'" /> '.ucwords($social).'</li>';
				}
			}		
		}
		
		if ($post_to)
		{
			return '<ul id="social_post">'.$post_to.'</ul>';
		}
			
		return NULL;
	}

	// NEEDS TO BE FIXED
	function get_social_checkin($user_id)
	{
		$checkin 			= NULL;
		$user_connections	= $this->ci->social_auth->get_connections_user($user_id);

		foreach ($this->ci->social_checkin as $social)
		{
			foreach($user_connections as $exists)
			{
				if ($exists->module == $social)
				{
					$checkin .= '<li><input type="checkbox" value="1" id="post_'.$social.'" checked="checked" name="post_'.$social.'" /> '.ucwords($social).'</li>';
				}
			}		
		}
		
		if ($checkin)
		{
			return '<ul id="social_post"><li id="social_post_share">Check In:</li>'.$checkin.'</ul>';
		}
			
		return NULL;
	}
	
	
	/* File & Directory Scanning */
	function scan_themes()
	{
		return $themes_scan = directory_map('./application/views/', TRUE);		
	}

	function scan_modules()
	{
		return $modules_scan = directory_map('./application/modules/', TRUE);
	}

	function scan_layouts($theme)
	{		
		$layouts_scan	= directory_map('./application/views/'.$theme.'/layouts/', TRUE);
		$layouts		= array();
		
		foreach ($layouts_scan as $layout)
		{
			$layout = str_replace('.php', '', $layout);
		
			if ($layout != 'profile')
			{
				$layouts[] = $layout;
			}
		}
	
		return $layouts;
	}
	
	
	/* Site */
	function get_site()
	{
		return $this->ci->site_model->get_site();
	}
	
	function get_themes($theme_type='site')
	{
		$theme_array		= array();
		$themes 			= $this->scan_themes();
	
		foreach ($themes as $theme)
		{
			if (strstr($theme, $theme_type))
			{
				$theme_array[] = $theme;
			}			
		}
	
		return $theme_array;
	}	
	
	
	/* Pages */
	function get_index_page()
	{
		return $this->ci->pages_model->get_index_page($this->site_id);
	}
	
	function get_page($title_url)
	{
		return $this->ci->pages_model->get_page($this->site_id, $title_url);
	}	

	function get_page_id($page_id)
	{
		return $this->ci->pages_model->get_page_id($page_id);
	}

	function get_pages()
	{
		return $this->ci->pages_model->get_pages($this->site_id);
	}
	
	function get_menu()
	{
		return $this->ci->pages_model->get_menu($this->site_id);	
	}
	
	
	/* Settings */	
	function get_settings($module=NULL)
	{
		return $this->ci->settings_model->get_settings($this->site_id, $module);
	}

	function get_settings_type($setting)
	{
		return $this->ci->settings_model->get_settings_type($setting);
	}	

	function get_settings_type_value($setting, $value)
	{
		return $this->ci->settings_model->get_settings_type_value($setting, $value);
	}	
	
	function get_setting_module_type($module, $setting)
	{
		return $this->ci->settings_model->get_setting_module_type($module, $setting);
	}	

	function update_settings($module, $settings_update_array)
	{
		$settings_current = $this->get_settings($module);
	
		// Loop through all settings submitted
		foreach ($settings_update_array as $setting_update)
		{
			$name = key($settings_update_array);

			// Loops through all current settings
			foreach ($settings_current as $setting_current)
			{
				// If matches update it
				if ($setting_current->setting == $name)
				{
					$this->ci->settings_model->update_setting($setting_current->settings_id, $name, $setting_update);
					break;
				}
			}
			next($settings_update_array);
			
		}
		return;	
	}	

	
	/* Activity */
	function get_timeline()
	{
		return $this->ci->activity_model->get_timeline();		
	}
	
	function get_activity($activity_id)
	{
		return $this->ci->activity_model->get_activity($activity_id);
	}
	
	function add_activity($info, $data)
	{
		return $this->ci->activity_model->add_activity($info, json_encode($data));
	}
	
	function delete_activity($activity_id)
	{
	 	$activity = $this->get_activity($activity_id);

 		if (is_object($activity))
 		{ 		
 			if ($activity->user_id != $this->ci->session->userdata('user_id'))
 			{
 				return FALSE;
 			}
 		
 			$this->ci->activity_model->delete_activity($activity->activity_id);
 		
 			if ($activity->type == 'status')
 			{
 				$content = json_decode($activity->data);
 				
 				$this->delete_content($content->content_id);
 			}
 		
 			return TRUE;
 			
 		}

		return FALSE;
	}
	

	/* Content */
	function check_content_comments($content_id)
	{
		$content = $this->ci->content_model->get_content($content_id);
		
		if ($content->comments_allow == 'N')
		{
			return FALSE;
		}
		elseif (($content->comments_allow == 'A') || ($content->comments_allow == 'Y'))
		{
			return $content;
		}
		
		return FALSE;
	}
	
	function check_content_duplicate($user_id, $title, $content)
	{
		if (!$this->ci->content_model->check_content_duplicate($user_id, $title, $content))
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	function get_content($content_id)
	{
		return $this->ci->content_model->get_content($content_id);
	}
	
	function add_content($content_data, $site_id=NULL)
	{
		$check_content = $this->check_content_duplicate($content_data['user_id'], $content_data['title'], $content_data['content']);
	
		if ($check_content)
		{
			if (!$site_id) $site_id = $this->site_id;
		
			return $this->ci->content_model->add_content($site_id, $content_data);
		}
		else
		{
			return FALSE;
		}
	}
	
	function update_content($content_id, $content_data)
	{
		return $this->ci->content_model->update_content($content_id, $content_data);
	}

	function update_content_comments_count($content_id)
	{
		$comments_count = $this->ci->social_tools->get_comments_content_count($content_id);
	
		return $this->ci->content_model->update_content_comments_count($content_id, $comments_count);
	}
	
	function delete_content($content_id)
	{
		return $this->ci->content_model->delete_content($content_id);
	}
	
		
}