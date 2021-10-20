<?php
class ControllerExtensionModuleDSubCategoryShowcase extends Controller
{
	private $error = array();
	public $heading_title = "Sub Category showcase";
	public $codename = "d_sub_category_showcase";
	private $route = 'extension/module/d_sub_category_showcase';


	public function __construct($registry)
	{
		parent::__construct($registry);
		$this->d_shopunity = (file_exists(DIR_SYSTEM . 'library/d_shopunity/extension/d_shopunity.json'));
		$this->d_opencart_patch = (file_exists(DIR_SYSTEM . 'library/d_shopunity/extension/d_opencart_patch.json'));
		if ($this->d_opencart_patch) {
			$this->load->model('extension/d_opencart_patch/url');
			$this->load->model('extension/d_opencart_patch/module');
			$this->load->model('extension/d_opencart_patch/load');
			$this->load->model('extension/d_opencart_patch/user');
		}
		$this->d_twig_manager = (file_exists(DIR_SYSTEM . 'library/d_shopunity/extension/d_twig_manager.json'));
		$this->d_admin_style = (file_exists(DIR_SYSTEM . 'library/d_shopunity/extension/d_admin_style.json'));

		if (isset($this->request->get['store_id'])) {
			$this->store_id = $this->request->get['store_id'];
		}
	}


	public function index()
	{
		$this->load->language('extension/module/d_sub_category_showcase');
		$this->document->setTitle($this->heading_title);

		$this->load->model('setting/setting');

		$data['heading_title'] = $this->heading_title;

		if ($this->d_admin_style) {
			$this->load->model('extension/d_admin_style/style');
			$this->model_extension_d_admin_style_style->getStyles('light');
		}

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$settings[$this->codename . '_settings']['status'] 			= $this->request->post['module_d_sub_category_showcase_status'];
			$settings[$this->codename . '_settings']['show_images'] 	= $this->request->post['show_images'];					
			$settings[$this->codename . '_settings']['count_products']  = $this->request->post['count_products'];	
			$settings[$this->codename . '_settings']['selector'] 		= $this->request->post['selector'];	

			if( isset($this->request->post['custom_styles']) ){
				$settings[$this->codename . '_settings']['custom_styles'] = $this->request->post['custom_styles'];
			}

			$this->model_setting_setting->editSetting($this->codename, $settings);
			$this->response->redirect($this->model_extension_d_opencart_patch_url->getExtensionLink('module'));
		}

		$module_settings = $this->model_setting_setting->getSetting('d_sub_category_showcase');
		$data['name'] = !empty($module_info['name']) ? $module_info['name'] : "";

		$data['text_edit'] 			= $this->language->get('text_edit');
		$data['text_enabled'] 		= $this->language->get('text_enabled');
		$data['text_disabled'] 		= $this->language->get('text_disabled');
		$data['text_save_and_stay'] = $this->language->get('text_save_and_stay');
		
		$data['entry_show_images'] 	  = $this->language->get('entry_show_images');
		$data['show_images_enabled']  = $this->language->get('show_images_enabled');
		$data['show_images_disabled'] = $this->language->get('show_images_disabled');
		
		$data['entry_count_products'] 	 = $this->language->get('entry_count_products');
		$data['count_products_enabled']  = $this->language->get('count_products_enabled');
		$data['count_products_disabled'] = $this->language->get('count_products_disabled');
		
		$data['entry_selector'] 	 = $this->language->get('selector');
		$data['settings'] = $module_settings[$this->codename . "_settings"];
		$data['entry_status'] 		= "Status";

		$data['text_save_and_stay'] = $this->language->get('text_save_and_stay');
		$data['button_save'] 		= $this->language->get('button_save');
		$data['button_cancel'] 		= $this->language->get('button_cancel');

		//breadcrumbs
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->model_extension_d_opencart_patch_url->link('common/dashboard')
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->model_extension_d_opencart_patch_url->getExtensionLink('module')
		);

		$data['breadcrumbs'][] = array(
			'text' => 'Sub category wall',
			'href' => $this->model_extension_d_opencart_patch_url->link('extension/module/d_sub_category_showcase')
		);

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$url_params = array();

		if (isset($this->response->get['store_id'])) {
			$url_params['store_id'] = $this->store_id;
		}

		if (isset($this->response->get['config'])) {
			$url_params['config'] = $this->response->get['config'];
		}

		$url = ((!empty($url_params)) ? '&' : '') . http_build_query($url_params);
		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->model_extension_d_opencart_patch_url->link('extension/module/d_sub_category_showcase', $url);
		} else {
			$data['action'] = $this->model_extension_d_opencart_patch_url->link('extension/module/d_sub_category_showcase', '&module_id=' . $this->request->get['module_id']);
		}

        $data['cancel'] = $this->model_extension_d_opencart_patch_url->ajax('extension/module');
		$data['setup_link'] = $this->model_extension_d_opencart_patch_url->ajax($this->route . '/setup');		
        $data['token'] = $this->model_extension_d_opencart_patch_user->getToken();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/d_sub_category_showcase', $data));
	}

	public function install()
	{
		$this->load->model('setting/setting');
		$settings[$this->codename . '_settings']['status'] 			= '1';
		$settings[$this->codename . '_settings']['show_images' ] 	= '1';			
		$settings[$this->codename . '_settings']['count_products' ] = '1';
		$settings[$this->codename . '_settings']['selector' ] 		= 'h2';

		$this->model_setting_setting->editSetting($this->codename, $settings);

		$this->load->model('extension/module/d_event_manager');
		$this->model_extension_module_d_event_manager->addEvent($this->codename, 'catalog/view/' . 'product/category/' . 'after', 'extension/module/' . $this->codename . '/view_category_after');
	
	}

	public function uninstall(){
        // if ($this->d_event_manager) {
            $this->load->model('extension/module/d_event_manager');
            $this->model_extension_module_d_event_manager->deleteEvent($this->codename);
        // }
    }
}
