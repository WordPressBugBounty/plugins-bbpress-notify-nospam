<?php defined( 'ABSPATH' ) or die( "No direct access allowed" );
/**
 * Plugin settings model.
 * @author vinnyalves
 */
class bbPress_Notify_noSpam_Model_Settings {

    /**
     * Declare properties as private so we can use __set() and __get() on them
     * @var string
     */
    
    
    /**
     * Whether to UTF-8 encode or not the subject line 
     * @var bool
     */
    private $encode_subject = false;
    
    /**
     * The type of message to be sent out. plain, html, multipart 
     * @var unknown
     */
    private $email_type = 'html';

    
    /**
     * Whether to send messages in the background
     * @var bool
     */
    private $newtopic_background = false;
    private $newreply_background = false;
    private $background_notifications = false;
    
    
    /**
     * This controls the default status of the 'Send Notifications' checkbox in the New Post Admin UI
     * @var bool
     */
    private $default_topic_notification_checkbox = false;
    private $default_reply_notification_checkbox = false;
    
    
    /**
     * Whether to take over Core bbPress subscriptions to forums
     * @var bool
     */
    private $override_bbp_forum_subscriptions = false;
    private $override_bbp_topic_subscriptions = false;
    private $include_bbp_forum_subscriptions_in_replies = false;
    private $forums_auto_subscribe_to_topics = false;
    private $forums_auto_subscribe_new_users = false;
    
    
    /**
     * The list of roles to use for topic notification recipients
     * @var array
     */
    private $newtopic_recipients = array();
    private $newreply_recipients = array();
    
    
    /**
     * Whether to notify authors of their own topics and replies
     * @var bool
     */
    private $notify_authors_topic = false;
    private $notify_authors_reply = false;
    
    
    /**
     * Whether to force admin-only emails if a forum is hidden
     * @var bool
     */
    private $hidden_forum_topic_override = false;
    private $hidden_forum_reply_override = false;
    
    
    /**
     * The Email Subject line for notifications
     * @var string
     */
    private $newtopic_email_subject = '';
    private $newreply_email_subject = '';
    
    
    /**
     * The Email body template for notifications
     * @var string
     */
    private $newtopic_email_body = '';
    private $newreply_email_body = '';

    
    /**
     * The custom From email information
     * @var string
     */
    private $from_name  = '';
    private $from_email = '';
    
    
    /**
     * Internal vars
     * @var unknown
     */
    protected $domain;
    protected $option_keys;
    
    
    ########################

    /**
     * The constructor - it overrides any default settings with whatever is in $params 
     * @param array $params
     */
    public function __construct( $params=array() ) 
    {
        // Translate keys
        $this->translate_options();
        
        $this->set_properties( $params );
        
        /**
         * Some defaults
         */
        $this->maybe_set_default( 'all' );
    }
    
