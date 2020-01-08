<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
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

// ** MySQL settings ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'c/DE49WX6n25Io++xHyoQEHMNJM4r/ZoUzZzc+93EFO8ODBgvtQOpVfu928gOuOE66olKq5by/V8e7uYJOeylg==');
define('SECURE_AUTH_KEY',  'Y20HAmZfr5AHofvKKugvLxQ1Ngy1K79xLQ4Q7V9BI1cLDPdLI5QbhzUNGQy3Qftm7HEbUWGIa5SYsyBwXYNAkg==');
define('LOGGED_IN_KEY',    'RjZyoxq0JE3RhplE12YBexYZcCrgdEMC22Eq0AhcOfrZ1voKJc2Owgtty5EaalcPoJA1HNAWebi/PUd7a1Ybdg==');
define('NONCE_KEY',        '++Czfy6EM/D+BSGm57KyzE+Mi8Bvp0u0t8ACANphn1ouSeldGEB9A0hIueqQ1OuqIfREJ6KnfPscOCVvHEzXxQ==');
define('AUTH_SALT',        '6SBdkSqZjabhJNNG9fLd0ZPyL4O7kPJQVjpWQXMKR62fJzYIKyZbY1qHxlOyxjO63w3Q8Wx+MjTF99lCxFKKYw==');
define('SECURE_AUTH_SALT', 'xi7OGixhij6AFi0TS/SFTZDP0yrVakX+Jo+L4kiRanIvfhwOHn0o/obkw3GdDn0Efdrudt/fj0TxDYQ451pCRQ==');
define('LOGGED_IN_SALT',   '91bj52KEulDfLHhzRTvrT+uuHevMrFtApL8fxM9jswmBAzMR2GvLtbxEG07pG0B4Z1XqWLvxlE9yl0Dwjsionw==');
define('NONCE_SALT',       '+oq2/BLIDFmPOlpfj1aFSQCt3d9hSU2AvxI1U3qBpxNrQP6Q2qcn7TAnO1FTEhs49QBdM28vvQeO90Dw0QGXJw==');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
