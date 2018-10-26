<?php
class ControllerExtensionModulePower extends Controller { 
	private $error = array();
 
	
	public function install() {
		$this->load->model('extension/module/power');
		$this->load->model('setting/store');
		$this->model_extension_module_power->install();
	}
	
	public function uninstall() {
		$this->load->model('setting/store');
        $this->load->model('extension/module/power');
        $this->model_extension_module_power->uninstall();
	}
 
	public function index() {
		$this->load->language('extension/module/power');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
		 
 

			$this->model_setting_setting->editSetting('module_power', $this->request->post);			
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL'));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_crosses'] = $this->language->get('text_crosses');
		$data['text_crosses_descr'] = $this->language->get('text_crosses_descr');
		$data['text_cоunt'] = $this->language->get('text_cоunt');
		$data['text_get_cross'] = $this->language->get('text_get_cross');
		$data['btn_analyze'] = $this->language->get('btn_analyze');
		$data['text_analyze_descr'] = $this->language->get('text_analyze_descr');
		$data['text_select'] = $this->language->get('text_select');
		$data['text_deselect'] = $this->language->get('text_deselect');

		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_inn'] = $this->language->get('entry_inn');
		
		$data['entry_url'] = $this->language->get('entry_url');
		$data['entry_login'] = $this->language->get('entry_login');
		$data['entry_password'] = $this->language->get('entry_password');
		$data['entry_shopid'] = $this->language->get('entry_shopid');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		
		
 
		$data['power_connection'] = $this->language->get('power_connection');
		$data['power_about'] = $this->language->get('power_about');
		$data['power_settings'] = $this->language->get('power_settings');
		$data['power_shop'] = $this->language->get('power_shop');
		$data['power_btn_check'] = $this->language->get('power_btn_check');
		$data['power_entry_check'] = $this->language->get('power_entry_check');
		$data['power_payment_systems'] = $this->language->get('power_payment_systems');
		$data['power_dont_use'] = $this->language->get('power_dont_use');
		$data['power_cron'] = $this->language->get('power_cron');
		$data['power_status_heading'] = $this->language->get('power_status_heading');
	 
		 
		
