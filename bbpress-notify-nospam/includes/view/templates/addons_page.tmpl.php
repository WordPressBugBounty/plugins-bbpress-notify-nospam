<?php
/**
 * Add-ons listing template.
 *
 * @package bbPress_Notify_NoSpam
 * Location: includes/view/templates/addons_page.tmpl.php
 */

?>
<h1><?php esc_html_e( 'bbPress Notify (No-Spam) Add-Ons', 'bbpress-notify-nospam' ); ?></h1>
<h1 class="screen-reader-text"><?php esc_html_e( 'Add-On list', 'bbpress-notify-nospam' ); ?></h1>

<p>
<?php
/* Translators: short description for the add-ons page. */
esc_html_e(
	"bbPress Notify (No-Spam) is a great plugin for notifications, but you already knew that (or you wouldn't be using it, right?). What makes it even greater are the several add-on extensions available. Check out all of the options below, as there's bound to be something you like.",
	'bbpress-notify-nospam'
);
?>
</p>

<hr>
<div class="wp-list-table widefat plugin-install">
	<div id="the-list">
	<?php foreach ( (array) $stash->addons as $p ) : ?>
					<div class="plugin-card plugin-card-<?php echo esc_attr( $p->slug ); ?>">
			<div class="plugin-card-top">
				<div class="name column-name">
					<h3>
						<a href="<?php echo esc_url( $p->permalink ); ?>" target="_new"><?php echo esc_html( $p->name ); ?></a>
						<img src="<?php echo esc_url( $p->image ); ?>" class="plugin-icon" alt="<?php echo esc_attr( $p->name ); ?>">
					</h3>
				</div>
				<div class="action-links">
					<ul class="plugin-action-buttons">
						<?php if ( $p->is_installed && $p->is_active ) : ?>
							<li>
								<button type="button" class="button button-disabled" disabled="disabled"><?php esc_html_e( 'Active', 'bbpress-notify-nospam' ); ?></button>
							</li>
							<?php $plugin_page_local = apply_filters( $p->local->TextDomain . '_plugin_page', null ); ?>
								<?php if ( $p->local->license_page ) : ?>
							<li>
								<a href="<?php echo esc_url( $p->local->license_page ); ?>"><?php esc_html_e( 'Manage License', 'bbpress-notify-nospam' ); ?></a>
							</li>
								<?php endif; ?>
							<?php else : ?>
							<li>
								<a href="<?php echo esc_url( $p->permalink ); ?>" class="button button-primary" target="_new"><?php esc_html_e( 'More Details', 'bbpress-notify-nospam' ); ?></a>
							</li>
						<?php endif; ?>
					</ul>
				</div>
					<div class="desc column-description">
					<?php echo esc_html( $p->short_description ); ?>
				</div>
			</div>
			<div class="plugin-card-bottom">
				<div class="vers column-rating">
					<ul>
					<?php if ( $p->is_installed ) : ?>
						<li> <strong><?php esc_html_e( 'Installed version:', 'bbpress-notify-nospam' ); ?></strong> <?php echo esc_html( $p->local->Version ); ?></li>
							<?php if ( $p->update_available ) : ?>
						<li> <span class="dashicons dashicons-warning"></span><?php esc_html_e( 'Update available', 'bbpress-notify-nospam' ); ?></li>
							<?php endif; ?>
					<?php endif; ?>
					</ul>
				</div>
				<div class="column-updated">
					<ul>
						<li> <strong><?php esc_html_e( 'Latest version:', 'bbpress-notify-nospam' ); ?></strong> <?php echo esc_html( $p->version ); ?></li>
					<?php if ( $p->recommended ) : ?>
						<li>
							<span class="dashicons dashicons-yes"></span> <?php echo wp_kses_post( __( '<strong>Recommended!</strong>', 'bbpress-notify-nospam' ) ); ?>
						</li>
					<?php endif; ?>
					</ul>
				</div>
			</div>
		</div>
	<?php endforeach; ?>


	</div>
</div>