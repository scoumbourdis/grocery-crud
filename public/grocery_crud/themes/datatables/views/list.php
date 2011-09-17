<?php  
	if (!defined('BASEPATH')) exit('No direct script access allowed');
?><table cellpadding="0" cellspacing="0" border="0" class="display" id="groceryCrudTable">
	<thead>
		<tr>
			<?php foreach($columns as $column){?>
				<th><?php echo $column->display_as?></th>
			<?php }?>
			<?php if(!$unset_delete || !$unset_edit){?>
			<th align='right'>Actions</th>
			<?php }?>
		</tr>
	</thead>
	<tbody>
		<?php foreach($list as $num_row => $row){ ?>  
		<tr id='row-<?php echo $num_row?>'>
			<?php foreach($columns as $column){?>
				<td><?php echo $row->{$column->field_name}?></td>
			<?php }?>
			<?php if(!$unset_delete || !$unset_edit){?>
			<td align='right' width='200'>
				<?php if(!$unset_edit){?>
					<a href="<?php echo $row->edit_url?>" class="edit_button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" role="button"><span class="ui-button-icon-primary ui-icon ui-icon-pencil"></span><span class="ui-button-text">Edit</span></a>
				<?php }?>
				<?php if(!$unset_delete){?>
					<a onclick = "javascript: return delete_row('<?php echo $row->delete_url?>', '<?php echo $num_row?>')" 
						href="javascript:void(0)" class="delete_button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" role="button">
						<span class="ui-button-icon-primary ui-icon ui-icon-circle-minus"></span>
						<span class="ui-button-text">Delete</span>
					</a>
				<?php }?>
			</td>
			<?php }?>
		</tr>
		<?php }?>
	</tbody>
</table>