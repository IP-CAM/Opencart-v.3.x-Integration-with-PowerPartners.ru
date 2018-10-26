<?php
	shell_exec("php update_orders.php >/dev/null &");
	shell_exec("php update_products.php >/dev/null &");
?>