<?php
class ControllerExtensionModuleDCategoryShowcase extends Controller {
    
    public $codename = "d_category_showcase";

    public function index($setting) {
        $this->d_opencart_patch = (file_exists(DIR_SYSTEM . 'library/d_shopunity/extension/d_opencart_patch.json'));
        if ($this->d_opencart_patch) {
            $this->load->model('extension/d_opencart_patch/load');
        }
        $this->load->language('module/' . $this->codename);

        $data['heading_title'] = $this->language->get('heading_title');

        if (isset($this->request->get['path'])) {
            $parts = explode('_', (string) $this->request->get['path']);
        } else {
            $parts = array();
        }

        if (isset($parts[0])) {
            $data['category_id'] = $parts[0];
        } else {
            $data['category_id'] = 0;
        }

        if (isset($parts[1])) {
            $data['child_id'] = $parts[1];
        } else {
            $data['child_id'] = 0;
        }

        $this->load->model('catalog/category');
        $this->load->model('catalog/product');

        $data['categories'] = array();

        if(!isset($setting['product_category'])){
            return false;
        }
        foreach($setting['product_category'] as $category ){
            $categories[] = $this->model_catalog_category->getCategory($category);
        }

        $this->load->model('tool/image');

        foreach ($categories as $category) {

            $img_w = isset($setting['width']) ? $setting['width'] : '286';
            $img_h = isset($setting['height']) ? $setting['height'] : '244';
            
            if ($category['image']) {
                $image = $this->model_tool_image->resize($category['image'], $img_w, $img_h);
            } else {
                $image = $this->model_tool_image->resize('placeholder.png', $img_w, $img_h);
            }

            $data['categories'][] = array(
                'name' => $category['name'],
                'image' => $image,
                'href' => $this->url->link('product/category', 'path=' . $category['category_id'])
            );
        }
        
        return $this->model_extension_d_opencart_patch_load->view('extension/module/d_category_showcase', $data);
    }
  
}