
<a href="#" class="ce_element_options_on" ><?= lang('settings_options_show') ?> &raquo;</a>


<div class="ce_element_options js_hide">

	<table cellpadding="0" cellspacing="0" class="ce_settings_table ce_settings_mason" width="100%">
	
		<?php foreach ($settings as $i => $setting_block): ?>
	    <tr><td colspan="3"><div class="element_top"></div></td></tr>
		<tr class="mason_block_element <?php if($i == count($settings)-1) echo ' last'; ?>">
    		<td>
    		    <?php if(is_array($setting_block)): ?>
        		    <span class="mason_block_handle" href="#"></span>
        		    <a class="mason_button_remove" href="#"></a>
        		    <table width="100%">
            		<?php foreach ($setting_block as $setting_options): ?>
            		
            		<tr>
            			<?php foreach ($setting_options as $k => $option): ?>
            			<td <?= !is_numeric($k) ? $k : '' ?> align="left" valign="top"><?= $option ?></td>
            			<?php endforeach; ?> 
            		</tr>		
        		
        		    <?php endforeach; ?>
        		    </table>
        		<?php else: ?>
        		    <?= $setting_block ?>
        		<?php endif; ?>
		    </td>
		</tr>
		<?php if($i != count($settings)-1): ?>
	    <tr><td colspan="3"><div class="element_bottom"></div></td></tr>
	    <?php endif; ?>
		<?php endforeach; ?>
		 	 		 
	</table>
</div>	

<a href="#" class="ce_element_options_off js_hide" >&laquo; <?= lang('settings_options_hide') ?></a>