<?php
class ControllerExtensionPaymentPower extends Controller {
	public function index() {
		return $this->load->view('extension/payment/power');
	}

	public function confirm() {
		$json = array();
	 
		if ($this->session->data['payment_method']['code'] == 'power') {
			$this->load->model('checkout/order');

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('module_power_order_status_id'));
			$this->load->model('extension/module/power');
			$this->model_extension_module_power->sendOrder($this->session->data['order_id']);
			$json['redirect'] = $this->url->link('checkout/success');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}
	
	public function test() {
	 
			$this->load->model('extension/module/power');
			$this->model_extension_module_power->sendOrder(20);
		 
	}
	
	
}
