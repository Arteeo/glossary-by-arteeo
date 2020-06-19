<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package glossary
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Generates available letters
 *
 * Generates the letters shown by the glossary.
 *
 * @since 1.0.0
 *
 * @param array  $attributes @see glossary_cgb_block_render.
 * @param array  $letters {
 *     The letters which should be generated. Each letter object has the
 *     following attributes.
 *     @type string $letter The letter associated with the object.
 * }
 * @param string $current_letter The letter which is currently selected.
 */
function generate_letters( $attributes, $letters, $current_letter ) {
	$result  = '';
	$hashtag = false;
	foreach ( $letters as $letter_obj ) {
		if ( '#' !== $letter_obj->letter ) {
			if ( $letter_obj->letter === $current_letter ) {
				$result = $result . '<a class="active" style="color: ' .
						$attributes['secondaryColor'] . '; border-color: ' .
						$attributes['secondaryColor'] . ';" href="' .
						generate_url( array( 'letter' => $letter_obj->letter ) ) . '">' .
						$letter_obj->letter . '</a>';
			} else {
				$result = $result . '<a href="' .
						generate_url( array( 'letter' => $letter_obj->letter ) ) . '">' .
						$letter_obj->letter . '</a>';
			}
		} else {
			$hashtag = true;
		}
	}

	if ( $hashtag ) {
		if ( '#' === $current_letter ) {
			$result = $result . '<a class="active" style="color: ' .
					$attributes['secondaryColor'] . '; border-color: ' .
					$attributes['secondaryColor'] . ';" href="' .
					generate_url( array( 'letter' => 'hashtag' ) ) . '">#</a>';
		} else {
			$result = $result . '<a href="' .
					generate_url( array( 'letter' => 'hashtag' ) ) . '">#</a>';
		}
	}

	return $result;
}

/**
 * Generates available entries
 *
 * Generates the entries which will be shown by the glossary filtered by locale.
 *
 * @since 1.0.0
 *
 * @param array $attributes @see glossary_cgb_block_render.
 * @param array $entries {
 *     The entries which should be generated. Each entry object has the
 *     following attributes.
 *     @type string $term        The term of the entry. Default ''. Accepts any
 *                               text.
 *     @type string $description The description of the entry. Default ''.
 *                               Accepts any text.
 * }
 */
function generate_entries( $attributes, $entries ) {
	$result = '';
	foreach ( $entries as $entry ) {
		$result = $result .
			'<article class="entry">' .
			'	<div class="name">' .
			'		<h2 style="color: ' . $attributes['secondaryColor'] . ';">' .
							$entry->term .
			'   </h2>' .
			' </div>' .
			'	<div class="description">' .
			'		<p>' .
			'			' . nl2br( $entry->description ) .
			'		</p>' .
			' </div>' .
			'</article>';
	}

	return $result;
}
/**
 * Generates block for frontend
 *
 * Generates the glossary block for the frontend. Is called by render_callback
 * hook.
 *
 * @since 1.0.0
 *
 * @global $wpdb                WordPress database instance
 * @global $glossary_table_name Name of the glossary-data-table
 *
 * @param array $attributes {
 *     The attributes defined inside the gutenberg block.
 *     @type string $primary_color   The primary color of the gutenberg block.
 *                                   Default '#0065AE'. Accepts any hex color
 *                                   value.
 *     @type string $secondary_color The secondary color of the gutenberg block.
 *                                   Default '#82878c'. Accepts any hex color
 *                                   value.
 * }
 */
function glossary_cgb_block_render( $attributes ) {
	global $wpdb;
	global $glossary_table_name;

	$locale = get_locale();

	$handled = false;
	$letters = $wpdb->get_results(
		$wpdb->prepare(
			'SELECT letter FROM ' . $glossary_table_name . ' WHERE' .
			' locale = %s GROUP BY letter ORDER BY letter ASC',
			$locale
		)
	);
	$current_letter;
	$entries;

	if ( 0 === $wpdb->num_rows ) {
		$current_letter          = '?';
		$letters                 = array();
		$letters[0]              = new stdClass();
		$letters[0]->letter      = '?';
		$entries                 = array();
		$entries[0]              = new stdClass();
		$entries[0]->term        = __( 'No entry', 'glossary' );
		$entries[0]->description = __(
			'Unfortunately no entries in your language could be found in this glossary.',
			'glossary'
		);
	} else {
		if ( isset( $_GET['letter'] ) ) {
			if ( 1 === strlen( $_GET['letter'] ) ) {
				$current_letter = strtoupper( sanitize_text_field( $_GET['letter'] ) );

				$entries = $wpdb->get_results(
					$wpdb->prepare(
						'SELECT term, description FROM ' . $glossary_table_name .
						' WHERE letter = %s AND locale = %s ORDER BY term ASC',
						$current_letter,
						$locale
					)
				);

				if ( $wpdb->num_rows > 0 ) {
					$handled = true;
				}
			} elseif ( 'hashtag' === $_GET['letter'] ) {
				$current_letter = '#';

				$entries = $wpdb->get_results(
					$wpdb->prepare(
						'SELECT term, description FROM ' . $glossary_table_name . ' WHERE' .
						' letter = %s AND locale = %s ORDER BY term ASC',
						$current_letter,
						$locale
					)
				);

				if ( 0 < $wpdb->num_rows ) {
					$handled = true;
				}
			}
		}

		if ( ! $handled ) {
			if ( 1 < count( $letters ) ) {
				if ( '#' === $letters[0]->letter ) {
					$current_letter = $letters[1]->letter;
				} else {
					$current_letter = $letters[0]->letter;
				}
			} else {
				$current_letter = $letters[0]->letter;
			}
			$entries = $wpdb->get_results(
				"SELECT term, description FROM" .
				" $glossary_table_name WHERE letter = '$current_letter'" .
				' ORDER BY term ASC'
			);
		}
	}

	return '' .
		'<div class="wp-block-glossary-block-glossary"' .
				'id="wp-block-glossary-block-glossary">' .
		'	<div class="wrapper">' .
		'		<section class="sidebar">' .
		'			<div class="sidebar-header" style="background-color:' .
				$attributes['primaryColor'] . ';">' .
		'				<div class="letter">' .
		'					<h2>' . $current_letter . '</h2>' .
		'				</div>' .
		'			</div>' .
		'			<div class="sidebar-content">' .
		'				<h3 style="color:' . $attributes['secondaryColor'] . ';">' .
				__('Select a letter:', 'glossary') . '</h3>' .
		'				<div class="letters">' .
		'					' . generate_letters( $attributes, $letters, $current_letter ) .
		'				</div>' .
		'			</div>' .
		'		</section>' .
		'		<main class="content">' .
		'			' . generate_entries( $attributes, $entries, $current_letter ) .
		'		</main>' .
		'	</div>' .
		'</div>';
}

