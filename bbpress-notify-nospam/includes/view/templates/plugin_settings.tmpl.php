<?php
/**
 * Plugin settings page body template.
 *
 * Location: includes/view/templates/plugin_settings.tmpl.php
 *
 * @package bbPress_Notify_NoSpam
 */

?>
<form method="POST" action="options.php" class="">
	<h1><?php esc_html_e( 'bbPress Notify (No-Spam) Settings', 'bbpress-notify-nospam' ); ?> <small>(v<?php echo esc_html( self::VERSION ); ?>)</small></h1>


	<h2 class="sbiforwpnav nav-tab-wrapper">
		<?php foreach ( (array) apply_filters( 'bbpnns_settings_registered_tabs', array() ) as $tab_key => $text ) : ?>
			<?php do_action( 'bbpnns_settings_nav_' . $tab_key, $stash, $tab_key, $text ); ?>
		<?php endforeach; ?>
	</h2>
	
	<input type="hidden" id="active_tab" name="active_tab" value="<?php echo esc_attr( $stash->active_tab ); ?>" />
		
		<div id="poststuff" class="metabox-holder <?php echo $stash->has_sidebar ? 'bbpnns_settings_main' : ''; ?>">
			<div id="post-body">
				<div id="post-body-content">
				
				<div id="bbpnns_settings_wrapper">
					<?php do_meta_boxes( $stash->pagehook . '-' . $stash->active_tab, 'normal', $stash ); ?>
				</div>
			</div>
		</div>
	</div>
	
	<?php if ( $stash->has_sidebar ) : ?>
		<div class="bbpnns_settings_sidebar">
			<?php echo wp_kses_post( $stash->sidebar ); ?>
		</div>
		<br style="clear:both">
	<?php endif; ?>
	
	<div>
		<?php settings_fields( apply_filters( 'bbpnns_settings_screen_settings_name', $this->settings_name ) ); ?>
		<?php submit_button(); ?>
	</div>
</form>
