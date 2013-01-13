<?php
if(!empty($list)){ ?>
<div class="span12" >
	<table class="table table-bordered tablesorter table-striped">
		<thead>
			<tr>
				<?php foreach($columns as $column){?>
				<th class="field-sorting <?php echo (isset($order_by[0]) &&  $column->field_name == $order_by[0]) ? $order_by[1] : ''; ?>" data-field-name="<?php echo $column->field_name; ?>" >
					<?php echo $column->display_as; ?>
				</th>
				<?php }?>
				<?php if(!$unset_delete || !$unset_edit || !empty($actions)){?>
				<th class="no-sorter">
						<?php echo $this->l('list_actions'); ?>
				</th>
				<?php }?>
			</tr>
		</thead>
		<tbody>
			<?php foreach($list as $num_row => $row){ ?>
			<tr>
				<?php foreach($columns as $column){?>
					<td class="<?php echo (isset($order_by[0]) &&  $column->field_name == $order_by[0]) ? 'sorted' : ''; ?>" >
						<?php echo ($row->{$column->field_name} != '') ? $row->{$column->field_name} : '&nbsp;' ; ?>
					</td>
				<?php }?>
				<?php if(!$unset_delete || !$unset_edit || !empty($actions)){?>
				<td align="left">
					<div class="tools">
						<div class="btn-group">
							<button class="btn"><?php echo $this->l('list_actions'); ?></button>
							<button class="btn dropdown-toggle" data-toggle="dropdown">
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu">
								<?php
								if(!$unset_edit){?>
									<li>
										<a href="<?php echo $row->edit_url?>" title="<?php echo $this->l('list_edit')?> <?php echo $subject?>">
											<i class="icon-pencil"></i>
											<?php echo $this->l('list_edit') . ' ' . $subject; ?>
										</a>
									</li>
								<?php
								}
								if(!$unset_delete){?>
									<li>
										<a href="<?php echo $row->delete_url?>" title="<?php echo $this->l('list_delete')?> <?php echo $subject?>" class="delete-row" >
											<i class="icon-trash"></i>
											<?php echo $this->l('list_delete') . ' ' . $subject; ?>
										</a>
									</li>
								<?php
								}
								if(!empty($row->action_urls)){
									foreach($row->action_urls as $action_unique_id => $action_url){
										$action = $actions[$action_unique_id];
										?>
										<li>
											<a href="<?php echo $action_url; ?>" class="<?php echo $action->css_class; ?> crud-action" title="<?php echo $action->label?>"><?php
											if(!empty($action->image_url)){ ?>
												<img src="<?php echo $action->image_url; ?>" alt="<?php echo $action->label?>" />
											<?php
											}
											?>
											</a>
										</li>
									<?php
									}
								}
								?>
								</ul>
							</div>
							<div class="clear"></div>
						</div>
					</td>
					<?php }?>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
<?php }else{ ?>
	<br/><?php echo $this->l('list_no_items'); ?><br/><br/>
<?php }?>