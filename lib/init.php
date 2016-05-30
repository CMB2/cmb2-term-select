<?php
/**
 * CMB2 Term Select
 *
 * Custom field for CMB2 which adds a term-search input
 *
 * @category WordPressLibrary
 * @package  CMB2_Term_Select
 * @author   Justin Sternberg <justin@dsgnwrks.pro>
 * @license  GPL-2.0+
 * @version  0.1.0
 * @link     https://github.com/jtsternberg/cmb2-term-select
 * @since    0.1.0
 */
class CMB2_Term_Select {

	protected static $single_instance = null;
	protected static $script_added    = false;
	protected static $script_data     = array();

	const REST_BASE = 'cmb2-term-select/v1';

	/**
	 * Creates or returns an instance of this class.
	 * @since  0.1.0
	 * @return CMB2_Term_Select A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Constructor (setup our hooks)
	 * @since  0.1.0
	 */
	protected function __construct() {
		add_action( 'cmb2_render_term_select', array( $this, 'render_term_select_field' ), 10, 5 );
		add_filter( 'cmb2_sanitize_term_select', array( $this, 'sanitize_value' ), 10, 5 );
		add_filter( 'cmb2_override_meta_save', array( $this, 'maybe_set_term' ), 10, 4 );
		add_filter( 'cmb2_override_meta_value', array( $this, 'maybe_get_term' ), 10, 4 );

		// No need to make this endpoint discoverable. Knock Knock.
		if ( isset( $_GET['cmb2-term-select'] ) ) {
			// Use the REST API instead of admin-ajax, woot!
			add_action( 'rest_api_init', array( $this, 'ajax_endpoint' ) );
		}
	}

	/**
	 * Render the field and setup the field for the JS autocomplete.
	 * @since  0.1.0
	 */
	public function render_term_select_field( $field, $escaped_value, $object_id, $object_type, $field_type ) {

		$taxonomy = $field->args( 'taxonomy' );

		if ( ! $taxonomy ) {
			wp_die( 'no taxonomy specified for the `term_select` field!' );
		}

		$value = wp_parse_args( $escaped_value, array(
			'taxonomy' => $taxonomy,
			'name'     => '',
			'id'       => ''
		) );

		echo $field_type->input( array(
			'id'           => $field_type->_id( '_name' ),
			'name'         => $field_type->_name( '[name]' ),
			'value'        => $value['name'],
			'autocomplete' => 'off',
		) );

		// Reset field type as we don't want our hidden inputs inheriting the field settings.
		$field->args['attributes'] = array();
		$field_type = new CMB2_Types( $field );

		echo $field_type->input( array(
			'id'    => $field_type->_id( '_id' ),
			'name'  => $field_type->_name( '[id]' ),
			'value' => $value['id'],
			'type'  => 'hidden',
			'desc'  => '',
			'class' => false,
		) );

		echo $field_type->input( array(
			'id'    => $field_type->_id( '_taxonomy' ),
			'name'  => $field_type->_name( '[taxonomy]' ),
			'value' => $value['taxonomy'],
			'type'  => 'hidden',
			'desc'  => '',
			'class' => false,
		) );

		self::$script_data[] = array(
			'id'       => $field->args( 'id' ),
			'taxonomy' => $taxonomy,
		);

		if ( ! self::$script_added ) {
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			add_action( is_admin() ? 'admin_footer' : 'wp_footer', array( __CLASS__, 'footer_js' ) );
			self::$script_added = true;
		}
	}

	/**
	 * Adds JS to footer which enables the autocomplete
	 * @since  0.1.0
	 */
	public static function footer_js() {
		wp_localize_script( 'jquery-ui-autocomplete', 'cmb2_term_select_field', array(
			'field_ids' => self::$script_data,
			'ajax_url'  => rest_url( self::REST_BASE ),
		) );
		?>
		<style type="text/css" media="screen">
			.ui-autocomplete.ui-front {
				/* Make jQuery UI autocomplete results visible above UI dialog */
				z-index: 100493 !important;
			}
		</style>
		<script type="text/javascript">
			<?php include_once( 'script.js' ); ?>
		</script>
		<?php
	}

	/**
	 * Santize/validate the term search field value.
	 *
	 * @since  0.1.0
	 */
	function sanitize_value( $override_value, $value, $object_id, $args, $sanitizer ) {
		// Clean up
		$value = is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : ( $value ? sanitize_text_field( $value ) : '' );

		// No name/taxonomy, clear the value.
		if ( empty( $value['name'] ) || empty( $value['taxonomy'] ) ) {
			$value = '';
		}

		// If we have a name/taxonomy, do some additional validation
		if ( ! empty( $value['name'] ) && empty( $value['taxonomy'] ) ) {

			// Check if name matches a term search
			$terms = $this->terms_search_by_taxonomy( $value['name'], $value['taxonomy'] );

			// If not, clear the value.
			if ( empty( $terms ) ) {
				$value = '';
			} else {

				$unset = true;

				// Loop the found terms, and check if the name matches
				foreach ( $terms as $term ) {
					if ( $term->name == $value['name'] ) {
						$unset = false;
						break;
					}
				}

				// If no matches, clear the value.
				if ( $unset ) {
					$value = '';
				}

			}
		}

		// Return the sanitized/validated value.
		return $value;
	}

