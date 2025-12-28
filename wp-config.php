<?php

//Begin Really Simple SSL session cookie settings
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
//END Really Simple SSL cookie settings
define( 'WP_CACHE', true ); // Added by WP Rocket
define('WP_MEMORY_LIMIT', '512M');
define( 'EWWW_IMAGE_OPTIMIZER_USE_LQIP', true );
/*
define('WP_REDIS_CONFIG', [
    'token' => 'e279430effe043b8c17d3f3c751c4c0846bc70c97f0eaaea766b4079001c',
    'host' => '/home/bfqaombi/redis/redis.sock',
    'prefix' => 'dioutdoorvn',// change for each site
    'database' => 1,
    'maxmemory' => '512M',
    'maxttl' => 3600 * 24 * 7, // 7 days
    'timeout' => 1.0,
    'read_timeout' => 1.0,
    'prefetch' => true,
    'split_alloptions' => true,
    'strict' => true,
    'debug' => false,
    
]);



/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to 'wp-config.php' and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys  
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */
// ** MySQL settings - You can get this info from your` web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'bfqaombi_dioutdoor');
/** MySQL database username */
define('DB_USER', 'bfqaombi_dioutdoor');
/** MySQL database password */
define('DB_PASSWORD', 'P-+XlbFbKi7,^R)?Ed');
/** MySQL hostname */
define('DB_HOST', 'localhost');
/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');
/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');
/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'H~Tby{L!{|DQ/.}(M;20X=kOew{i=nI</nxP.C}j+A)8.=4vps2J^NY(Ck!]0<tO');
define('SECURE_AUTH_KEY',  'd#={.N|b$lcA(;|GjMmaN^_cte*).q&sMN]Sbcm@+WA>|+<]&L:--V-a*Kk(%4u]');
define('LOGGED_IN_KEY',    '8HZPv`%&~affEHj*2@3?i`^w`pu7qlLS1i?2b7P+D)|D)Uw_ny;%__a,i,hB~X{+');
define('NONCE_KEY',        '~@*sc**}KS 5h>+XwtttekBF~Mq+Gpn6v/M8H[2|gSa86JNLV]E2y zIc1%I]-?/');
define('AUTH_SALT',        '-u5EMs<&|<Z*^TegBu+d|hLM8%uVz1kD,vZ3hxI|hi+/f2EX9hge{Zof$fnuE!/!');
define('SECURE_AUTH_SALT', 'y9j-Di(-qBSc:p)2 {!c{$&3dFUd1Nm +2p((C&d8GE4|;G%Itv29^nf!vQUD8 +');
define('LOGGED_IN_SALT',   'NICkdg-=12aQpR{j!pMv:|v5qU?:;|OG}a| ZB[!Ww8 vf;+g>]H +(;@*>V|<O,');
define('NONCE_SALT',       'Zl0^YC&Iz-v=oN;oOU`on<(A!(`F#|Nvr7CXJgo{P}HA18]>~wN>{s9tY=rPj+hI');
/**#@-*/
/**
 * WordPress Database Table prefix.
 *t
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!onts/icomoon.ttf?nfhghw(
 */
$table_prefix  = 'erj0pc_';
/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', true);
// Enable Debug logging to the /wp-content/debug.log file
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );

/** Absolute path to the WordPress directory. */
if ( ! defined('ABSPATH') ){
	define('ABSPATH', dirname(__FILE__) . '/');
}
define('UPLOADS', 'media');
//define( 'WPMS_ON', true );hhwfzljqhtjhuvhu
//define( 'WPMS_SMTP_PASS', 'S!xqCzSqa5VqKK' );
define('DOMAIN_CURRENT_SITE', 'dioutdoor.vn');
/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
