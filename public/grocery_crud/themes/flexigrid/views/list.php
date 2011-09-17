<?php  
	if (!defined('BASEPATH')) exit('No direct script access allowed');

	$column_width = (int)(80/count($columns));
	
	if(!empty($list)){
?><div class="bDiv" >
		<table cellspacing="0" cellpadding="0" border="0" style="" id="flex1" width='960'>
		<thead>
			<tr class='hDiv'>
				<?php foreach($columns as $column){?>
				<th width='<?php echo $column_width?>%'>
					<div class="text-left field-sorting <?php if(isset($order_by[0]) &&  $column->field_name == $order_by[0]){?><?php echo $order_by[1]?><?php }?>" 
						rel='<?php echo $column->field_name?>'>
						<?php echo $column->display_as?>
					</div>
				</th>
				<?php }?>
				<?php if(!$unset_delete || !$unset_edit){?>
				<th align="left" abbr="tools" axis="col1" class="" width='20%'>
					<div class="text-right">
						Actions
					</div>
				</th>
				<?php }?>
			</tr>
		</thead>		
		<tbody>
<?php foreach($list as $num_row => $row){ ?>        
		<tr  <?php if($num_row % 2 == 1){?>class="erow"<?php }?>>
			<?php foreach($columns as $column){?>
			<td width='<?php echo $column_width?>%' class='<?php if(isset($order_by[0]) &&  $column->field_name == $order_by[0]){?>sorted<?php }?>'>
				<div style="width: 100%;" class='text-left'>
					<?php echo !empty($row->{$column->field_name}) ? $row->{$column->field_name} : '&nbsp;'?>
				</div>
			</td>
			<?php }?>
			<?php if(!$unset_delete || !$unset_edit){?>
			<td align="left" width='20%'>
				<div class='tools'>
					<?php if(!$unset_delete){?>
                    	<a href='<?php echo $row->delete_url?>' title='Delete <?php echo $subject?>'  class='delete-row' ><div class='delete-icon'></div></a>
                    <?php }?>
                    <?php if(!$unset_edit){?>
						<a href='<?php echo $row->edit_url?>' title='Edit <?php echo $subject?>'><div class='edit-icon'></div></a>
					<?php }?>
                    <div class='clear'></div>
				</div>
			</td>
			<?php }?>
		</tr>
<?php } ?>        
		</tbody>
		</table>
	</div>
<?php }else{?>
	<br/>
	&nbsp;&nbsp;&nbsp;&nbsp; No items to display
	<br/>
	<br/>
<?php }?>	