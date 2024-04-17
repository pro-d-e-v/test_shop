<?php

namespace TinySolutions\cptwooint\Controllers;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'This script cannot be accessed directly.' );
}

class Installation {
    /**
     * @return void
     */
    public static function activate() {
        if ( ! get_option( 'cptwooint_plugin_version' ) ) {
            $options = get_option( 'cptwooint_settings' , [] );
            $get_activation_time = strtotime( 'now' );

            update_option( 'cptwooint_settings', $options );
            update_option('cptwooint_plugin_version', CPTWI_VERSION);
            update_option('cptwooint_plugin_activation_time', $get_activation_time);
        }
    }

    /**
     * @return void
     */
    public static function deactivation() { }

}