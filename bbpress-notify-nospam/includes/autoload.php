<?php
/**
 * Simple autoloader for bbPress Notify (No-Spam) classes.
 *
 * Looks for class files under `includes/` with either the legacy
 * `*.class.php` naming or a `class-*.php` naming convention.
 *
 * @package bbPress_Notify_Nospam
 */

defined( 'ABSPATH' ) || exit;

	spl_autoload_register(
		function ( $classname ) {
			$prefix = 'bbPress_Notify_noSpam';

			// Only attempt to load plugin classes (case-insensitive).
			if ( 0 !== stripos( $classname, $prefix ) ) {
				return;
			}

			$includes_dir = __DIR__; // This file is already in includes.

			// Handle the root class name specially (bbPress_Notify_noSpam).
			if ( $classname === $prefix ) {
				$slug       = strtolower( str_replace( '_', '-', $classname ) );
				$candidates = array(
					$includes_dir . '/controller/class-' . $slug . '.php',
					$includes_dir . '/class-' . $slug . '.php',
				);
			} else {
				// Remove the prefix and the following underscore.
				$suffix = substr( $classname, strlen( $prefix ) + 1 );

				// Split on underscores (class names use underscores as separators).
				$parts = preg_split( '/_+/', $suffix );

				// Normalize parts to lowercase for path building.
				$parts_lc = array_map( 'strtolower', $parts );

				// Primary directory is the first part (controller, model, dal, view, helper, etc.).
				$dir = array_shift( $parts_lc );

				// Build a kebab-style suffix from all parts (e.g. Controller_Ajax -> controller-ajax).
				$kebab_suffix = strtolower( implode( '-', array_merge( array( $dir ), $parts_lc ) ) );

				// Build a sub-directory path from any parts after the first (e.g. model/abstract/...).
				$subdirs = '';
				if ( count( $parts_lc ) > 0 ) {
					$subdirs = '/' . implode( '/', $parts_lc );
				}

				// Also build an underscored basename for legacy filenames.
				$underscored_basename = strtolower( implode( '_', array_merge( array( $dir ), $parts_lc ) ) );

				$candidates = array(
					// New WPCS-style filename with plugin prefix in the directory root.
					$includes_dir . '/' . $dir . '/class-bbpress-notify-nospam-' . $kebab_suffix . '.php',
					// New WPCS-style filename without plugin prefix in the directory root.
					$includes_dir . '/' . $dir . '/class-' . $kebab_suffix . '.php',
					// New WPCS-style filename with plugin prefix in correct subdir.
					$includes_dir . '/' . $dir . $subdirs . '/class-bbpress-notify-nospam-' . $kebab_suffix . '.php',
					// New WPCS-style filename without plugin prefix (older class-<suffix>.php style).
					$includes_dir . '/' . $dir . $subdirs . '/class-' . $kebab_suffix . '.php',
					// Possible class file in top-level includes with plugin prefix.
					$includes_dir . '/class-bbpress-notify-nospam-' . $kebab_suffix . '.php',
					// Possible class file in top-level includes without plugin prefix.
					$includes_dir . '/class-' . $kebab_suffix . '.php',
					// Legacy filenames (underscored) inside dir/subdirs.
					$includes_dir . '/' . $dir . $subdirs . '/' . $underscored_basename . '.class.php',
					$includes_dir . '/' . $dir . $subdirs . '/' . $underscored_basename . '.php',
				);
			}

			foreach ( $candidates as $file ) {
				if ( empty( $file ) ) {
					continue;
				}

				if ( file_exists( $file ) ) {
					require_once $file;
					return;
				}
			}

			// Nothing found. Do nothing and let the caller handle the missing class.
		}
	);
