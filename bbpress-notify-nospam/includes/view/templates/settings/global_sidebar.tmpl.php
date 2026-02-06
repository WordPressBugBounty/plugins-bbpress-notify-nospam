<?php
/**
 * Settings sidebar warnings.
 *
 * Location: includes/view/templates/settings/global_sidebar.tmpl.php
 *
 * @package bbPress_Notify_NoSpam
 */

?>
<div class="bbpnns-warnings">
<?php if ( ! empty( $stash->warnings ) ) : ?>

	<?php foreach ( $stash->warnings as $w ) : ?>
		<div class="notice notice-warning inline">
			<p><?php echo esc_html( $w ); ?></p>
		</div>
	<?php endforeach; ?>

<?php endif; ?>
</div>