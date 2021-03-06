<?php  if  ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* Name:		Dashboard_Controller Library
* 
* Author:	Brennan Novak
* 		  	contact@social-igniter.com
*         	@brennannovak
* 
* Location: http://github.com/socialigniter
* 
* Created:  06-01-2010
* 
* Description: Library that is extended by all "Dashboard" facing 'home' controllers
* that requires user authentication and access
*/
class Dashboard_Controller extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

	    if (!$this->social_auth->logged_in()) redirect('login');

	    // Load Values
        $this->data['head']						= $this->load->view(config_item('dashboard_theme').'/partials/head_dashboard.php', $this->data, true);
        $this->data['navigation']				= '';
        $this->data['content']					= '';
        $this->data['shared_ajax']				= '';
        $this->data['sidebar_messages']			= $this->load->view(config_item('dashboard_theme').'/partials/sidebar_messages.php', $this->data, true);
        $this->data['sidebar_tools']			= $this->load->view(config_item('dashboard_theme').'/partials/sidebar_tools.php', $this->data, true);
        $this->data['sidebar_admin']			= $this->load->view(config_item('dashboard_theme').'/partials/sidebar_admin.php', $this->data, true);
		$this->data['footer']					= $this->load->view(config_item('dashboard_theme').'/partials/footer.php', $this->data, true);
		$this->data['modules_assets']			= NULL;

    	// Set This Module Vars
       	if ($this->module_name) 
    	{		
			$this->data['modules_assets'] 		= base_url().'application/modules/'.$this->module_name.'/assets/';
			$this->data['this_module']			= $this->module_name;
		}

		// If Modules Exist
		if ($this->modules_scan)
		{
			foreach ($this->modules_scan as $module)
			{
				$this->load->config($module.'/'.$module);
			
				if (config_item($module.'_enabled') == 'TRUE')
				{	
					// Set Module Partials
					$module_head 						= '/modules/'.$module.'/views/partials/head_dashboard.php';
					$module_sidebar_messages 			= '/modules/'.$module.'/views/partials/sidebar_messages.php';
					$module_sidebar_tools 				= '/modules/'.$module.'/views/partials/sidebar_tools.php';
					$module_sidebar_admin 				= '/modules/'.$module.'/views/partials/sidebar_admin.php';
					$this->data['this_module_assets'] 	= base_url().'application/modules/'.$module.'/assets/';
	
					// Load Views From All Modules
				    if (($this->module_name == $module) && (file_exists(APPPATH.$module_head)))
				    {
				    	$this->data['head'] 			.= $this->load->view('..'.$module_head, $this->data, true);
				    }
				    
				    if (file_exists(APPPATH.$module_sidebar_messages))
				    {
				    	$this->data['sidebar_messages'] .= $this->load->view('..'.$module_sidebar_messages, $this->data, true);
				    }
				    
				    if (file_exists(APPPATH.$module_sidebar_tools))
				    {
				    	$this->data['sidebar_tools'] 	.= $this->load->view('..'.$module_sidebar_tools, $this->data, true);
				    }
				}
			}
		}
    }

    function render($layout='dashboard')
    {
    	// Is Module
       	if ($this->module_name) 
    	{
    		// Navigation extends / replaces core navigation If this changes it breaks 'settings' navigations
		    if (!file_exists(APPPATH.'/modules/'.$this->module_name.'/views/partials/navigation_'.$this->module_controller.'.php'))
		    {
				$navigation_path	= config_item('dashboard_theme').'/partials/navigation_'.$this->module_controller.'.php';
			}
			// Does URLS like 'home/blog/write'
			else
		    {
        		$navigation_path	= '../modules/'.$this->module_name.'/views/partials/navigation_home.php';
			}
			
			// Content Path
    	    $content_path 			= '../modules/'.$this->module_name.'/views/'.$this->module_controller.'/'.$this->action_name.'.php';
		}
		// Module but uses 'home activity feed' like '/home/blog'
		elseif (($this->uri->segment(1) == 'home') && (in_array($this->uri->segment(2), $this->modules_scan)))
		{			
			$this->data['modules_assets'] = base_url().'application/modules/'.$this->uri->segment(2).'/assets/';        
        
        	$navigation_path		= '../modules/'.$this->uri->segment(2).'/views/partials/navigation_home.php';
    	    $content_path 			= config_item('dashboard_theme').'/home/module.php';
		}
		// Settings (only needed for navigation; should perhaps be rethought in the future)
		elseif ($this->uri->segment(1) == 'settings')
		{
			$this->data['modules_assets'] = base_url().'application/modules/'.$this->uri->segment(2).'/assets/';

	        $navigation_path 		= config_item('dashboard_theme').'/partials/navigation_settings.php';
        	$content_path 			= config_item('dashboard_theme').'/'.$this->controller_name.'/'.$this->action_name.'.php';
		}
		// Comments *Kind of nasty solution but works. Should perhaps be rethought in the future
		elseif ($this->uri->segment(2) == 'comments')
		{
	        $navigation_path 		= config_item('dashboard_theme').'/partials/navigation_comments.php';
        	$content_path 			= config_item('dashboard_theme').'/'.$this->controller_name.'/'.$this->action_name.'.php';
		}
		// Dashboard Error Page - must be manual redirect(404) goes to public error page
		elseif ($this->uri->segment(2) == 'error')
		{
	        $navigation_path 		= config_item('dashboard_theme').'/partials/navigation_error.php';
        	$content_path 			= config_item('dashboard_theme').'/'.$this->controller_name.'/'.$this->action_name.'.php';
		}
		// Not Module
		else
		{
	        $navigation_path 		= config_item('dashboard_theme').'/partials/navigation_'.$this->controller_name.'.php';
        	$content_path 			= config_item('dashboard_theme').'/'.$this->controller_name.'/'.$this->action_name.'.php';
		}

		// Load Partial Views
        $this->data['navigation'] 	= $this->load->view($navigation_path, $this->data, true);
        $this->data['content'] 		= $this->load->view($content_path, $this->data, true);

 		// Load Main Template View
        $this->load->view(config_item('dashboard_theme').'/layouts/'.$layout.'.php', $this->data);
    }
}