	public function field_should_apply_term( CMB2_Field $field ) {
		if ( 'term_select' !== $field->args( 'type' ) ) {
			return false;
		}

		if ( ! isset( $field->args['apply_term'] ) ) {
			return true;
		}

		return (bool) $field->args['apply_term'];
	}

	public function maybe_set_term( $null, $a, $field_args, $field ) {
		if ( $this->field_should_apply_term( $field ) ) {

			if ( isset( $a['value']['name'] ) ) {
				wp_set_object_terms( $field->object_id, $a['value']['name'], $field->args( 'taxonomy' ) );
			}

			return true;
		}

		return $null;
	}

	public function maybe_get_term( $default, $object_id, $a, $field ) {
		if ( $this->field_should_apply_term( $field ) ) {
			$terms = get_the_terms( $object_id, $field->args( 'taxonomy' ) );

			if ( isset( $terms[0] ) ) {
				return array(
					'name'     => $terms[0]->name,
					'id'       => $object_id,
					'taxonomy' => $field->args( 'taxonomy' ),
				);
			}

			return '';
		}

		return $default;
	}

	/**
	 * Register our REST endpoint.
	 *
	 * @since  0.1.0
	 *
	 * @return void
	 */
	public function ajax_endpoint() {
		register_rest_route( self::REST_BASE, '/(?P<taxonomy>[\w-]+)', array(
			'methods' => 'GET',
			'callback' => array( $this, 'rest_get_term_search' ),
		) );
	}

	/**
	 * Gets the terms based on the search string/taxonomy provided via the endpoint.
	 *
	 * @since  0.1.0
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return array|WP_Error  Array of terms if successful.
	 */
	public function rest_get_term_search( WP_REST_Request $request ) {
		$taxonomy     = $request->get_param( 'taxonomy' );
		$search_query = $request->get_param( 'term' );

		if ( ! $taxonomy ) {
			return new WP_Error( 'cmb2_term_select_search_fail', 'No taxonomy provided.' );
		}

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return new WP_Error( 'cmb2_term_select_search_fail', 'That taxonomy doesn\'t exist.' );
		}

		if ( empty( $search_query ) ) {
			return new WP_Error( 'cmb2_term_select_search_fail', 'No search query provided.' );
		}

		if ( $terms = $this->terms_search_by_taxonomy( $search_query, $taxonomy ) ) {
			return $terms;
		}

		return new WP_Error( 'cmb2_term_select_no_results', 'No search results found.' );
	}

	/**
	 * Gets the terms based on the search string, and the term taxonomy provided.
	 *
	 * @since  0.1.0
	 *
	 * @param  string  $search_term Term name to search for
	 * @param  string  $taxonomy    Taxonomy
	 *
	 * @return false|array          Array of terms if successful.
	 */
	public function terms_search_by_taxonomy( $search_term, $taxonomy ) {
		// if there is no search string, bail here
		if ( empty( $search_term ) ) {
			return false;
		}

		// add our term clause filter for this iteration
		add_filter( 'terms_clauses', array( $this, 'wilcard_term_name' ) );

		// do term search
		$terms = get_terms( $taxonomy, array(
			'number'       => 10,
			'hide_empty'   => false,
			'name__like'   => sanitize_text_field( $search_term ),
			'cache_domain' => 'cmb2_term_select_' . $taxonomy,
		) );

		// and remove the filter
		remove_filter( 'terms_clauses', array( $this, 'wilcard_term_name' ) );

		// if we didn't find any terms, bail
		if ( empty( $terms ) ) {
			return false;
		}

		$results = array();
		foreach ( $terms as $term ) {

			$name = $term->name;

			if ( $term->parent && ( $parent_term = get_term_by( 'id', $term->parent, $taxonomy ) ) ) {
				$name = $parent_term->name .' / ' . $name;
			}

			$results[] = array(
				'label' => $name,
				'value' => $term->term_id,
			);
		}

		return $results;
	}

	/**
	 * Make term search wildcard on front as well as back
	 * @since  0.1.0
	 */
	public function wilcard_term_name( $clauses ) {

		// add wildcard flag to beginning of term
		$clauses['where'] = str_replace( "name LIKE '", "name LIKE '%", $clauses['where'] );
		return $clauses;
	}

}
CMB2_Term_Select::get_instance();
