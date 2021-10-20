<?php
class ControllerExtensionModuleDSubCategoryShowcase extends Controller {
  public $codename = "d_sub_category_showcase";
  
  public function view_category_after(&$route, &$data, &$output)
    {
        $this->load->model('setting/setting');
		$settings =  $this->model_setting_setting->getSetting('d_sub_category_showcase');
        $settings = $settings[$this->codename . "_settings"];
        if(!$settings["status"]){
            return false;
        }

        $this->load->model('catalog/category');

        if (isset($this->request->get['path'])) {
            $parts = explode('_', (string) $this->request->get['path']);
        } else {
            $parts = array();
        }
        $data['category_id'] = $parts[0];
        if(count($parts) > 1){
            $data['child_id'] = array_pop($parts);
        }

        $category_id = isset($data['child_id']) ? $data['child_id'] : $data['category_id'];
        $children = $this->model_catalog_category->getCategories($category_id);

        //If category hasn't got any subcategories, stop script
        if(count($children) < 1) {
            return false;
        } 

        foreach ($children as $child) {
            $filter_data = array(
                'filter_category_id'  => $child['category_id'],
                'filter_sub_category' => true
            );

            $img_w = isset($setting['width']) ? $setting['width'] : '286';
            $img_h = isset($setting['height']) ? $setting['height'] : '244';
            
            if ($child['image']) {
                $image = $this->model_tool_image->resize($child['image'], $img_w, $img_h);
            } else {
                $image = $this->model_tool_image->resize('placeholder.png', $img_w, $img_h);
            }

            $children_data[] = array(
                'name'  => $child['name'],
                'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '_' . $child['category_id']),
                'image' => $image,
                'count_products' => $this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''
            );            
        }
                
        $data['children_data'] = $children_data;
        
        $data['show_images'] = $settings['show_images'];
        $data['count_products'] = $settings['count_products'];
        $data['custom_styles'] = $settings['custom_styles'];
        $selector = $settings['selector'];
        
        $html_dom = new d_simple_html_dom();
        $html_dom->load((string)$output, $lowercase = true, $stripRN = false, $defaultBRText = DEFAULT_BR_TEXT);

        $h2 =  $html_dom->find($selector, 0);
        $h2->outertext .= $this->load->view('extension/module/d_sub_category_showcase', $data);
        $html_dom->save();
        $output = (string)$html_dom;
    }
}