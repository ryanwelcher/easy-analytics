<table class="form-table">
	<tr>
    	<th scope="row">
        	<label for="tracking_id"><?php _e('Tracking ID','easy-analytics');?></label>
        </th>
        <td>
        	<input type="text" id="tracking_id" name="<?php echo $this->_settings_name; ?>[tracking_id]" value="<?php echo isset( $settings['tracking_id'] ) ? $settings['tracking_id'] : ''; ?>" />
        </td>
    </tr>
    <tr>
    	<th scope="row">
        	<label for="tracking_id"><?php _e('Snippet Type','easy-analytics');?></label>
        </th>
        <td>
        	Universal <input type="radio" name="<?php echo $this->_settings_name; ?>[type]" value="ua" <?php checked( 'ua', $settings['type']);?> />
        	Legacy <input type="radio" name="<?php echo $this->_settings_name; ?>[type]" value="ga" <?php checked( 'ga', $settings['type']);?> />
        </td>
    </tr>
    <tr>
    	<th scope="row">
        	<label for="tracking_id"><?php _e('Snippet Location','easy-analytics');?></label>
        </th>
        <td>
        	Header <input type="radio" name="<?php echo $this->_settings_name; ?>[location]" value="header" <?php checked( 'header', $settings['location']);?> />
        	Footer <input type="radio" name="<?php echo $this->_settings_name; ?>[location]" value="footer" <?php checked( 'footer', $settings['location']);?> />
        	<br/><small>Google recommends putting the snippet in your header but you can choose to override that here.</small>
        </td>
    </tr>
    <tr>
    	<th scope="row">
        	<label for="tracking_id"><?php _e('Use Enhanced Link Attribution','easy-analytics');?></label>
        </th>
        <td>
        	Yes <input type="radio" name="<?php echo $this->_settings_name; ?>[enhanced_link_attribution]" value="yes" <?php checked( 'yes', $settings['enhanced_link_attribution']);?> />
        	No <input type="radio" name="<?php echo $this->_settings_name; ?>[enhanced_link_attribution]" value="no" <?php checked( 'no', $settings['enhanced_link_attribution']);?> />
        	<br/><small>You will need to enable <em>enhanced link attribution</em> in your property settings for this tracking id in your Google Analytics account.</small>
        </td>
    </tr>
    <tr>
    	<th scope="row"><label for="domain_name"><?php _e('_setDomain - Legacy Option','easy-analytics');?></label></th>
    	<td>
    		<input type="text" id="ea_domain_name" name="ea_domain_name" value="<?php echo isset( $settings['domain_name'] ) ? esc_attr($settings['domain_name']) : ''; ?>" />
    		<br/><small>Note: This is only used in the Legacy code snippet and will be eventually removed from this plugin</small>
    	</td>
    </tr>
</table>