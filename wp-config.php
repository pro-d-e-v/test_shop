<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'sql_work_wpdev_c' );

/** Database username */
define( 'DB_USER', 'sql_work_wpdev_c' );

/** Database password */
define( 'DB_PASSWORD', 'TsJ4SrcGSDrspbTd' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'o-[?i+st{LX(9Viz?>}W1X|ZjcH0X+@Gk0uypl#>ASm`<U5u>uzH$J!4?7LU1/8J' );
define( 'SECURE_AUTH_KEY',  '::-zaPk)gJg>S)$&]QYQh{^)^H8^[IgU?R+)g<0Ow;:C3(F`e5:R@UTY)^Q#e[lg' );
define( 'LOGGED_IN_KEY',    'C-.y7Jb45f|3,kU6dRbBI+$*`4rqSBBt%MejW$U3)0B1K(M{SS7&D$kR wswz6#7' );
define( 'NONCE_KEY',        '~prs+n}~,]E_}8L ?U)%Mv,b`xhgvTz?O38^Yyv|Z*kIY!<&Q<Bvxob26XnUgQja' );
define( 'AUTH_SALT',        'WtN%sAi$m)}o9Cs(uX5}>JP|;?6&GU[|.m9z.VTbKYv||?7S9om]x5{|7+U}M9$!' );
define( 'SECURE_AUTH_SALT', '-!9|wue/j:d,?Zq,BggE+*gXnU}6ATXS*w1k]jcVuc{@X9`?yaPlZb@.=:l}x=qD' );
define( 'LOGGED_IN_SALT',   '*9GW`$k&^N8pp!VIff?R3jl#i/nBj,WaEx&*n+%m&FSwL<H%=Lnl26<ODLcXTO5A' );
define( 'NONCE_SALT',       'bl*dmPl[abkXd$-qwpukLb-E]b>kG*AkNweTwAOLt!tmb9;~O.n%TxMspVZ*uXHl' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