		$data['check_url'] = $this->url->link('extension/module/power/check', 'user_token=' . $this->session->data['user_token'], 'SSL');

		 
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['error_warning'];
		} else {
			$data['error_warning'] = '';
		}
		  
		 
		if (isset($this->error['error_token'])) {
			$data['error_token'] = $this->error['error_token'];
		} else {
			$data['error_token'] = '';
		}
		 
 
		define('ORDER_STATUS_PROCESSING', 10);
		define('ORDER_STATUS_PREORDER', 15);
		define('ORDER_STATUS_INPAYMENT', 20);
		define('ORDER_STATUS_INDELIVERY', 25);
		define('ORDER_STATUS_INPICKUP', 30);
		define('ORDER_STATUS_FINISHED', 35);
		define('ORDER_STATUS_COMPLETED', 40);
		define('ORDER_STATUS_CANCELED', 45);
		define('ORDER_STATUS_RETURNED', 50);

		$STATUS_STR = array(
		  ORDER_STATUS_PROCESSING => 'В обработке',
		  ORDER_STATUS_PREORDER => 'Предзаказ',
		  ORDER_STATUS_INPAYMENT => 'Ожидает оплаты',
		  ORDER_STATUS_INDELIVERY => 'В доставке',
		  ORDER_STATUS_INPICKUP => 'Ожидает выдачи',
		  ORDER_STATUS_FINISHED => 'Выполнен',
		  ORDER_STATUS_COMPLETED => 'Выполнен',
		  ORDER_STATUS_CANCELED => 'Отменен',
		  ORDER_STATUS_RETURNED => 'Возврат покупки',
		);
		if (isset($this->request->post['module_power_status_oc'])) {
			$data['module_power_status_oc'] = $this->request->post['module_power_status_oc'];
		} else {
			$data['module_power_status_oc'] = $this->config->get('module_power_status_oc');
		}

		$data['module_power_status_power'] = $STATUS_STR;
					
 
		//default
		if(empty($data['module_power_status_oc'][10])){
			$data['module_power_status_oc'][10] = 2;
		}	
		if(empty($data['module_power_status_oc'][15])){
			$data['module_power_status_oc'][15] = 1;
		}	
		if(empty($data['module_power_status_oc'][20])){
			$data['module_power_status_oc'][20] = 1;
		}	
		if(empty($data['module_power_status_oc'][25])){
			$data['module_power_status_oc'][25] = 1;
		}	
		if(empty($data['module_power_status_oc'][30])){
			$data['module_power_status_oc'][30] = 3;
		}	
		if(empty($data['module_power_status_oc'][35])){
			$data['module_power_status_oc'][35] = 5;
		}	
		if(empty($data['module_power_status_oc'][40])){
			$data['module_power_status_oc'][40] = 5;
		}
		if(empty($data['module_power_status_oc'][45])){
			$data['module_power_status_oc'][45] = 7;
		}
		if(empty($data['module_power_status_oc'][50])){
			$data['module_power_status_oc'][50] = 13;
		} 
		
		 
		$url = new Url(HTTP_CATALOG, $this->config->get('config_secure') ? HTTP_CATALOG : HTTPS_CATALOG);
		//$data['cron'] =  DIR_APPLICATION . 'controller/extension/power_cron/update_orders.php';
		//$data['cron2'] =  DIR_APPLICATION . 'controller/extension/power_cron/update_products.php';
		$data['cron3'] =  DIR_APPLICATION . 'controller/extension/power_cron/update.php';
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/power', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);
		
		


		$data['action'] = $this->url->link('extension/module/power', 'user_token=' . $this->session->data['user_token'], 'SSL');

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL');
		
	 
		
		
		if (isset($this->request->post['module_power_token'])) {
			$data['module_power_token'] = $this->request->post['module_power_token'];
		} else {
			$data['module_power_token'] = $this->config->get('module_power_token');
		}
		
		
		if (isset($this->request->post['module_power_order_status_id'])) {
			$data['module_power_order_status_id'] = $this->request->post['module_power_order_status_id'];
		} else {
			$data['module_power_order_status_id'] = $this->config->get('module_power_order_status_id');
		}
		
 
		
		if (isset($this->request->post['module_power_category'])) {
			$data['module_power_category'] = $this->request->post['module_power_category'];
		} else {
			$data['module_power_category'] = $this->config->get('module_power_category');
		}
		
		if (isset($this->request->post['module_power_goods'])) {
			$data['module_power_goods'] = $this->request->post['module_power_goods'];
		} else {
			$data['module_power_goods'] = $this->config->get('module_power_goods');
		}
		
		
		if (isset($this->request->post['module_power_in_stock'])) {
			$data['module_power_in_stock'] = $this->request->post['module_power_in_stock'];
		} else {
			$data['module_power_in_stock'] = $this->config->get('module_power_in_stock');
		}
		if(empty($data['module_power_in_stock'])){
			$data['module_power_in_stock'] = 7;
		} 	
		
		if (isset($this->request->post['module_power_out_of_stock'])) {
			$data['module_power_out_of_stock'] = $this->request->post['module_power_out_of_stock'];
		} else {
			$data['module_power_out_of_stock'] = $this->config->get('module_power_out_of_stock');
		}
		if(empty($data['module_power_out_of_stock'])){
			$data['module_power_out_of_stock'] = 5;
		} 

		
		if (isset($this->request->post['module_power_length_class_id'])) {
			$data['module_power_length_class_id'] = $this->request->post['module_power_length_class_id'];
		} else {
			$data['module_power_length_class_id'] = $this->config->get('module_power_length_class_id');
		}
		if(empty($data['module_power_length_class_id'])){
			$data['module_power_length_class_id'] = 2;
		} 	
		
		if (isset($this->request->post['module_power_weight_class_id'])) {
			$data['module_power_weight_class_id'] = $this->request->post['module_power_weight_class_id'];
		} else {
			$data['module_power_weight_class_id'] = $this->config->get('module_power_weight_class_id');
		}
		if(empty($data['module_power_weight_class_id'])){
			$data['module_power_weight_class_id'] = 1;
		} 	


		if (isset($this->request->post['module_power_password'])) {
			$data['module_power_password'] = $this->request->post['module_power_password'];
		} else {
			$data['module_power_password'] = $this->config->get('module_power_password');
		}
 
		$data['module_power_last_feed'] = $this->config->get('module_power_last_feed');
 
		

		if (isset($this->request->post['module_power_status'])) {
			$data['module_power_status'] = $this->request->post['module_power_status'];
		} else {
			$data['module_power_status'] = $this->config->get('module_power_status');
		}
		
		$this->load->model('extension/module/power');
 		
		$data['update_feed_url'] = $this->url->link('extension/module/power/feed_download', 'user_token=' . $this->session->data['user_token'], 'SSL');
	 
		
			
		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses(); 
		
		$this->load->model('localisation/stock_status');
		$data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses(); 
		$this->load->model('localisation/length_class');
		$data['length_classes'] = $this->model_localisation_length_class->getLengthClasses(); 
		$this->load->model('localisation/weight_class');
		$data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses(); 


		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/power', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/power')) {
			$this->error['warning'] = $this->language->get('error_permission');
		
		}
		if(empty( $this->request->post['module_power_token'] )){
			$this->error['error_token'] =  'Нет токена';
		}
	 
		return !$this->error;
	}
	
 
	
		
	/*
	*	DOWNLOAD FEED MANUAL
	*/
	public function feed_download(){
		 
		$token = $this->config->get('module_power_token');
	 
		if(empty($token)){
			$data['success'] = false;
			$data['message'] = 'No token';
			echo json_encode($data);
			exit;
		}
	 
		
		$this->load->model('extension/module/power');
		$result = $this->model_extension_module_power->request($token, 'shops/goods');
		
		
		if(!empty($result->errors)){
		 
			//todo log 
			return $result->errors[0]->msg;
		}
		
		
		echo '<pre>';
		$categories = $result->categories;
		$goods = $result->goods;
		$setting_category = $this->config->get('module_power_category');
		
 
		$import_cats = $this->model_extension_module_power->import_categories($categories);
 
	 
		$setting_goods = $this->config->get('module_power_goods');
 
		$import_goods = $this->model_extension_module_power->import_goods($goods );
 
		
	
		
	}
	
	 /*
	*	UPDATE ORDERS
	*/
	public function update_orders(){
		//$login = $this->request->post['login'];
		//$pass = $this->request->post['pass'];
		$token = $this->config->get('module_power_token');
		$store_id = (int) $this->config->get('config_store_id');
		
		if(empty($token)){
			$data['success'] = false;
			$data['message'] = 'No token';
			echo json_encode($data);
			exit;
		}
	 
		$data = [];
		$this->load->model('extension/module/power');
		$this->load->model('sale/order');

		$orders = $this->model_extension_module_power->getOrdersQueue( );

		foreach($orders as $id => $status){
			$data['orders'][] = $id;
		}
		if(empty($data['orders'])){
		 	return;
		}
	 
		 
		$result = $this->model_extension_module_power->request($token, 'orders/info',$data);
		
		if(!empty($result->errors)){
		 
			//todo log 
			return $result->errors[0]->msg;
		}
		
		echo '<pre>';
		
		if(empty($result->orders)){
			$result->orders = [];
			if(isset($result->order)){
				$result->orders[] = $result->order;
			}
		}
 
 
		foreach($result->orders as $order){

			if($order->status == $orders[$order->id]){
				echo 'Status not changed'. PHP_EOL;
				continue;
			}
	 
			$order_id = $this->model_extension_module_power->get_local_order($order->id);
			$order_status_id = $this->model_extension_module_power->get_local_status($order->status);
			$power_status = $order->status;
		 	echo 'Adding history:' . $orders[$order->id] . ' to '. $order->status . PHP_EOL;
	 
			$result = $this->model_extension_module_power-> addOrderHistory($order_id, $order_status_id , $power_status )  ;
			
		} 
	}
	
	
	
	
}