/**
 * Enqueue Gutenberg block assets for both frontend + backend.
 *
 * Assets enqueued:
 * 1. blocks.style.build.css - Frontend + Backend.
 * 2. blocks.build.js - Backend.
 * 3. blocks.editor.build.css - Backend.
 *
 * @uses {wp-blocks} for block type registration & related functions.
 * @uses {wp-element} for WP Element abstraction — structure of blocks.
 * @uses {wp-i18n} to internationalize the block's text.
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */
function glossary_cgb_block_assets() { // phpcs:ignore
	global $wpdb;
	global $glossary_table_name;
	// Register block styles for both frontend + backend.
	wp_register_style(
		'glossary-cgb-style-css', // Handle.
		plugins_url(
			'../css/block/blocks.style.build.css',
			dirname( __FILE__ )
		), // Block style CSS.
		is_admin() ? array( 'wp-editor' ) : null, // Dependency to include the CSS after it.
		filemtime( plugin_dir_path( __DIR__ ) . 'js/block/blocks.style.build.css' ) // Version: File modification time.
	);

	// Register block editor script for backend.
	wp_register_script(
		'glossary-cgb-block-js', // Handle.
		plugins_url( '../js/block/blocks.build.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ), // Dependencies, defined above.
		filemtime( plugin_dir_path( __DIR__ ) . 'js/block/blocks.build.js' ), // Version: filemtime — Gets file modification time.
		true // Enqueue the script in the footer.
	);

	wp_register_script(
		'resize-js', // Handle.
		plugins_url( '../js/resize.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
		null, // Dependencies, defined above.
		filemtime( plugin_dir_path( __DIR__ ) . 'js/block/blocks.build.js' ), // Version: filemtime — Gets file modification time.
		true // Enqueue the script in the footer.
	);

	// Register block editor styles for backend.
	wp_register_style(
		'glossary-cgb-block-editor-css', // Handle.
		plugins_url( '../css/block/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
		array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
		filemtime( plugin_dir_path( __DIR__ ) . 'css/block/blocks.editor.build.css' ) // Version: File modification time.
	);

	// WP Localized globals. Use dynamic PHP stuff in JavaScript via `cgbGlobal` object.
	wp_localize_script(
		'glossary-cgb-block-js',
		'cgbGlobal', // Array containing dynamic data for a JS Global.
		array(
			'pluginDirPath'         => plugin_dir_path( __DIR__ ),
			'pluginDirUrl'          => plugin_dir_url( __DIR__ ),
			'__Glossary'            => __( 'Glossary', 'glossary' ),
			'__glossary'            => __( 'glossary', 'glossary' ),
			'__glossaryDescription' => __( 'glossary', 'glossary' ) . ' - ' .
																	__(
																		'A simple beautiful glossary',
																		'glossary'
																	),
			'__primaryColor'        => __( 'Primary color', 'glossary' ),
			'__accentColor'         => __( 'Accent color', 'glossary' ),
			'__selectLetter'        => __( 'Select a letter:', 'glossary' ),
			'__example'             => __( 'Example', 'glossary' ),
			// Add more data here that you want to access from `cgbGlobal` object.
		)
	);

	/**
	 * Register Gutenberg block on server-side.
	 *
	 * Register the block on server-side to ensure that the block
	 * scripts and styles for both frontend and backend are
	 * enqueued when the editor loads.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type#enqueuing-block-scripts
	 * @since 1.16.0
	 *
	 * @see generate_letters for explanation of attributes array.
	 */
	register_block_type(
		'glossary/block-glossary',
		array(
			// Enqueue blocks.style.build.css on both frontend & backend.
			'style'           => 'glossary-cgb-style-css',
			'script'          => 'resize-js',
			// Enqueue blocks.build.js in the editor only.
			'editor_script'   => 'glossary-cgb-block-js',
			// Enqueue blocks.editor.build.css in the editor only.
			'editor_style'    => 'glossary-cgb-block-editor-css',
			'attributes'      => array(
				'primary_color'   => array(
					'type'    => 'string',
					'default' => '#0065AE',
				),
				'secondary_color' => array(
					'type'    => 'string',
					'default' => '#82878c',
				),
			),
			'render_callback' => 'glossary_cgb_block_render',
		)
	);
}

// Hook: Block assets.
add_action( 'init', 'glossary_cgb_block_assets' );
