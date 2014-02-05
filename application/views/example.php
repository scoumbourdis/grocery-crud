<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
<?php 
foreach($css_files as $file): ?>
	<link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
<?php endforeach; ?>
<?php foreach($js_files as $file): ?>
	<script src="<?php echo $file; ?>"></script>
<?php endforeach; ?>
<style type='text/css'>
body
{
	font-family: Arial;
	font-size: 14px;
}
a {
    color: blue;
    text-decoration: none;
    font-size: 14px;
}
a:hover
{
	text-decoration: underline;
}
</style>
</head>
<body>
	<div>
		<a href='<?php echo site_url('examples/customers_management')?>'>Customers</a> |
		<a href='<?php echo site_url('examples/orders_management')?>'>Orders</a> |
		<a href='<?php echo site_url('examples/products_management')?>'>Products</a> |
		<a href='<?php echo site_url('examples/offices_management')?>'>Offices</a> | 
		<a href='<?php echo site_url('examples/employees_management')?>'>Employees</a> |		 
		<a href='<?php echo site_url('examples/film_management')?>'>Films</a> | 
		<a href='<?php echo site_url('examples/film_management_twitter_bootstrap')?>'>Twitter Bootstrap Theme [BETA]</a> | 
		<a href='<?php echo site_url('examples/multigrids')?>'>Multigrid [BETA]</a>	
	</div>
	<div>
		<a href='<?php echo site_url('examples/column_align_right')?>'>Flexigrid - Column Align Right</a> |
		<a href='<?php echo site_url('examples/column_align_center')?>'>Flexigrid - Column Align Center</a> |
		<a href='<?php echo site_url('examples/column_align_right2')?>'>Datatables - Column Align Right</a> |
		<a href='<?php echo site_url('examples/column_align_center2')?>'>Datatables - Column Align Center</a> |
	</div>
	<div style='height:20px;'></div>  
    <div>
		<?php echo $output; ?>
    </div>
</body>
</html>
