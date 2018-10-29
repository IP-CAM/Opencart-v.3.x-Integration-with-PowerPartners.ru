<?php
	shell_exec("php ". dirname(__FILE__)."/update_orders.php >/dev/null &");
	shell_exec("php ". dirname(__FILE__) ."/update_products.php >/dev/null &");
?>