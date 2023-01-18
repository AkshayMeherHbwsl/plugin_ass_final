<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://akshay meher.com
 * @since      1.0.0
 *
 * @package    Wpb
 * @subpackage Wpb/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wpb
 * @subpackage Wpb/includes
 * @author     Akshay Meher <akshay.meher.21@gmail.com>
 */
class Wpb {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wpb_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WPB_VERSION' ) ) {
			$this->version = WPB_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wpb';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wpb_Loader. Orchestrates the hooks of the plugin.
	 * - Wpb_i18n. Defines internationalization functionality.
	 * - Wpb_Admin. Defines all hooks for the admin area.
	 * - Wpb_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpb-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpb-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wpb-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wpb-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/wp_book_cust_widget.php';

		$this->loader = new Wpb_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wpb_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wpb_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wpb_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// action hook for custom post type "Book"
		$this->loader->add_action( 'init', $plugin_admin, 'custom_post_type_book' );

		// action hook for custom hierarchical taxonomy Book Category
		$this->loader->add_action( 'init', $plugin_admin, 'custom_category_book' );
		
		// action hook for custom non-hierarchical taxonomy Book Tag
		$this->loader->add_action( 'init', $plugin_admin, 'custom_tag_book' );

		// action hook for registering the custom table named bookmeta
		$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'pw_register_bookmeta_table' );

		// action hook for custom metabox
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'custom_metabox_books' );

		// action hook to store metadata of custom metabox book
		$this->loader->add_action( 'save_post', $plugin_admin, 'save_custom_metabox_data', 10, 2 );

		// action hook for admin_menu
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'book_menu' );

		// action hook to include post type Book as post to show Book posts in post archive.
		$this->loader->add_action( 'pre_get_posts', $plugin_admin, 'namespace_add_custom_types' );

		// action hook to display widget on dashboard as top 5 categories of book post type based on their count
		$this->loader->add_action( 'wp_dashboard_setup', $plugin_admin, 'custom_dashboard_widgets' );

		// action hook to register the settings for book
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_book_settings');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wpb_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Create Shortcode named book to show information about book
		add_shortcode( "book", array( $plugin_public, 'load_book_content') );

		// action hook to display custom widget which shows books of selected category
		add_action('widgets_init', 'wp_book_widget_init');

		// action hook to make international localize
		add_action(
			'plugins_loaded',
			function () {
				load_plugin_textdomain( 'wpb' , false, dirname(plugin_basename(__FILE__)).'/languages/' );
			}
		);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wpb_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
