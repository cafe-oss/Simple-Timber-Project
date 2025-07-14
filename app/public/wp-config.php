<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          '}Nh Ty0Dl~]o[!o$o)Cq</y$JALxp~QO]eujMD`9uBg Fd3-&D@ 5[Ics#`Ouxsi' );
define( 'SECURE_AUTH_KEY',   't(v:jeT<gTPK=tlsmto^;Fp_?0*_63?[<9Ex]aR[w-4{e6T+eydgr<ALb.]|fj+=' );
define( 'LOGGED_IN_KEY',     '-rq%pO,I)kdSGjd-N%e7#:rCb1jD2[4kB48#p_w(~et.,2]WyAbVc?:Xr3h`lQaG' );
define( 'NONCE_KEY',         'b~JRkwbq y.Q,p#<kEQ$~;(5?6;f.NF$jV!G}(bz/Buc@|BLnZ%)Gf=V.e%Y&eX2' );
define( 'AUTH_SALT',         'VvSY$`!nRYT;a{Sg%yC^P.~7gH <H(]KTTRvWeAdwEu|c;Jxz_s[8^t<E&iCM<=U' );
define( 'SECURE_AUTH_SALT',  '2H[HE[k`WTVi-B?s?rx|W%7py%(I_O-?3k&V%jvkZ dQz+L7YEZTTMgn ;%<~Fs@' );
define( 'LOGGED_IN_SALT',    '^IYZt>>{ZEp|iNMcXHR/W[^+p+~ 1_$E4e=TD )}y)$DWZ9`bAoeS!7%r)&J>$_c' );
define( 'NONCE_SALT',        'Bk?cRqYLdbu_/}_q^3W]X$_EB#FlwkOfwe*/oDLyK!F2fizpXrMnelwD~o1{9>j(' );
define( 'WP_CACHE_KEY_SALT', '#tc<(2HLpS1Yin|xRzbqkRgqv(_9UTPN]S4oxA$Ic~$W_&$HDUW|n@Aetj+/fBi*' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
