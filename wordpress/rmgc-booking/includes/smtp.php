<?php
// Add SMTP configuration
function rmgc_configure_smtp($phpmailer) {
    $phpmailer->isSMTP();
    $phpmailer->Host = get_option('rmgc_smtp_host', 'localhost');
    $phpmailer->SMTPAuth = true;
    $phpmailer->Port = get_option('rmgc_smtp_port', '587');
    $phpmailer->Username = get_option('rmgc_smtp_user', '');
    $phpmailer->Password = get_option('rmgc_smtp_pass', '');
    $phpmailer->SMTPSecure = get_option('rmgc_smtp_secure', 'tls');
    
    // Enable debug mode in logs
    $phpmailer->SMTPDebug = 2;
    $phpmailer->Debugoutput = function($str, $level) {
        rmgc_log_error('SMTP Debug', array(
            'message' => $str,
            'level' => $level
        ));
    };
}
add_action('phpmailer_init', 'rmgc_configure_smtp');

// Add SMTP settings to admin page
function rmgc_add_smtp_settings() {
    add_settings_section(
        'rmgc_smtp_settings',
        'SMTP Configuration',
        null,
        'rmgc-settings'
    );
    
    add_settings_field(
        'rmgc_smtp_host',
        'SMTP Host',
        'rmgc_text_field_callback',
        'rmgc-settings',
        'rmgc_smtp_settings',
        array('id' => 'rmgc_smtp_host', 'default' => 'localhost')
    );
    
    add_settings_field(
        'rmgc_smtp_port',
        'SMTP Port',
        'rmgc_text_field_callback',
        'rmgc-settings',
        'rmgc_smtp_settings',
        array('id' => 'rmgc_smtp_port', 'default' => '587')
    );
    
    add_settings_field(
        'rmgc_smtp_user',
        'SMTP Username',
        'rmgc_text_field_callback',
        'rmgc-settings',
        'rmgc_smtp_settings',
        array('id' => 'rmgc_smtp_user')
    );
    
    add_settings_field(
        'rmgc_smtp_pass',
        'SMTP Password',
        'rmgc_password_field_callback',
        'rmgc-settings',
        'rmgc_smtp_settings',
        array('id' => 'rmgc_smtp_pass')
    );
    
    add_settings_field(
        'rmgc_smtp_secure',
        'SMTP Security',
        'rmgc_select_field_callback',
        'rmgc-settings',
        'rmgc_smtp_settings',
        array(
            'id' => 'rmgc_smtp_secure',
            'options' => array(
                'tls' => 'TLS',
                'ssl' => 'SSL',
                'none' => 'None'
            ),
            'default' => 'tls'
        )
    );
    
    register_setting('rmgc_settings', 'rmgc_smtp_host');
    register_setting('rmgc_settings', 'rmgc_smtp_port');
    register_setting('rmgc_settings', 'rmgc_smtp_user');
    register_setting('rmgc_settings', 'rmgc_smtp_pass');
    register_setting('rmgc_settings', 'rmgc_smtp_secure');
}
add_action('admin_init', 'rmgc_add_smtp_settings');