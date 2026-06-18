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
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'word' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '1234' );

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
define( 'AUTH_KEY',         'YFDx#tBPsdi_ DiG-7y6ec@Qt!P4F[!#mc#CpA OC2H-Ff&{ZgcDE]Uf&I183Ml-' );
define( 'SECURE_AUTH_KEY',  'V/ig3u8XrcY}aVkiZ|Jg%#q`7YkIx&aPiA|i:DX[U>Qo{=F3[B.$6*<oA>3,{,%{' );
define( 'LOGGED_IN_KEY',    'N)L)~.kvn_F/Z%TjC;T2`mqn3_EI{j~:@tFzpS3jb)vC=>t4;>*o[i2r#<Xt3K?2' );
define( 'NONCE_KEY',        '^gg_M*q2{eY_6fq,GS!ZLi9Q9&-eMDE;M2FVz$R$rV>c=qE7]lTr4-~vIb[Bonkv' );
define( 'AUTH_SALT',        'P-} TJBj70rDL[w!IN&q%]on{{px)x4&qSs#_-OnWp BM3j$%|e6OOx[vF.R,{-e' );
define( 'SECURE_AUTH_SALT', 'ekS[%Nuf}<.|$|LoY)Qv!%Y2> e$gEVThP.&+b2BhF9nmu%fNk3G4 q7Cv1*r<=0' );
define( 'LOGGED_IN_SALT',   'sIm%qvwt3#Cu5`hZVJ)LT|i$;E>y3 :%8{ZS1&i* 6OBfo7:>g{(lw2yy=;5s^e;' );
define( 'NONCE_SALT',       'xz2D8owSD{Nz)WOw-t&fguEyk#.{$`?X8$f:/Bf@#9*}R/2Wi,4IR~H]=d#^1sZ/' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
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