    /**
     * Sets default values for some properties
     * @param string $key
     */
    public function maybe_set_default( $key='' )
    {
    	if ( 'all' === $key || 'newtopic_email_subject' === $key )
    	{
    		if ( empty( $this->newtopic_email_subject ) )
    			$this->newtopic_email_subject = __( '[[blogname]] New topic: [topic-title]', 'bbpress-notify-nospam' ) ;
    	}
    	
    	if ( 'all' === $key || 'newreply_email_subject' === $key )
    	{
    		if ( empty( $this->newreply_email_subject ) )
    			$this->newreply_email_subject = __( '[[blogname]] New reply for [topic-title]', 'bbpress-notify-nospam' ) ;
    	}
    	
    	if ( 'all' === $key || 'newtopic_email_body' === $key )
    	{
    		if ( empty( $this->newtopic_email_body ) )
    			$this->newtopic_email_body    = __( "Hello!\nA new topic has been posted by [topic-author].\nTopic title: [topic-title]\nTopic url: [topic-url]\n\nExcerpt:\n[topic-excerpt]", 'bbpress-notify-nospam' ) ;
    	}
    	
    	if ( 'all' === $key || 'newreply_email_body' === $key )
    	{
    		if ( empty( $this->newreply_email_body ) )
    			$this->newreply_email_body    = __( "Hello!\nA new reply has been posted by [reply-author].\nTopic title: [reply-title]\nTopic url: [reply-url]\n\nExcerpt:\n[reply-excerpt]", 'bbpress-notify-nospam' ) ;
    	}
    	
    	// Remove this default as we want to allow no roles to be selected
//     	if ( 'all' === $key || 'newtopic_recipients' === $key )
//     	{
//     		if ( empty( $this->newtopic_recipients ) )
//     			$this->newtopic_recipients = array( 'administrator' );
//     	}
    	
//     	if ( 'all' === $key || 'newreply_recipients' === $key )
//     	{
//     		if ( empty( $this->newreply_recipients ) )
//     			$this->newreply_recipients = array( 'administrator' );
//     	}
    }
    
    
    /**
     * Make keys readable if displaying any errors.
     */
    public function translate_options()
    {
        $this->option_keys = array(
            'newtopic_email_subject'              => __( 'New Topic Email Subject', 'bbpress-notify-nospam' ) ,
            'newreply_email_subject'              => __( 'New Reply Email Subject', 'bbpress-notify-nospam' ) ,
            'newtopic_email_body'                 => __( 'New Topic Email Body', 'bbpress-notify-nospam' ) ,
            'newreply_email_body'                 => __( 'New Reply Email Body', 'bbpress-notify-nospam' ) ,
            'newtopic_recipients'                 => __( 'Recipients for New Topics', 'bbpress-notify-nospam' ) ,
            'newreply_recipients'                 => __( 'Recipients for New Replies', 'bbpress-notify-nospam' ) ,
            'encode_subject'                      => __( 'Encode Subject', 'bbpress-notify-nospam' ) ,
            'newtopic_background'                 => __( 'Notify New Topics in Background', 'bbpress-notify-nospam' ) ,
            'newreply_background'                 => __( 'Notify New Replies in Background', 'bbpress-notify-nospam' ) ,
            'background_notifications'            => __( 'Background Notifications', 'bbpress-notify-nospam' ) ,
            'default_topic_notification_checkbox' => __( 'Default Topic Notification Checkbox', 'bbpress-notify-nospam' ) ,
            'default_reply_notification_checkbox' => __( 'Default Reply Notification Checkbox', 'bbpress-notify-nospam' ) ,
            'override_bbp_forum_subscriptions'    => __( 'Override bbPress Forum Subscriptions', 'bbpress-notify-nospam' ) ,
            'override_bbp_topic_subscriptions'    => __( 'Override bbPress Topic Subscriptions', 'bbpress-notify-nospam' ) ,
            'include_bbp_forum_subscriptions_in_replies' => __( 'Also notify <em>forum</em> subscribers of new replies', 'bbpress-notify-nospam' ) ,
            'forums_auto_subscribe_to_topics'     => __( 'Automatically subscribe forum subscribers to new topics.', 'bbpress-notify-nospam' ) ,
            'forums_auto_subscribe_new_users'     => __( 'Automatically subscribe new users to all forums.', 'bbpress-notify-nospam' ) ,
            'notify_authors_topic'                => __( 'Notify Authors of their Topics', 'bbpress-notify-nospam' ) ,
            'notify_authors_reply'                => __( 'Notify Authors of their Replies', 'bbpress-notify-nospam' ) ,
            'hidden_forum_topic_override'         => __( 'Only Notify Admins if Forum is Hidden', 'bbpress-notify-nospam' ) ,
            'hidden_forum_reply_override'         => __( 'Only Notify Admins if Topic is Hidden', 'bbpress-notify-nospam' ) ,
            'email_type'                          => __( 'Message Type', 'bbpress-notify-nospam' ) ,
            'from_name'                           => __( 'From Name', 'bbpress-notify-nospam' ) ,
            'from_email'                          => __( 'From Email', 'bbpress-notify-nospam' ) ,
        );
        
    }
    
    
    /**
     * Our getter. Used mainly because we have to _validate the setter
     * and getter/setter magic methods only get called for private properties
     * @param string $key
     */
    public function __get( $key )
    {
    	$value = $this->{$key};
    	
    	// Fix badly converted recipients array on the fly.
    	if ( 'newtopic_recipients' === $key || 'newreply_recipients' === $key )
    	{
    		if ( ! empty( $value ) && ! isset( $value[0] ) )
    		{
    			$value = array_keys( $value );
    		}
    	}
    	elseif( ( 'newtopic_email_subject' === $key || 'newreply_email_subject' === $key ) && $this->encode_subject )
    	{
    		// De-entitize HTML if UTF-8 subjects have been set
    		$value = html_entity_decode( $value );
    	}
    	
        return $value;
    }
    
    
    /**
     * Our setter, takes care of validating input.
     * @param string $key
     * @param mixed $val
     */
    public function __set( $key, $val )
    {
    	if ( 'message_type' === $key ) {
    		$key = 'email_type';
    	}
    	
        $val = $this->_validate( $key, $val );
        
        $this->{$key} = $val;
    }
    

