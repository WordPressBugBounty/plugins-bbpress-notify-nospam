<?php
/**
 * Settings topics template.
 *
 * @package bbpress-notify-nospam
 */

do_action( 'bbpnns_settings_topics_box_top' ); ?>

<style>
	.form-table tr {
		border-bottom: 1px groove #ccc;
	}
	.form-table tr:last-child {
		border-bottom:0;
	}
	.handlediv {
		display: none !important;
	}
	.full-width {
		width: 100%;	
	}
</style>

<script>
jQuery(document).ready(function($){
	
	$("#bbpnns-topic-recipients").select2({
			placeholder: "<?php esc_attr_e( 'Select one or more Roles', 'bbpress-notify-nospam' ); ?>",
			allowClear: true
		});

	$("#forums_auto_subscribe_to_topics,#forums_auto_subscribe_new_users").on('click', function(){
		if ( $(this).prop( 'checked' ) && ! $("#override_bbp_forum_subscriptions").prop( 'checked' ) ) {
			$("#override_bbp_forum_subscriptions").prop( 'checked', true );
		}
	});

	$("#override_bbp_forum_subscriptions").on( 'click', function(){
		if ( ! $(this).prop( 'checked' ) ) {
			$("#forums_auto_subscribe_to_topics").prop( 'checked', false );
			$("#forums_auto_subscribe_new_users").prop( 'checked', false );
		}
	});
});

</script>

