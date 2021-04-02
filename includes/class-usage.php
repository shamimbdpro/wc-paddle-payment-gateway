<?php




class Ppgw_Usage {

    public function __construct() {
        // Define hooks
        add_action( 'admin_notices', array( $this, 'render_notice' ) );

        // Handle notice form submit
        add_action( 'init', array( $this, 'handle_submit' ) );

        // Add custom cron interval 
        add_filter( 'cron_schedules', array( $this, 'custom_cron_schedule' ) );

        // Define our cron action
        add_action( 'ppgw_display_notice', array( $this, 'reset_notice_click' ) );

        // Trigger cron
        add_action( 'init', array( $this, 'initialize_cron' ) );
    }


    function initialize_cron() {
        if ( ! wp_next_scheduled( 'ppgw_display_notice' ) ) {
            wp_schedule_event( time(), 'ppgw_one_month', 'ppgw_display_notice' );
        }
    }


    function reset_notice_click() {

        $usage_data_sent = get_option( 'ppgw_usage_sent', 'no' );

        if ( $usage_data_sent !== 'yes' ) {
            update_option( 'ppgw_notice_closed', 'no' );
        }
    }


    function custom_cron_schedule( $schedules ) {
        $schedules['ppgw_one_month'] = array(
            'interval' => 30*86400,
            'display'  => esc_html__( 'Every Month', 'wc-paddle-payment-gateway' ), 
        );
        return $schedules;
    }


    function handle_submit() {
        if ( isset( $_POST['action'] ) && $_POST['action'] == 'paddle_notice_form' ) {
            // This is the correct form
            if ( isset( $_POST['paddle_usage_tracking'] ) ) {
                // Send usage info
                $to          = 'divdojo@gmail.com';
                $site_url    = get_home_url();
                $wp_version  = get_bloginfo( 'version' );
                $admin_email = get_option('admin_email');

                $message = "Site url: $site_url\nWP version: $wp_version\nAdmin email: $admin_email";
                $subject = "Paddle usage data from $site_url";
                $sent    = wp_mail($to, $subject, strip_tags($message));

				if($sent) {
					update_option('ppgw_usage_sent', 'yes');
				}
				else {
					update_option('ppgw_usage_sent', 'no');
				}

                update_option( 'ppgw_notice_closed', 'yes' );
            }
            else {
                update_option( 'ppgw_notice_closed', 'yes' );
            }
        }
    }


    function render_notice() {
        $notice_already_closed = get_option( 'ppgw_notice_closed', 'no' );
        
        if ( $notice_already_closed !== 'yes' ) {
            ?> 
        
            <div class="notice notice-primary">
                <div class="paddle-notice-wrapper">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="paddle_notice_form">
                        <input checked type="checkbox" name="paddle_usage_tracking" id="">
                        <?php echo esc_html__( 'Get improved features and faster fixes by sharing ', 'wc-paddle-payment-gateway' )
                        . '<span class="non-sensitive">' . esc_html__('non-sensitive data', 'wc-paddle-payment-gateway') . '</span>'
                        . esc_html__(' via usage tracking that shows us how Paddle is used. No personal data is tracked or stored.'); ?>
                        <button class="button" type="submit"><?php esc_html_e( 'Okay!', 'wc-paddle-payment-gateway' ); ?></button>
                    </form>
                    <div class="paddle-track-list">
                        <?php esc_html_e( 'Site URL, Active plugins, Payment Gateways, WP version, PHP version, Mysql version, Admin email, Active theme, PHP settings, Active plugins', 'wc-paddle-payment-gateway' ); ?>
                    </div>
                </div>
            </div>
            
            <?php
        }
        
    }
}