    /**
     * Property setter
     * @param array $params
     */
    private function set_properties( $params=array() )
    {
        foreach ( $params as $key => $val )
        {
            $val = $this->_validate( $key, $val );
            
            $this->{$key} = $val;
        }
    }
    

    /**
     * Used by the WP Settings API. See settings_dao.class.php
     * @param string $key
     * @param mixed $val
     * @return Ambigous <mixed, string, boolean>
     */
    public function is_valid( $key, $val )
    {
        return $this->_validate( $key, $val, false );
    }
    
    
    /**
     * Setter validation
     * @param string $key
     * @param mixed $val
     * @param boolean $die_on_error - whether to throw wp_die() or return false on errors
     * @return Ambigous <mixed, string, boolean>
     */
    private function _validate( $key, $val, $die_on_error=true )
    {
        if ( ! property_exists( $this, $key ) )
        {
            return $die_on_error ? 
                   wp_die( __( sprintf( 'Invalid property %s for Settings Model', $key ), 'bbpress-notify-nospam' )  ) :
                   false;
        }

        // Validate each key/value pair
        switch( $key )
        {
            case 'encode_subject':
            case 'newtopic_background':
            case 'newreply_background':
            case 'background_notifications':
            case 'default_topic_notification_checkbox':
            case 'default_reply_notification_checkbox':
            case 'override_bbp_forum_subscriptions':
            case 'override_bbp_topic_subscriptions':
            case 'include_bbp_forum_subscriptions_in_replies':
            case 'forums_auto_subscribe_to_topics':
            case 'forums_auto_subscribe_new_users':
            case 'notify_authors_topic':
            case 'notify_authors_reply':
            case 'hidden_forum_topic_override':
            case 'hidden_forum_reply_override':
                $val = (bool) $val;
                break;
            case 'email_type':
                if ( ! in_array( $val, array( 'html', 'plain', 'multipart' ) ) )
                {
                    return $die_on_error ?
                    wp_die( __( 'Invalid value for Message Type', 'bbpress-notify-nospam' )  ) :
                    false;
                    
                    unset($val);
                }
                break;
            case 'newtopic_recipients':
            case 'newreply_recipients':
                if ( ! is_array( $val ) )
                {
                    return $die_on_error ?
                    wp_die( __( sprintf( 'Invalid data type for %s', $this->option_keys[$key] ), 'bbpress-notify-nospam' )  ) :
                    false;
                    
                    $this->set_default( $key );
                    $val = $this->{$key};
                }
                break;
            case 'newtopic_email_subject':
            case 'newreply_email_subject':
            case 'newtopic_email_body':
            case 'newreply_email_body':
                $val = trim( $val );
                if ( empty( $val ) )
                {
                    return $die_on_error ?
                    wp_die( __( sprintf( '%s cannot be empty!', $this->option_keys[$key] ), 'bbpress-notify-nospam' )  ) :
                    false;
                    
                    $this->set_default( $key );
                    $val = $this->{$key};
                }
                break;
            case 'from_name':
            case 'from_email':
                $val = trim($val);
                break;
            default:
                break;
        }
        
        return $val;
    }
    
    
    public function as_array()
    {
    	$vars = array();
    	
    	foreach ( $this->option_keys as $key => $val )
    	{
    		$vars[$key] = $this->{$key};
    	}

    	return $vars;
    }
}

/* End of file settings_model.class.php */
/* Location: bbpress-notify-nospam/includes/model/settings.class.php */
