<a id="mason<?php echo str_replace(array('.',' '), '', $int_id); ?>"></a>
<?php if($field_types_changed): ?>
    <div class="warning element_modified_type">Subelement types have been changed or added - please resave this element to apply new settings.</div>
    <script>
        setTimeout("$('html, body').animate({scrollTop: $(window.location.hash).offset().top -50 }, 'slow'); ", 500);
    </script>
    <a href="#" class="ce_element_options_off" >&laquo; <?= lang('settings_options_hide') ?></a>
    <div class="ce_element_options js_show">
<?php else: ?>
    <a href="#" class="ce_element_options_on" ><?= lang('settings_options_show') ?> &raquo;</a>
    <div class="ce_element_options js_hide">
<?php endif; ?>

    <!-- int_id = <?php echo $int_id; ?> -->
    <?php echo form_hidden('int_id', $int_id); ?>
    <!--<label>Block Name <?php echo form_input('mason_name', $mason_name); ?></label><br/>-->
    
    <table cellpadding="0" cellspacing="0" class="ce_settings_table" width="100%">
        <tbody class="ce_settings_mason">
            <?php foreach ($settings as $i => $setting_block): ?>
                <tr class="mason_block_element <?php if($i == count($settings)-1) echo ' last'; ?>">
                    <td>
                        <div class="element_top"></div>
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
                        <?php if($i != count($settings)-1): ?>
                        <div class="element_bottom"></div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>                     
    </table>
</div>  

<a href="#" class="ce_element_options_off js_hide" >&laquo; <?= lang('settings_options_hide') ?></a>
