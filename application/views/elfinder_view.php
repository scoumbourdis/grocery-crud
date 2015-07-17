<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>Elfinder</title>
	<meta name="description" content="">
	<link rel="stylesheet" href="<?php echo base_url();?>assets/grocery_crud/elfinder/jquery/ui-themes/smoothness/jquery-ui-1.8.18.custom.css">
	<link rel="stylesheet" href="<?php echo base_url();?>assets/grocery_crud/elfinder/css/elfinder.min.css">
	<link rel="stylesheet" href="<?php echo base_url();?>assets/grocery_crud/elfinder/css/theme.css">
	<script src="<?php echo base_url();?>assets/jgrocery_crud/elfinder/query/jquery-1.7.2.min.js"></script>
	<script src="<?php echo base_url();?>assets/grocery_crud/elfinder/jquery/jquery-ui-1.8.18.custom.min.js"></script>
	<script src="<?php echo base_url();?>assets/grocery_crud/elfinder/js/elfinder.min.js"></script>
	<script>
	$().ready(function(){
		var elf = $('#elfinder').elfinder({
			url:'<?php echo base_url("examples/elfinder_init");?>',
			height:460,
		}).elfinder('instance');
	});
	</script>	
</head>
<body>
<div>
		<a href='<?php echo site_url('examples/customers_management')?>'>Customers</a> |
		<a href='<?php echo site_url('examples/orders_management')?>'>Orders</a> |
		<a href='<?php echo site_url('examples/products_management')?>'>Products</a> |
		<a href='<?php echo site_url('examples/offices_management')?>'>Offices</a> | 
		<a href='<?php echo site_url('examples/employees_management')?>'>Employees</a> |	
		<a href='<?php echo site_url('examples/employees_disk_management')?>'>Employees <i>*new</i></i></a> |			
		<a href='<?php echo site_url('examples/elfinder_files')?>'>FILES<i>*new</i></i></a> |		 	 
		<a href='<?php echo site_url('examples/film_management')?>'>Films</a> |
		<a href='<?php echo site_url('examples/multigrids')?>'>Multigrid [BETA]</a>
		
	</div>
	<div id="elfinder">Elfinder</div>
</body>
</html>
