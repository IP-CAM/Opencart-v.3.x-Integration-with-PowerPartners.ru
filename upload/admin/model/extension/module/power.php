<?php 
class ModelExtensionModulePower extends Model {


	public $url = 'http://api.powerpartners.ru/';
	public $api = 'v2.0/';
	

	public $token = '';
	 public function install() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "power_attachment` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`date_created` DATETIME NULL DEFAULT NULL, 
			`date_modified` DATETIME NULL DEFAULT NULL, 
			`hash` VARCHAR(255) NULL DEFAULT NULL,
			`file` VARCHAR(255) NULL DEFAULT NULL,
			`type` VARCHAR( 20 )   NULL DEFAULT NULL,
			`product_id` INT(11) NULL DEFAULT NULL,
 
			 PRIMARY KEY (`id`))");
	 
				
		if(!$this->check_column_exists('product', 'power_id')){
			 $this->db->query("ALTER TABLE `" . DB_PREFIX . "product` ADD `power_id` VARCHAR(255) DEFAULT 0;");
		}
		if(!$this->check_column_exists('product', 'manual')){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "product` ADD `manual` VARCHAR(255) DEFAULT 0;");
		}
		if(!$this->check_column_exists('product', 'certificate')){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "product` ADD `certificate` VARCHAR(255) DEFAULT 0;");
		}
		if(!$this->check_column_exists('order', 'power_id')){
			 $this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD `power_id` VARCHAR(255) DEFAULT 0;");
		}
		if(!$this->check_column_exists('order', 'power_status')){
			 $this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD `power_status` VARCHAR(255) DEFAULT 0;");
		}
		if(!$this->check_column_exists('category', 'power_id')){
			 $this->db->query("ALTER TABLE `" . DB_PREFIX . "category` ADD `power_id` VARCHAR(255) DEFAULT 0;");
		}
		if(!$this->check_column_exists('category', 'name_hash')){
			 $this->db->query("ALTER TABLE `" . DB_PREFIX . "category` ADD `name_hash` VARCHAR(255) DEFAULT 0;");
		}
		if(!$this->check_column_exists('category', 'description_hash')){
			 $this->db->query("ALTER TABLE `" . DB_PREFIX . "category` ADD `description_hash` VARCHAR(255) DEFAULT 0;");
		}

		$store_id = (int) $this->config->get('config_store_id');
		$key = 'module_power_last_feed';
		$code = 'module_power';
		$value = '';
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int)$store_id . "' AND `key` = '" . $this->db->escape($key) . "'");
		$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
		 
		$this->db->query("UPDATE `" . DB_PREFIX . "modification` SET status=1 WHERE `name` LIKE'%power%'");
		$modifications = $this->load->controller('extension/modification/refresh');
	}
	
	public function uninstall() {
		
		
		
		
	 	$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "power_attachment`");
		
		
		if($this->check_column_exists('product', 'power_id')){
				 $this->db->query("ALTER TABLE `" . DB_PREFIX . "product` DROP  `power_id` ");
		}
		if($this->check_column_exists('product', 'manual')){
				 $this->db->query("ALTER TABLE `" . DB_PREFIX . "product` DROP  `manual` ");
		}
		if($this->check_column_exists('product', 'certificate')){
				 $this->db->query("ALTER TABLE `" . DB_PREFIX . "product` DROP  `certificate` ");
		}
		if($this->check_column_exists('order', 'power_id')){
				 $this->db->query("ALTER TABLE `" . DB_PREFIX . "order` DROP  `power_id` ");
		}
		if($this->check_column_exists('order', 'power_status')){
				 $this->db->query("ALTER TABLE `" . DB_PREFIX . "order` DROP  `power_status` ");
		}
		if($this->check_column_exists('category', 'power_id')){
				 $this->db->query("ALTER TABLE `" . DB_PREFIX . "category` DROP  `power_id` ");
		}
		if(!$this->check_column_exists('category', 'name_hash')){
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "category` DROP  `name_hash` ");
		}
		if(!$this->check_column_exists('category', 'description_hash')){
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "category` DROP  `description_hash` ");
		}	
	 
		 
		$this->db->query("UPDATE `" . DB_PREFIX . "modification` SET status=0 WHERE `name` LIKE'%power%'");
		$modifications = $this->load->controller('extension/modification/refresh');
	} 
	
	
	public function check_column_exists($table, $column){
		$result = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . $this->db->escape($table). "` WHERE  `field` =  '".$this->db->escape($column)."'");
		if(!empty($result->row)){
				return true;
		}
		return false;
		
	}

	
	public  function checkValid( ){
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
		$this->db->query("UPDATE `" . DB_PREFIX . "product` SET 'quantity' = 0,  'status' = 0 WHERE `power_id`  <> '0'");
		
		return true;
	}
	public function request($token, $method, $data = []) {
		$url = $this->url . $this->api . $method ;
		
		$request = $this->curlFunction($url,   $data, true , $token );
 
		if($this->isJson($request)){
			return(json_decode($request));
			
		}
		
		
		return false;	
	}
		
	public function import_goods($goods  ) {
		
		//$check attribute_group_id
		$this->load->model('setting/setting'); 
		$attribute_group_id = $this->check_attribute_group('Характеристики');
		$language_id = (int)$this->config->get('config_language_id') ;
		$store_id = (int) $this->config->get('config_store_id');
		
		//in stok status
		$in_stock = (int)$this->config->get('module_power_in_stock') ;
		//not in stock status
		$out_of_stock = (int)$this->config->get('module_power_out_of_stock') ;
		$weight_class_id = (int)$this->config->get('module_power_weight_class_id') ;
		$length_class_id = (int)$this->config->get('module_power_length_class_id') ;
		
		
		echo '<pre>';
		$product_ids = [];
		
		$this->load->model('catalog/product');
		
		$this->load->model('catalog/category');
		$this->load->model('catalog/manufacturer');
		foreach($goods as $feed_product){
				
		 
				
			echo 'Product '.$feed_product->code  . PHP_EOL;		
			if(empty($feed_product->article)){
				$feed_product->article =  $feed_product->code;
			}
			
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE power_id = '".$this->db->escape($feed_product->code)."'");
			$row = $query->row;

			if(!empty($row['product_id'])){
				$product_id =$row['product_id'];
				echo 'Updating '.$feed_product->code . PHP_EOL;		 
				$product =  $this->model_catalog_product->getProduct($row['product_id']);
	 
				//price
				$product['price'] = $feed_product->price;
				if(!empty($feed_product->special_price)){
					$product['price'] =$feed_product->special_price;
					
				}
				$product["model"] =  $feed_product->model;
				if(empty($product["model"])){
					$product["model"] = $feed_product->article;
				}
				//qty
				if($feed_product->quantity > 0 ){
					$product['quantity'] = $feed_product->quantity;
					$product['stock_status_id'] = $in_stock; 
				
				}else{
					$product['quantity'] = 0;
					$product['stock_status_id'] = $out_of_stock;  
				}
				$product["weight"]=  $feed_product->weight;
				$description["name"]=	 $feed_product->model;
				echo 'Set new price, qty, weight'.  PHP_EOL;		 
				$query = $this->db->query("UPDATE " . DB_PREFIX . "product SET 
						price = '".$this->db->escape($product['price'])."', 
						quantity = '".$this->db->escape($product['quantity'])."',  
						weight = '".$this->db->escape($product['weight'])."',  
						model = '".$this->db->escape($product['model'])."',  
						status = '1',  
						stock_status_id = '".$this->db->escape($product['stock_status_id'])."'
						WHERE  product_id = '".(int)$product_id."'");
				/*
				*	NAME
				*/
				$query = $this->db->query("UPDATE " . DB_PREFIX . "product_description SET 
					name = '".$this->db->escape($description["name"])."'
					WHERE  product_id = '".(int)$product_id."'");
				
				/*
				*	MANUFACTURER
				*/
				$manufacturer =  $this->model_catalog_manufacturer->getManufacturer($row['manufacturer_id']);
				if($manufacturer['name'] !=	$feed_product->trademark ){
					$manufacturer_id = $this->check_manufacturer($feed_product->trademark);	
					$query = $this->db->query("UPDATE " . DB_PREFIX . "product SET 
						manufacturer_id = '".(int)$manufacturer_id."' 
						WHERE  product_id = '".(int)$product_id."'");
				}
	 
				/*
				*	ATTRIBUTES
				*/
				echo 'attributes   '   . PHP_EOL;	
	 
				$height = 1;
				$width = 1;
				$length = 1;
				
				if(!empty($feed_product->height)){
					$height = $feed_product->height;
				}
				if(!empty($feed_product->width)){
					$width  = $feed_product->width;
				}
				if(!empty($feed_product->length)){
					$length = $feed_product->length;
				}
		
				
				
				$product["length"]=  $length;
				$product["width"]= $width;
				$product["height"]=  $height;
				$query = $this->db->query("UPDATE " . DB_PREFIX . "product SET 
							width = '".$this->db->escape( $width)."', 
							height = '".$this->db->escape( $height)."',  
							length = '".$this->db->escape($length )."',  
							length_class_id = '". (int)$length_class_id."' 
							WHERE  product_id = '".(int)$product_id."'");
							
							
				$current_attributes = [];
				if(!empty($feed_product->attributes)){
				 
					foreach($feed_product->attributes as $feed_attribute){
						$_attr = [];
						
					 
						$_attr['name'] = $feed_attribute->name;
						$_attr['attribute_id'] = $this->check_attribute($feed_attribute->name, $attribute_group_id) ;
						$_attr['product_id'] = $product_id;
						$_attr['language_id'] = $language_id;
						$_attr['text']  =  $feed_attribute->value  ;
						$this->update_attribute($_attr); 
						$current_attributes[] = $_attr['attribute_id'] ;
					} 	
				}
				//delete old attributes
				if(!empty($current_attributes)){
					$current_attributes_str = implode(',',$current_attributes );
					$current_attributes_str = "AND attribute_id NOT IN (".$this->db->escape($current_attributes_str) .")";
				}else{
					$current_attributes_str = '';
				}
				$query = $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute  WHERE  product_id = '".(int)$product_id."' $current_attributes_str ");

				/*
				*	CATEGORIES
				*/
				echo 'categories   '   . PHP_EOL;	
				$current_categories = $this->model_catalog_product->getProductCategories($product_id);
				$power_categories = [];
				foreach($feed_product->categories as $power_cat_id){
					//$local_cat_id =  $this->get_local_category($power_cat_id, $categories);
					
					$local_cat_id =  $this->get_category($power_cat_id);
					$power_categories[] = $local_cat_id ;
					if(!in_array($local_cat_id, $current_categories)){
						//add product to $local_cat_id		
						$this->deleteProductCategory($product_id, $local_cat_id);
						$this->addProductCategory($product_id, $local_cat_id);
					}
				}
				//backwards
				foreach($current_categories  as $local_cat_id){
					if(!in_array($local_cat_id, $power_categories)){
						//delete product from  $local_cat_id
						$this->deleteProductCategory($product_id, $local_cat_id);
					}
				}


				/*
				*	IMAGES
				*/
				$current_attachment = $this->get_attachment($row['product_id'], 'image');
				$current_md5 =[];	
				foreach($current_attachment as $row){
					$current_md5[] = $row['hash'];
				}

				$new_md5 = [];
				$flag_update = false;	
				if(!empty( $feed_product->images)){
					foreach( $feed_product->images as $image){
							echo 'Checking '.$image->md5_checksum. PHP_EOL;	
							$new_md5[] = $image->md5_checksum;
							if(!in_array($image->md5_checksum, $current_md5)){
								$flag_update = true;
							}
						
					}
				} 
				//delete not included
				foreach( $current_attachment as $row){
						echo 'Checking '. $row['hash']. PHP_EOL;	
						if(!in_array($row['hash'], $new_md5)){
							//delete image as old
							$flag_update = true;
						}
					
				}
				 
				if($flag_update){
						echo 'Need to update  images'.  PHP_EOL;	
						//unlink old 
						foreach($current_attachment as $file){
						 
							$path =  $file['file'];
							if(is_file($path)){
								echo 'Delete '.$path . PHP_EOL;	
								unlink($path);
							}
						}
						
						
						$downloads = [];
					
						$product["image"]  = '';
						$product["product_image"] = [];
						$query = $this->db->query("DELETE  FROM  " . DB_PREFIX . "product_image
						WHERE  product_id = '".(int)$product_id."'");
						$sort = 0;
						if(!empty( $feed_product->images)){
							foreach( $feed_product->images as $image){
								$save_path = DIR_IMAGE . 'catalog/power/image/' ;
								if (!file_exists($save_path)) {
									mkdir($save_path, 0777, true);
								}
								$save_path = $save_path. basename($image->url);
								$this->download( $image->url, $save_path);
								echo 'downloading image ' . $image->url . PHP_EOL;	
								$download['md5'] =  $image->md5_checksum;
								$download['file'] =  $save_path;
								$download['type'] =  'image';
								$downloads[] = $download;
								
								$_img["image"] =  'catalog/power/image/'  . basename($image->url);
								$_img["sort_order"] = $sort;
								if($sort==0){
									$product["image"]  ='catalog/power/image/'  . basename($image->url);
								}else{
									$product["product_image"][] = $_img;
									$query = $this->db->query("INSERT INTO " . DB_PREFIX . "product_image
									SET product_id = '".(int)$product_id."', image = '".$this->db->escape($_img["image"]) ."', sort_order = '".$this->db->escape($_img["sort_order"]) ."'");
									echo 'Saving  images'.  PHP_EOL;	
								}
								$sort++;
							}
							
						} 
						
						$query = $this->db->query("UPDATE " . DB_PREFIX . "product SET 
						image = '".$this->db->escape($product["image"] )."'
						WHERE  product_id = '".(int)$product_id."'");
						echo 'Saving main image '.  PHP_EOL;	
						
						//update downloads	
						echo 'Updating attachments '.  PHP_EOL;	
						$this->update_downloads($product_id, $downloads, 'image');	
						
				}
			 
				/*
				*	CERTIFICATE
				*/
				
				
				$current_attachment = $this->get_attachment($row['product_id'], 'certificate');
				$current_md5 =[];	
				foreach($current_attachment as $row){
					$current_md5[] = $row['hash'];
				}
			 
				$flag_update = false;
				if(!empty( $feed_product->certificate ) ){
					echo 'Checking '.$feed_product->certificate->md5_checksum. PHP_EOL;		
					$new_md5[] = $feed_product->certificate->md5_checksum;
					if(!in_array($feed_product->certificate->md5_checksum, $current_md5)){	
						$flag_update = true;
					}
				}
				
				if($flag_update){
						echo 'Need to update  certificate'.  PHP_EOL;	
						//unlink old 
						foreach($current_attachment as $file){
						 
							$path =  $file['file'];
							if(is_file($path)){
								echo 'Delete '.$path . PHP_EOL;	
								unlink($path);
							}
						}
						
						
						$downloads = [];

						$product["certificate"] =  '';
						if(!empty($feed_product->certificate->url)){
							$save_path = DIR_IMAGE . 'catalog/power/cert/' ;
							if (!file_exists($save_path)) {
								mkdir($save_path, 0777, true);
							}
							$save_path = $save_path. basename($feed_product->certificate->url) ;
							$this->download($feed_product->certificate->url,   $save_path);
							echo 'downloading certificate ' . $feed_product->certificate->url . PHP_EOL;	
							$download['md5'] =  $feed_product->certificate->md5_checksum;
							$download['file'] =  $save_path;
							$download['type'] =  'certificate';
							
							$downloads[] = $download;
							$product["certificate"] =  	 'catalog/power/cert/'  . basename($feed_product->certificate->url) ;
						}
						$query = $this->db->query("UPDATE " . DB_PREFIX . "product SET 
						certificate = '".$this->db->escape($product["certificate"] )."'
						WHERE  product_id = '".(int)$product_id."'");
						echo 'Saving certificate '.  PHP_EOL;	
						//update downloads	
						echo 'Updating attachments '.  PHP_EOL;	
						$this->update_downloads($product_id, $downloads, 'certificate');	
				}
				
				
				/*
				*	MANUAL
				*/
				$current_attachment = $this->get_attachment($row['product_id'], 'manual');
				$current_md5 =[];	
				foreach($current_attachment as $row){
					$current_md5[] = $row['hash'];
				}		 
				$flag_update = false;
				if(!empty( $feed_product->manual ) ){
					echo 'Checking '.$feed_product->manual->md5_checksum. PHP_EOL;	
					$new_md5[] = $feed_product->manual->md5_checksum;
					if(!in_array($feed_product->manual->md5_checksum, $current_md5)){	
						$flag_update = true;
					}
				}
				if($flag_update){
						echo 'Need to update  manual'.  PHP_EOL;	
						//unlink old 
						foreach($current_attachment as $file){
						 
							$path =  $file['file'];
							if(is_file($path)){
								echo 'Delete '.$path  . PHP_EOL;	
								unlink($path);
							}
						}
						
						
						$downloads = [];
					
					 
						
						$product["manual"] =  '';
						if(!empty($feed_product->manual->url)){
							$save_path = DIR_IMAGE . 'catalog/power/manual/' ;
							if (!file_exists($save_path)) {
								mkdir($save_path, 0777, true);
							}
							$save_path = $save_path.basename($feed_product->manual->url) ;
							$this->download($feed_product->manual->url ,   $save_path);
							echo 'downloading manual ' . $feed_product->manual->url . PHP_EOL;	
							$download['md5'] =  $feed_product->manual->md5_checksum; 
							$download['file'] =  $save_path;
							$download['type'] =  'manual';
							$downloads[] = $download;	
							$product["manual"] =  	 'catalog/power/manual/'  . basename($feed_product->manual->url) ;
						}
						
						
						
						$query = $this->db->query("UPDATE " . DB_PREFIX . "product SET 
						manual = '".$this->db->escape($product["manual"] )."'
						WHERE  product_id = '".(int)$product_id."'");
						echo 'Saving manual '.  PHP_EOL;	
						//update downloads	
						echo 'Updating attachments '.  PHP_EOL;	
						$this->update_downloads($product_id, $downloads, 'manual');	
				}
 
			}else{
				//else create	
				echo 'Creating' . PHP_EOL;		 
				
				$downloads = [];
				$manufacturer_id = $this->check_manufacturer($feed_product->trademark);	
				$product  = [];
				$product["image"]  = '';
				$product["product_image"] = [];
				$sort = 0;
				if(!empty( $feed_product->images)){
					foreach( $feed_product->images as $image){
						$save_path = DIR_IMAGE . 'catalog/power/image/' ;
						if (!file_exists($save_path)) {
							mkdir($save_path, 0777, true);
						}
						$save_path = $save_path. basename($image->url);
						$this->download( $image->url, $save_path);
						echo 'downloading image ' . $image->url . PHP_EOL;	
						$download['md5'] =  $image->md5_checksum;
						$download['file'] =  $save_path;
						$download['type'] =  'image';
						$downloads[] = $download;
						
						$_img["image"] =  'catalog/power/image/'  . basename($image->url);
						$_img["sort_order"] = $sort;
						if($sort==0){
							$product["image"]  ='catalog/power/image/'  . basename($image->url);
						}else{
							$product["product_image"][] = $_img;
							
						}
						$sort++;
					}
					
				}
				
				
				$product["manual"] =  '';
				if(!empty($feed_product->manual->url)){
					$save_path = DIR_IMAGE . 'catalog/power/manual/' ;
					if (!file_exists($save_path)) {
						mkdir($save_path, 0777, true);
					}
					$save_path = $save_path.basename($feed_product->manual->url) ;
					$this->download($feed_product->manual->url ,   $save_path);
					echo 'downloading manual ' .$feed_product->manual->url . PHP_EOL;	
					$download['md5'] =  $feed_product->manual->md5_checksum; 
					$download['file'] =  $save_path;
					$download['type'] =  'manual';
					$downloads[] = $download;	
					$product["manual"] =  	 'catalog/power/manual/'  . basename($feed_product->manual->url) ;
				}
				
				
				
				$product["certificate"] =  '';
				if(!empty($feed_product->certificate->url)){
					$save_path = DIR_IMAGE . 'catalog/power/cert/' ;
					if (!file_exists($save_path)) {
						mkdir($save_path, 0777, true);
					}
					$save_path = $save_path. basename($feed_product->certificate->url) ;
					$this->download($feed_product->certificate->url,   $save_path);
					echo 'downloading certificate ' .$feed_product->certificate->url . PHP_EOL;	
					$download['md5'] =   $feed_product->certificate->md5_checksum; 
					$download['file'] =  $save_path;
					$download['type'] =  'certificate';
					$downloads[] = $download;
					$product["certificate"] =  	 'catalog/power/cert/'  . basename($feed_product->certificate->url) ;
				}
		 
		 
				 
					 
						
				$description["name"]=	 $feed_product->model;
				$description["description"]=		$this->prepare_text( $feed_product->description);	 
				$description["meta_title"]=			 $feed_product->model;
				$description["meta_description"]=		$feed_product->description_short ;
				$description["meta_keyword"]=			 $feed_product->type . ' ' .$feed_product->model ;
				$description["tag"]=		 $feed_product->type;

		 
				$product["product_description"][$language_id] =   $description ;
				if($feed_product->quantity > 0 ){
					$product['quantity'] = $feed_product->quantity;
					$product['stock_status_id'] = $in_stock; //todo
				}else{
					$product['quantity'] = 0;
					$product['stock_status_id'] = $out_of_stock; //todo
				}

				//$product["model"] =  $feed_product->article;
				$product["model"] =  $feed_product->model;
				if(empty($product["model"])){
					$product["model"] = $feed_product->article;
				}
				
				
				//$product["sku"]=  $feed_product->code;
				$product["sku"]=  $feed_product->article;
				
				$product["upc"]= $feed_product->code;
				$product["ean"]= "";
				$product["jan"]= "";
				$product["isbn"]=   "";
				$product["mpn"]=    "" ;
				$product["location"]=   "";
				$product["price"]=     $feed_product->price;
				$product["tax_class_id"]=    "9";
				if(!empty($feed_product->special_price)){
						$product['price'] =$feed_product->special_price;
				}
				$product["minimum"]=   "1" ;
				$product["subtract"]=   "1";
		 
				$product["shipping"]="1";
				$product["date_available"]=   "2009-02-04";
				
				$product["weight"]=  $feed_product->weight;
				$product["weight_class_id"]= $weight_class_id;
				
			
				
				$product["status"]= "1";
				$product["sort_order"]= "0";
				$product["manufacturer"]=  $feed_product->trademark;
				$product["manufacturer_id"]=  $manufacturer_id ;
				$product["category"]=  "";
				
				
				$product["product_store"][] =  "0";
		 
				$product["download"]=  "0";
				$product["related"]=  "0";
				$product["filter"]=  "";
				$product["option"]=  "";
				$product["product_option"]=  [];
				$product["product_discount"]=  [];
				$product["points"]=  [];
				$product["product_seo_url"][0][$language_id]= $feed_product->code . '_'.$feed_product->article 	;
					//clear seo url 
					$this->clear_seourl(	$feed_product->code . '_'.$feed_product->article 	);
					
			
				$product["product_reward"]=  [];
				$product["product_related"]=  [];
				$product["product_attribute"]=  [];
				//$product["product_layout"][0]=  [0];
				
						
				echo 'attributes   '   . PHP_EOL;	
				if(!empty($feed_product->attributes)){
					foreach($feed_product->attributes as $feed_attribute){
						$_attr = [];
					
					 
						
						$_attr['name'] = $feed_attribute->name;
						$_attr['attribute_id'] = $this->check_attribute($feed_attribute->name, $attribute_group_id) ;
						$_attr['product_attribute_description'][$language_id]['text'] =  $feed_attribute->value  ;
						$product["product_attribute"][] = $_attr;
					} 	
				}
				
				$height = 1;
				$width = 1;
				$length = 1;
				
				if(!empty($feed_product->height)){
					$height = $feed_product->height;
				}
				if(!empty($feed_product->width)){
					$width  = $feed_product->width;
				}
				if(!empty($feed_product->length)){
					$length = $feed_product->length;
				}
		
				
				
				$product["length"]=  $length;
				$product["width"]= $width;
				$product["height"]=  $height;
				
				$product["length_class_id"]=  $length_class_id;
				echo 'categories   '   . PHP_EOL;	
				foreach($feed_product->categories as $power_cat_id){
					//$local_cat_id =  $this->get_local_category($power_cat_id, $categories);
					$local_cat_id =  $this->get_category($power_cat_id);
					if($local_cat_id){
						$product["product_category"][] = $local_cat_id;
						
					}
					
					
				}
		 
				echo 'creating   '   . PHP_EOL;	
				$product_id = $this->model_catalog_product->addProduct($product);
				echo 'updating product   '   . PHP_EOL;	
				$query = $this->db->query("UPDATE " . DB_PREFIX . "product SET 
				power_id = '".$this->db->escape($feed_product->code)."', 
				manual = '".$this->db->escape($product["manual"] )."',
				certificate = '".$this->db->escape($product["certificate"] )."'
				WHERE  product_id = '".(int)$product_id."'");

				//update downloads	
				echo 'updating downloads   '   . PHP_EOL;	
				$this->update_downloads($product_id, $downloads);	
				
		 
			} //else create	
			$product_ids[] = $product_id; 
			echo  PHP_EOL;	
		}	//next product
		 
		
		echo 'Deleting old products' . PHP_EOL;
		$this->delete_old_products($product_ids);

		echo 'Update stamp' . PHP_EOL;
		$this->model_setting_setting->editSettingValue('module_power', 'module_power_last_feed', date('d-m-Y H:i:s'));
	}
	
	public function update_downloads($product_id, $downloads, $type=false){
		$str ='';
		if($type){
			$str = " AND type =  '".$this->db->escape($type)."' ";
		}
		
	
		$query = $this->db->query("DELETE FROM `" . DB_PREFIX . "power_attachment` WHERE product_id = '".$this->db->escape($product_id)."' $str");
		foreach( $downloads as $download){
			$query = $this->db->query("INSERT INTO  `" . DB_PREFIX . "power_attachment` (
				`date_created` , `date_modified` ,	`hash` ,`file` , `type`,	`product_id`	)
				VALUES (NOW() , NOW() , '".$this->db->escape($download['md5'])."' ,   '".$this->db->escape($download['file'])."', '".$this->db->escape($download['type'])."',   '".$this->db->escape($product_id)."');
				");
			
		}
	}
	public function delete_old_products($product_ids ){
		if(empty($product_ids )){
			return;
		}
		$this->load->model('catalog/product');
		$str = implode(',', $product_ids);
		$sql = "SELECT * FROM `" . DB_PREFIX . "product` WHERE power_id <> '' AND  product_id NOT IN (".$this->db->escape($str).")";
 
		$query = $this->db->query($sql);
		foreach($query->rows as $row){
			$this->model_catalog_product->deleteProduct($row['product_id']);
		}
	
	 
	}
	
	public function get_attachment($product_id, $type=false){
		$str ='';
		if($type){
			$str = " AND type =  '".$this->db->escape($type)."' ";
		}
		
		$sql = "SELECT * FROM `" . DB_PREFIX . "power_attachment` WHERE  product_id = '".$this->db->escape($product_id)."' $str";
 
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public function prepare_text($text){
	 
		if(!$this->isHtml($text)){
			return nl2br ( $text);
			
		}
		
		
		return $text;
	}
	
	public function isHtml($string){
		return preg_match("/<[^<]+>/",$string,$m) != 0;
	}
	
	public function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}
	
	public function clear_seourl($key){
			$query = $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE keyword = '".$this->db->escape($key)."'");
		
	}
	
		
	public function get_local_order($power_order_id){
		
		$this->load->model('catalog/manufacturer');
		$query = $this->db->query("SELECT order_id FROM " . DB_PREFIX . "order WHERE power_id = '".$this->db->escape($power_order_id)."'");
			
		$row = $query->row;
		if(!empty($row['order_id'])){
			return $row['order_id'];
		}
		return false;

	}
	public function get_local_status($power_status_id){
		$module_power_status_oc =  $this->config->get('module_power_status_oc') ; 
		if(!empty($module_power_status_oc[$power_status_id])){
			return $module_power_status_oc[$power_status_id];
		}
 
		return false;

	}
	
	public function check_manufacturer($trademark){
		
		$this->load->model('catalog/manufacturer');
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer WHERE name = '".$this->db->escape($trademark)."'");
			
		$row = $query->row;
		if(!empty($row)){
			return $row['manufacturer_id'];
		}
	 
		$language_id = (int)$this->config->get('config_language_id') ;
		$store_id = (int) $this->config->get('config_store_id');
		//create
		$data['name'] = $trademark;
		$data['sort_order'] = 0;
		
		 
		$data['manufacturer_store'][] = $store_id; 
		
		
		return $this->model_catalog_manufacturer->addManufacturer($data);
			
		
	}
	
	public function check_attribute_group($attribute){
	
		$this->load->model('catalog/attribute_group');
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_group_description WHERE name = '".$this->db->escape($attribute)."'");
			
		$row = $query->row;
		if(!empty($row)){
			return $row['attribute_group_id'];
		}
		$language_id = (int)$this->config->get('config_language_id') ;
		$store_id = (int) $this->config->get('config_store_id');
	 
		$data['sort_order'] = 0;
	 
		$data['attribute_group_description'][$language_id]['name'] = $attribute; 
 
		return $this->model_catalog_attribute_group->addAttributeGroup($data);
	}
	
	public function update_attribute($attribute){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attribute WHERE 
		product_id = '".(int)$attribute['product_id']."' AND 
		attribute_id  = '".(int)$attribute['attribute_id']."' AND
		language_id  = '".(int)$attribute['language_id']."'	
		");
		$row = $query->row;
 
		if(!empty($row)){
			$query = $this->db->query("UPDATE " . DB_PREFIX . "product_attribute
			SET text = '".$this->db->escape($attribute['text'])."'
			WHERE 
			product_id = '".(int)$attribute['product_id']."' AND 
			attribute_id  = '".(int)$attribute['attribute_id']."' AND
			language_id  = '".(int)$attribute['language_id']."'	 ");
		 
		}else{
			$query = $this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute
			SET text = '".$this->db->escape($attribute['text'])."', 
			product_id = '".(int)$attribute['product_id']."' , 
			attribute_id  = '".(int)$attribute['attribute_id']."' ,
			language_id  = '".(int)$attribute['language_id']."'	 ");
			 
			
		}	
		
	}
	
	public function check_attribute($attribute, $attribute_group_id){
	
		
		
		$this->load->model('catalog/attribute');
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_description WHERE name = '".$this->db->escape($attribute)."'");
			
		$row = $query->row;
		if(!empty($row)){
			return $row['attribute_id'];
		}
		$language_id = (int)$this->config->get('config_language_id') ;
		$store_id = (int) $this->config->get('config_store_id');
		//create
 
		$data['attribute_group_id'] = $attribute_group_id;
		$data['sort_order'] = 0;

		$data['attribute_description'][$language_id]['name'] = $attribute; 
	 
		return $this->model_catalog_attribute->addAttribute($data);
			
		
	}
	
	public function import_categories($categories){
		$categories_power = [];
 
		foreach($categories as $category){
 
			
			
				$categories_power[] = $category->id;
				echo 'Checking '. $category->name .PHP_EOL;
				$this->check_category($category);
			 
			
		}
		if(empty($categories_power )){
			return;
		}
		$this->load->model('catalog/category');
		$str = implode(',', $categories_power);
		if(empty($str )){
			
			$str = '0';
		}
 
		$sql = "SELECT * FROM `" . DB_PREFIX . "category` WHERE power_id <> '' AND power_id <> '0' AND   power_id NOT IN (".$this->db->escape($str).")";
 
		$query = $this->db->query($sql);
		foreach($query->rows as $row){
 
			$this->model_catalog_category->deleteCategory($row['category_id']);
		}
	 
	 
	}

	public function deleteProductCategory($product_id, $local_cat_id){
		$query = $this->db->query("DELETE FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` = '".(int)$product_id."' AND `category_id` = '".(int)$local_cat_id."' AND power_id <> '0'");
	
	}
	public function addProductCategory($product_id, $local_cat_id){
		 
			$query = $this->db->query("INSERT INTO  `" . DB_PREFIX . "product_to_category` ( `product_id` , `category_id`  	)
				VALUES ( '".(int)$product_id."' , '".(int)$local_cat_id."' ); 	");
	}
 
	public  function get_category($power_category_id ){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category WHERE power_id  = '".$this->db->escape($power_category_id)."'");
		$row = $query->row;
		 
		if(!empty($row['category_id'])){
			return $row['category_id'];
		}
		return false;
	}
	
	public function check_category($category ){
		
		$this->load->model('catalog/category');
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category WHERE power_id  = '".$this->db->escape($category->id)."'");
			
		$row = $query->row;
		if(!empty($row)){
			echo 'Category exists' . PHP_EOL;
			//exists
			//check name and description
			$name_md5 = md5($category->name);
			$description_md5 = md5($category->description);
			$category_id = $row['category_id'];


			if($name_md5 != $row['name_hash']){
				$query = $this->db->query("UPDATE " . DB_PREFIX . "category_description SET 
				name = '".$this->db->escape($category->name)."',
				meta_title = '".$this->db->escape($category->name)."',
				meta_description = '".$this->db->escape($category->name)."',
				meta_keyword = '".$this->db->escape($category->name)."'  
				WHERE  category_id = '".(int)$category_id."'");

				$query = $this->db->query("UPDATE " . DB_PREFIX . "category SET 
				name_hash = '".$this->db->escape($name_md5)."'  
				WHERE  category_id = '".(int)$category_id."'");
			}
			if($description_md5 != $row['description_hash']){
				$query = $this->db->query("UPDATE " . DB_PREFIX . "category_description SET 
				description = '".$this->db->escape(  $this->prepare_text( $category->description) )."' 
				WHERE  category_id = '".(int)$category_id."'");

				$query = $this->db->query("UPDATE " . DB_PREFIX . "category SET 
				description_hash = '".$this->db->escape($description_md5)."'  
				WHERE  category_id = '".(int)$category_id."'");
			}

			//
			return $row['category_id'];
		}
		echo 'Add category exist' . PHP_EOL;
		
		$language_id = (int)$this->config->get('config_language_id') ;
		$store_id = (int) $this->config->get('config_store_id');
		
		//create
		$data['name'] = $category->name;
		$data['sort_order'] =$category->sort_order;
		
		
		if( empty($category->parent_id)){
			$data['top'] = 1;
			$data['parent_id'] = 0;
		
		}else{
			$data['parent_id'] = $this->get_category($category->parent_id);
			$data['top'] = 0;
			
		}
		$data['column'] = 0;
		$data['status'] = 1;
		$data['category_description'][$language_id]['name'] = $category->name;
		$data['category_description'][$language_id]['description'] = $this->prepare_text( $category->description);
		$data['category_description'][$language_id]['meta_title'] = $category->name;
		$data['category_description'][$language_id]['meta_description'] = $category->name;
		$data['category_description'][$language_id]['meta_keyword'] =  $category->name;
		
		$name_md5 = md5($category->name);
		$description_md5 = md5($category->description);
	 
		$data['category_store'][] = $store_id; 
		
		
		$category_id =  $this->model_catalog_category->addCategory($data);
		$query = $this->db->query("UPDATE " . DB_PREFIX . "category SET 
		power_id = '".$this->db->escape($category->id)."',
		name_hash = '".$this->db->escape($name_md5)."'  ,
		description_hash = '".$this->db->escape($description_md5)."'  
		WHERE  category_id = '".(int)$category_id."'");

	 
		return $category_id;
			
	}
	

	public function getOrdersQueue(){

		$finished = [];
		$finished[] = 45; //ORDER_STATUS_CANCELED
		$finished[] = 50; //ORDER_STATUS_RETURNED
		$finished[] = 40; //ORDER_STATUS_COMPLETED
			
		$finished = implode(',', $finished);
		$query = $this->db->query("SELECT order_id, order_status_id, power_id, power_status FROM " . DB_PREFIX . "order WHERE power_id  <> '0' AND power_status NOT IN (".$this->db->escape($finished).")");
		$data = [];
		foreach ($query->rows as $row) {
		 
			$data[$row['power_id']] = $row['power_status'];
		}
		return $data;
	
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
	
	public function download($url, $file_target) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSLVERSION,3);
		$data = curl_exec ($ch);
		$error = curl_error($ch); 
		curl_close ($ch);

		if(is_file($file_target)){
			unlink($file_target);
			
		}
		$file = fopen($file_target, "w+");
		fputs($file, $data);
		fclose($file);
		return true;
	}
	
	public function addOrderHistory($order_id, $order_status_id, $power_status ) {
			$comment = ''; $notify = false; $override = false;
			// Update the DB with the new statuses
			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() , power_status = '" . (int)$power_status . "' WHERE order_id = '" . (int)$order_id . "'");

			$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '" . (int)$notify . "', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");

	 
	} 
}
?>