<table class="form-table">
	<tbody>
	
	<?php do_action( 'bbpnns_settings_topics_box_before_first_row' ); ?>
	
		<tr>
			<th scope="row"><?php esc_html_e( 'Recipients', 'bbpress-notify-nospam' ); ?></th>
			<td><label for="bbpnns-topic-recipients"><?php esc_html_e( 'Select one or more roles below to determine which users will be notified of new Topics.', 'bbpress-notify-nospam' ); ?>
				
				<br><br>
				<?php
					global $wp_roles;

					$options      = $wp_roles->get_names();
					$saved_option = array_flip( $stash->settings->newtopic_recipients );
				?>
				<select id="bbpnns-topic-recipients" class="full-width" multiple="multiple" name="<?php echo esc_attr( $this->settings_name . '[newtopic_recipients][]' ); ?>">
			<?php foreach ( $options as $value => $description ) : ?>
			
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( isset( $saved_option[ $value ] ) ); ?>><?php echo esc_html( $description ); ?></option>
			
			<?php endforeach; ?>
			</select>
			<br><br>
				<span class="description">
				<?php
				echo wp_kses_post(
					__(
						'By selecting roles, all users of the selected roles will receive a notification of each new topic.<br>
				You can also leave none selected and check only the "Override Subscriptions to Forums" option. That way only users who have explicitly subscribed to a 
				given forum will receive notifications.',
						'bbpress-notify-nospam'
					)
				);
				?>
				</span>
			</td>
		</tr>
	
		<tr>
			<th scope="row"><?php esc_html_e( 'Admin UI Topic Notifications', 'bbpress-notify-nospam' ); ?></th>
			<td>
				<label>
						<input type="checkbox" name="<?php echo esc_attr( $this->settings_name . '[default_topic_notification_checkbox]' ); ?>" value="1"
						<?php checked( $stash->settings->default_topic_notification_checkbox ); ?> >
							<?php esc_html_e( 'Make "Send Notifications" option checked by default.', 'bbpress-notify-nospam' ); ?>
							<br><br>
							<span class="description">
							<?php esc_html_e( 'This option controls the status of the "Send Notifications" checkbox in the New/Edit Topic Admin Screen', 'bbpress-notify-nospam' ); ?>
							
							.</span>
				</label>
			</td>
		</tr>
		
		
		<tr>
			<th scope="row"><?php esc_html_e( 'bbPress Forums Subscriptions Override', 'bbpress-notify-nospam' ); ?></th>
			<td>
				<label>
						<input id="override_bbp_forum_subscriptions" type="checkbox" name="<?php echo esc_attr( $this->settings_name . '[override_bbp_forum_subscriptions]' ); ?>" value="1"
						<?php checked( $stash->settings->override_bbp_forum_subscriptions ); ?> >
							<?php esc_html_e( 'Override Subscriptions to Forums.', 'bbpress-notify-nospam' ); ?>
							<br><br>
							<span class="description">
							<?php
							echo wp_kses_post(
								__(
									'Enable this option if you want bbPress Notify (No-Spam) to handle bbPress subscriptions to Forums (new topics).
The bbPress Setting "Allow users to subscribe to forums and topics" must also be enabled for this to work.<br><a target="_blank" href="https://usestrict.net/2013/02/bbpress-notify-nospam/#subscriptions">Click here to learn more.</a>',
									'bbpress-notify-nospam'
								)
							);
							?>
							</span>
				</label>
				<hr>
				
				<label style="margin-left:2em;">
						<input type="checkbox" id="forums_auto_subscribe_new_users" name="<?php echo esc_attr( $this->settings_name . '[forums_auto_subscribe_new_users]' ); ?>" value="1"
						<?php checked( $stash->settings->forums_auto_subscribe_new_users ); ?> >
							<?php esc_html_e( 'Automatically subscribe new users to all forums.', 'bbpress-notify-nospam' ); ?>
							<br><br>
							<span class="description" style="margin-left:2em;"><?php echo esc_html__( 'Enabling this option will make it so that users get subscribed to all forums the moment that they\'re registered. They can unsubscribe from the forums later to stop receiving topic notifications.', 'bbpress-notify-nospam' ); ?></span>
				</label>
				
				<hr>
				
				<label style="margin-left:2em;">
						<input type="checkbox" id="forums_auto_subscribe_to_topics" name="<?php echo esc_attr( $this->settings_name . '[forums_auto_subscribe_to_topics]' ); ?>" value="1"
						<?php checked( $stash->settings->forums_auto_subscribe_to_topics ); ?> >
							<?php esc_html_e( 'Automatically subscribe all forum subscribers to newly created topics.', 'bbpress-notify-nospam' ); ?>
							<br><br>
							<span class="description" style="margin-left:2em;"><?php echo esc_html__( 'Enabling this option will pull all of the forum subscribers and automatically subscribe them to the new topic so that they get notifications of new replies as well. They can unsubscribe from the topic later to stop receiving reply notifications.', 'bbpress-notify-nospam' ); ?></span>
				</label>
			</td>
		</tr>
		
		<tr>
			<th scope="row"><?php esc_html_e( 'Notify authors of their own Topics', 'bbpress-notify-nospam' ); ?></th>
			<td>
				<label>
						<input type="checkbox" name="<?php echo esc_attr( $this->settings_name . '[notify_authors_topic]' ); ?>" value="1"
						<?php checked( $stash->settings->notify_authors_topic ); ?> >
							<?php esc_html_e( 'Authors must also receive a notification when they create a topic.', 'bbpress-notify-nospam' ); ?>
				</label>
			</td>
		</tr>

		<tr>
			<th scope="row"><?php esc_html_e( 'Force Admin-only emails if Forum is hidden', 'bbpress-notify-nospam' ); ?></th>
			<td>
				<label>
						<input type="checkbox" name="<?php echo esc_attr( $this->settings_name . '[hidden_forum_topic_override]' ); ?>" value="1"
						<?php checked( $stash->settings->hidden_forum_topic_override ); ?> >
							<?php esc_html_e( 'Only admins should be notified of new topics in hidden forums.', 'bbpress-notify-nospam' ); ?>
				</label>
				<br></br>
				<span class="description">
				<?php
				echo wp_kses_post(
					__(
						'Looking to have group forums? Try <a href="https://wordpress.org/plugins/bbp-private-groups/" target="_blank">Private Groups</a>
				and our premium <a href="https://usestrict.net/product/bbpress-notify-no-spam-private-groups-bridge/" target="_new">Private Groups Bridge</a> add-on.',
						'bbpress-notify-nospam'
					)
				);
				?>
				</span>
			</td>
		</tr>
		
		<tr>
			<th scope="row"><?php esc_html_e( 'Topic E-mail Subject Line', 'bbpress-notify-nospam' ); ?></th>
			<td>
				<input type="text" class="full-width bbpnns-message-subject" name="<?php echo esc_attr( $this->settings_name . '[newtopic_email_subject]' ); ?>" value="<?php echo esc_attr( $stash->settings->newtopic_email_subject ); ?>">
				<br><br>
				<span class="description bbpnns-subject-line">
				<?php
				printf(
					/* translators: %s: comma-separated list of available tags. */
					wp_kses_post( __( '<strong>Available Tags</strong>: %s.', 'bbpress-notify-nospam' ) ),
					wp_kses_post( join( ', ', apply_filters( 'bbpnns_settings_available_topics_tags', array(), 'subject' ) ) )
				);
				?>
				</span>
			</td>
		</tr>
		
		<tr>
			<td colspan="2"><strong><?php esc_html_e( 'Topic E-mail Body', 'bbpress-notify-nospam' ); ?></strong><br>
				<?php
					wp_editor(
						$stash->settings->newtopic_email_body,
						'bbpnns_newtopic_email_body',
						array(
							'textarea_rows' => 15,
							'media_buttons' => false,
							'textarea_name' => $this->settings_name . '[newtopic_email_body]',
						)
					);
					?>
				<br>
				<span class="description bbpnns-message-body">
				<?php
				printf(
					/* translators: %s: comma-separated list of available tags. */
					wp_kses_post( __( '<strong>Available Tags</strong>: %s.', 'bbpress-notify-nospam' ) ),
					wp_kses_post( join( ', ', apply_filters( 'bbpnns_settings_available_topics_tags', array(), 'body' ) ) )
				);
				?>
				</span>
			</td>
		</tr>
		
		<input type="hidden" name="bbpnns_nullable_fields" 
value="default_topic_notification_checkbox,override_bbp_forum_subscriptions,notify_authors_topic,hidden_forum_topic_override,newtopic_recipients,forums_auto_subscribe_to_topics,forums_auto_subscribe_new_users" />
		
		<?php do_action( 'bbpnns_settings_topics_box_after_last_row' ); ?>
		
	</tbody>
</table>


<?php do_action( 'bbpnns_settings_topics_box_bottom' ); ?>

<?php
/*
 * End if file topics_body.tmpl.php
 * Location: includes/view/templates/settings/topics_body.tmpl.php
 */
