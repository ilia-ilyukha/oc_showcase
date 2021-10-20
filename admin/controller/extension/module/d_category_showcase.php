<?php
class ControllerExtensionModuleDCategoryShowcase extends Controller {
	private $error = array(); 
	public $heading_title = "Category wall";
	// public $codename = "d_category_wall";
	public $codename = "d_category_showcase";
	private $route = 'extension/module/d_category_showcase';

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
        // $this->extension = json_decode(file_get_contents(DIR_SYSTEM . 'library/d_shopunity/extension/d_category_wall.json'), true);
        if (isset($this->request->get['store_id'])) {
            $this->store_id = $this->request->get['store_id'];
        }
    }

    public function install()
    {
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting($this->codename);

        if(empty($settings)){
            $this->load->model('extension/module/d_event_manager');
            $this->model_extension_module_d_event_manager->addEvent($this->codename, 'catalog/view/common/footer/before', 'extension/module/' . $this->codename . '/view_footer_after');
        }
    }

    public function uninstall(){
        // if ($this->d_event_manager) {
            $this->load->model('extension/module/d_event_manager');
            $this->model_extension_module_d_event_manager->deleteEvent($this->codename);
            $this->model_extension_module_d_event_manager->deleteEvent($this->codename);
        // }
    }

	public function setup(){
		$this->load->model('localisation/language');
        $this->load->language('extension/module/'.$this->codename);

		// $this->load->model('extension/d_category_wall');
		// $this->model_extension_d_category_wall->createModule('Category wall');

        $this->load->model('setting/setting');
		$this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        $data = array();
        foreach ($languages as $language)
        {
            $data['title'][$language['language_id']] = 'You will also like';
        }

        $data['name'] = 'd_category_showcase';
        $data['status'] = 1;
        $data['limit'] = 4;
        $data['title'][''] = $this->config->get('config_language_id');
        $data['image_height'] = 200;
        $data['image_width'] = 200;
        
		$this->model_extension_d_opencart_patch_module->addModule($this->codename, $data);
		
        $module_id = $this->db->getLastId();
		
        $this->session->data['success'] = $this->language->get('success_setup');
        $this->response->redirect($this->model_extension_d_opencart_patch_url->link($this->route.'&module_id='.$module_id));
	}

    public function isSetup(){
        $this->load->model('extension/d_opencart_patch/module');
        $module = $this->model_extension_d_opencart_patch_module->getModulesByCode($this->codename);
        if ($module) {
            return true;
        }
        return false;
    }

	public function index() {

        if($this->d_twig_manager){
            $this->load->model('extension/module/d_twig_manager');
            $this->model_extension_module_d_twig_manager->installCompatibility();
        }

        if($this->d_event_manager){
            $this->load->model('extension/module/d_event_manager');
            $this->model_extension_module_d_event_manager->installCompatibility();
        }

        if ($this->d_admin_style){
            $this->load->model('extension/d_admin_style/style');
            $this->model_extension_d_admin_style_style->getStyles('light');
        }

        if ($this->d_validator) {
            $this->load->model('extension/d_shopunity/d_validator');
            $this->model_extension_d_shopunity_d_validator->installCompatibility();
        }
        
		$this->load->language($this->route);
		$this->document->setTitle($this->heading_title);
 
		$this->load->model('setting/setting');

		$data['heading_title'] = $this->heading_title;

		if ($this->d_admin_style) {
            $this->load->model('extension/d_admin_style/style');
            $this->model_extension_d_admin_style_style->getStyles('light');
        }

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$settings["name"] = $this->request->post["d_category_showcase_name"];
			$settings["status"] = $this->request->post["module_d_category_showcase_status"];
			$settings["product_category"] = $this->request->post["product_category"];

			if (!isset($this->request->get['module_id'])) {
                $this->model_extension_d_opencart_patch_module->addModule('d_category_showcase', $settings);
            } else {
                $this->model_extension_d_opencart_patch_module->editModule($this->request->get['module_id'], $settings);
            }
            $this->cache->delete('product');
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->model_extension_d_opencart_patch_url->getExtensionLink('module'));
        }

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $module_info = $this->model_extension_d_opencart_patch_module->getModule($this->request->get['module_id']);
        } else {
            $module_info = array();
        }

	  	$data['name'] = !empty($module_info['name']) ? $module_info['name'] : "";
	  	$data['status'] = !empty($module_info['status']) ? $module_info['status'] : 0;

		// Categories
		$this->load->model('catalog/category');
	  	$categories = !empty($module_info['product_category']) ? $module_info['product_category'] : [];
		 


		foreach ($categories as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);
			
			if ($category_info) {
				$data['product_categories'][] = array(
					'category_id' => $category_info['category_id'],
					'name'        => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
				);
			}
		}
		
		$data['text_edit'] = 'Edit';
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_status'] = "Status";

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['entry_category'] = $this->language->get('entry_category');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

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
        
		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title_main'),
				'href' => $this->model_extension_d_opencart_patch_url->link('extension/module/d_category_showcase')
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title_main'),
				'href' => $this->model_extension_d_opencart_patch_url->link('extension/module/d_category_showcase', '&module_id=' . $this->request->get['module_id'])
			);
		}

		$url_params = array();
        $url = '';

        if (isset($this->response->get['store_id'])) {
            $url_params['store_id'] = $this->store_id;
        }

        if (isset($this->response->get['config'])) {
            $url_params['config'] = $this->response->get['config'];
        }

        $url = ((!empty($url_params)) ? '&' : '') . http_build_query($url_params);
		if (!isset($this->request->get['module_id'])) {
            $data['action'] = $this->model_extension_d_opencart_patch_url->link('extension/module/d_category_showcase', $url);
        } else {
            $data['action'] = $this->model_extension_d_opencart_patch_url->link('extension/module/d_category_showcase', '&module_id=' . $this->request->get['module_id']);
        }


		if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($module_info['status'])) {
            $data['status'] = $module_info['status'];
        } else {
            $data['status'] = '';
        }

        $data['setup'] = $this->isSetup();
		$data['setup_link'] = $this->model_extension_d_opencart_patch_url->ajax($this->route . '/setup');
        $data['token'] = $this->model_extension_d_opencart_patch_user->getToken();
        $data['cancel'] = $this->language->get('button_cancel');
        $data['category_url'] = $this->model_extension_d_opencart_patch_url->ajax('catalog/category/autocomplete');

        $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->model_extension_d_opencart_patch_load->view('extension/module/d_category_showcase', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/' . $this->codename)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}