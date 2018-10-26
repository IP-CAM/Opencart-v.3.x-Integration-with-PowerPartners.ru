<?php 
class ModelExtensionModulePower extends Model {
	public $url = 'http://api.powerpartners.ru/';
	public $api = 'v2.0/';
	
	public $token = '';
   
	
	public function getPayment($methods){
		
			$products = $this->cart->getProducts();
 
			foreach ($products as $product) {
				if($this->checkProduct($product['product_id'])){
			 
					
					
					$data['power']['title'] = "Оплата согласуется с менеджером" ;
					$data['power']['code'] = "power" ;
					$data['power']['terms'] = "" ;
					$data['power']['sort_order'] = "1" ;
			 
		 	
					return $data;
					 
				 
				}
			}	
			
			return $methods;
	}
	
	public  function checkValid( ){
		
		if(!$this->check_column_exists('product', 'power_id')){
			return true;
			
		}
		
		$last_run = $this->config->get('module_power_last_feed');
		if(empty($last_run)){
			$this->zeroQty();
			
		}
		
		$delta =time()- strtotime($last_run);
	 
		if($delta > 60 * 60 * 48){
			
			$this->zeroQty();
		}
		
		return true;
	}
	public  function zeroQty( ){
		$this->db->query("UPDATE `" . DB_PREFIX . "product` SET `quantity` = 0,  `status` = 0 WHERE `power_id`  <> '0'");
		
		return true;
	}
	
	public function getShipping($methods){
		
			$products = $this->cart->getProducts();

			foreach ($products as $product) {
				if($this->checkProduct($product['product_id'])){
				 
					
					$data['power']['title'] = "Доставка согласуется с менеджером" ;
					$data['power']['quote']['power']['code'] = "power.power" ;
					$data['power']['quote']['power']['title'] =  "Доставка согласуется с менеджером" ;
					$data['power']['quote']['power']['cost'] =  "0.00" ;
					$data['power']['quote']['power']['tax_class_id'] = "9" ;
					$data['power']['quote']['power']['text'] = "0р." ;
					$data['power']['sort_order']  = "0" ;
					$data['power']['error']  = false ;
					
					return $data;
					
					
				}
			}	
			
			
			return $methods;
	}

	
	public function sendOrder($order_id){
		
 
		$status = $this->config->get('module_power_status');
		if($status !=1){
            return;
		}
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id); 
		
 
		
		$token = $this->config->get('module_power_token');
 
		
		if(empty($token)){
            return;
		}
		$method =  'orders/new';
		$url = $this->url . $this->api . $method ;
		
		
		//$order_data['order_ext_id'] = $order_info['order_id'];
		$order_data['fname'] = $order_info['shipping_firstname'];
		$order_data['lname'] = $order_info['shipping_lastname'];
		$order_data['mname'] = '';
		$order_data['note'] =  $order_info['comment'];
		$order_data['customer'] =  1;
		$order_data['email'] = $order_info['email'];
		$order_data['phone'] = $order_info['telephone'];
		$order_data['address'] = $order_info['shipping_address_1'];
		$order_data['address'] .= ' ' . $order_info['shipping_address_2'];
		$order_data['city'] = $order_info['shipping_city'];

	//	$order_data['postcode'] = $order_info['shipping_postcode'];
		//$order_data['country'] = $order_info['shipping_country'];
		//$order_data['region'] = $order_info['shipping_zone'];
		//$order_data['total'] = $order_info['total'];
		//$order_data['date_added'] = $order_info['date_added'];
		//$order_data['date_modified'] = $order_info['date_modified'];
		
		$order_data['goods'] = []; 
		
		/*
{
    "order": {
        "fname": "Иван",
        "lname": "Иванов",
        "mname": "Иваныч",
        "email": "ivanov@mail.ru",
        "phone": "+7(123)456-78-90",
        "note": "Привезти до 18:00",
        "customer": 1,      // Покупатель: физ.лицо/юр.лицо
        "paymethod": 1,      // Способ оплаты
        "recievemethod": 0,     // Способ получения: курьер/самовывоз/ТК
        "address": "Варшавская, 63",  // Адрес для курьерской доставки
        "city": "",       // Город, если доставка Транспортной Компанией
        "emoney_details": null,    // Реквизиты для выставления счета, при оплате электронными деньгами
        "individual_address": "",   // Адрес прописки физ.лица при доставке транспортной компанией (требование ТК)
        "transport_company_id": null,  // id транспортной компании
        "legal_name": null,     // Для юр.лиц: Название юр.лица
        "legal_address": null,    // Для юр.лиц: юридический адрес
        "legal_inn": null,     // Для юр.лиц: ИНН
        "legal_kpp": null,     // Для юр.лиц: КПП
        "legal_rs": null,     // Для юр.лиц: Рассчетный счет
        "legal_bank": null,     // Для юр.лиц: Название банка, город
        "legal_ks": null,     // Для юр.лиц: Кор.счет
        "legal_bik": null,     // Для юр.лиц: БИК банка
        "goods": [
            {
                "code": "kotel-400",
                "quantity": 1
            }
        ]
    }
}*/
		
