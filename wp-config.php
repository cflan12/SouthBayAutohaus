<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */
 
// Include local configuration
if (file_exists(dirname(__FILE__) . '/local-config.php')) {
	include(dirname(__FILE__) . '/local-config.php');
}

// Global DB config
if (!defined('DB_NAME')) {
	define('DB_NAME', 'sba');
}
if (!defined('DB_USER')) {
	define('DB_USER', 'root');
}
if (!defined('DB_PASSWORD')) {
	define('DB_PASSWORD', 'root');
}
if (!defined('DB_HOST')) {
	define('DB_HOST', 'localhost');
}

/** Database Charset to use in creating database tables. */
if (!defined('DB_CHARSET')) {
	define('DB_CHARSET', 'utf8');
}

/** The Database Collate type. Don't change this if in doubt. */
if (!defined('DB_COLLATE')) {
	define('DB_COLLATE', '');
}

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '|| Rde+FImc}IKtuhmz&+!W}5!:c+:T@e)fV^XPD12h,^7@>e$g-)^?I4X%C]WGF');
define('SECURE_AUTH_KEY',  ';lp%*`OWGaw1xXcjwf(gaf bk(8+2O6!,smd<v( N cj|HabL.F`}F`!OxVR<F-x');
define('LOGGED_IN_KEY',    '>sf;0L,E}9rV|Uuy^5(xF3y<HATsX4QY7 4^F*uk=+g%m%;}ceE{2y</bhC06m}l');
define('NONCE_KEY',        'a|=|E12Q2Ld<4=/.mGvGY:<Al1?_n!.+wgv%M@J-.vhq|e2.y(#qq,7^gR6(s^wl');
define('AUTH_SALT',        'aP9|![;8rMX+Zn-u:-kbqAP2?rF]~@}yU_3 HlK(K1qkA3~sj?({bu|6>%fo`a_+');
define('SECURE_AUTH_SALT', 'T{>NTHB3l1[05J6nk||si_g34UA#I!}5ca-I?K]4FS,QKsvVb:,9hl>P67D#;*:*');
define('LOGGED_IN_SALT',   '|||YQYSU53N^H{16ClFU?vAnY|`GQ}ZoDRs`,Ym51~=}[Imxe4QMu3B>VPR)CIE+');
define('NONCE_SALT',       'DF^w!;j4t3XMXFmH,F8yi4Y:9Kx57(@6h|%GSb=2Ds2W_U DX;{+VH?4@r Psp`h');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'south_bay_autohaus';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
if (!defined('WP_DEBUG')) {
	define('WP_DEBUG', false);
}

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