		$products_info = $this->model_checkout_order->getOrderProducts($order_id);  
		
		foreach($products_info as $order_product){
			$line['quantity'] = $order_product['quantity'];
			$line['code']= $this->checkProduct($order_product['product_id']);
 
			$order_data['goods'][] = $line;
		}
		
		
		$data['order'] = $order_data;
		
		$request = $this->curlFunction($url,  $data, true , $token );
 
		if($this->isJson($request)){
			//log
	 
			file_put_contents(DIR_LOGS.'power.log', 'new order '.$order_id.PHP_EOL , FILE_APPEND);
			file_put_contents(DIR_LOGS.'power.log', 'requested:' . PHP_EOL , FILE_APPEND);
			file_put_contents(DIR_LOGS.'power.log', json_encode($order_data)  . PHP_EOL , FILE_APPEND);
			file_put_contents(DIR_LOGS.'power.log', 'responsed:' . PHP_EOL , FILE_APPEND);
			file_put_contents(DIR_LOGS.'power.log',   $request. PHP_EOL , FILE_APPEND);
			file_put_contents(DIR_LOGS.'power.log', PHP_EOL , FILE_APPEND);
			//update order
			$json = json_decode($request);
			
			if(!empty( $json->order->id ) && !empty( $json->order->status )){
				$ext_id  = $json->order->id;
				$status  = $json->order->status;
				$this->db->query("UPDATE `" . DB_PREFIX . "order` SET power_id = '".$this->db->escape($ext_id)."', power_status = '".$this->db->escape($status)."' WHERE `order_id` = '".(int)$order_info['order_id']."'");
				
			}else{
				file_put_contents(DIR_LOGS.'power.log', 'error response order '.$order_id.PHP_EOL , FILE_APPEND);
				file_put_contents(DIR_LOGS.'power.log', 'responsed:' . PHP_EOL , FILE_APPEND);
				file_put_contents(DIR_LOGS.'power.log',   $request. PHP_EOL , FILE_APPEND);
				file_put_contents(DIR_LOGS.'power.log', PHP_EOL , FILE_APPEND);
				
			}
		}else{
			file_put_contents(DIR_LOGS.'power.log', 'error response order '.$order_id.PHP_EOL , FILE_APPEND);
			file_put_contents(DIR_LOGS.'power.log', PHP_EOL , FILE_APPEND);
		}
	 
		
	}
		
	private function curlFunction($url,  $data, $post , $token) {
			
 	
		$data['token'] = $token;
 
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);	// возвращает веб-страницу
		curl_setopt($ch, CURLOPT_HEADER, 0);			// не возвращает заголовки
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);	// переходит по редиректам
		curl_setopt($ch, CURLOPT_ENCODING, "");			// обрабатывает все кодировки
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120); 	// таймаут соединения
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);			// таймаут ответа
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);		// останавливаться после 10-ого редиректа
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		 
	 
		
		if($post){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		}

		$content = curl_exec( $ch );
		curl_close( $ch );
		
		return $content;
	} 
	
	public function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

 public function getProductDescription($product_id) {
		$query = $this->db->query("SELECT DISTINCT  *  FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)  
		WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'  ");

		if ($query->num_rows) {
			$row =  $query->row;
			$text =  $row['description'];
				
			
			if(!empty( $row['manual'])){
				$text .= '<br>';
				$text .= '<br>';	
				$text .= '<h3>'.'Инструкция пользователя '.$row['name'] . '</h3>';
				$text .= '<p><a target="blank" href="image/'.$row['manual'].'">'.'Скачать'.'</a></p>';
			}
				
			if(!empty( $row['certificate'])){
				$text .= '<br>';
				$text .= '<br>';	
				$text .= '<h3>'.'Сертификат '.$row['name'] .'</h3>';
				$text .= '<p><a target="blank"  href="image/'.$row['certificate'].'">'.'Скачать'.'</a></p>';
			}

			return $text;
		} else {
			return false;
		}
	}


	public function checkProduct($product_id){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE product_id = '".$this->db->escape($product_id)."'");
			
		$row = $query->row;
		if(!empty($row['power_id'])){
			return $row['power_id'];
		}
		
		return false;
	}
	
  
 
}
?>