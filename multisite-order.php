<?php
/*
Plugin Name: MultiSite order
Description: Set and obtain an order for your multisite sites
Author: Mart Haarman
Author URI: https://github.com/marthaarman
Version: 1.0
Network: true
*/

if ( ! defined( 'WPINC' ) ) { // If this file is called directly, abort.
	die;
}

class MultiSite_Order {
	const VERSION              	= 1.0;
	const META_NAME				= "multisite-order";

	protected static $instance 	= null;

	protected $orders = array();


	private function __construct() {
		add_filter( 'the_sites', array( $this, 'sort_sites' ) );
	}

	/**
	 * get an instance of this class
	 * @return Multisite_Order instance
	 */
	public static function get_instance() : MultiSite_Order {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Get the sites in custom order
	 * @return object the sites in custom order
	 */
	public function get_ordered_sites() {
		$sites = get_sites();
		return $sites;
		return $this->sort_sites($sites);
	}


	/**
	 * the sort function used in the multisite-order, sorts by custom order and then by id
	 * @param wp_object a is one object to compare with the other object
	 * @param wp_boject b is one object to compare with the other object
	 * @return -1 when when smaller, 1 when bigger
	 */
	public function sorter( $a, $b ) : int {
		$o = $this->orders;
		if ( $o[$a->blog_id] == $o[$b->blog_id] ) {
			return $a->blog_id < $b->blog_id ? -1 : 1;
		} else {
			return ( $o[$a->blog_id] < $o[$b->blog_id] ) ? -1 : 1;	
		} 
	}


	/**
	 * Filter hook to sort the sites
	 *

	 */
	public function sort_sites( $sites ) {
		$this->orders = array();
		foreach($sites as $site) {
			$meta = get_site_meta($site->blog_id, self::META_NAME, true);
			$this->orders[$site->blog_id] = $meta ? $meta : 999;
		}

		usort($sites, array($this, 'sorter'));
		return $sites;
	}

	/**
	 * Set order of one of the sites in its meta, will create new meta if order never set before
	 * 
	 * @param int|object site is the site of which the order is to be set. 
	 * @param int order is the order value this site has to get (its priority)
	 */
	public function set_site_order(int|object $site, int $order) : void {
		$site_id = gettype($site) == "integer" ? $site : $site->blog_id;
		if(get_site_meta($site_id, self::META_NAME, true)) {
			update_site_meta($site_id, self::META_NAME, $order);
		}else {
			add_site_meta($site_id, self::META_NAME, $order);
		}
	}

}

if ( is_multisite() ) {

	add_action( 'plugins_loaded', array( 'MultiSite_Order', 'get_instance' ) );

	if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

		require_once plugin_dir_path( __FILE__ ) . 'admin/admin.php';
		// add_action( 'plugins_loaded', array( 'Sort_My_Sites_Admin', 'get_instance' ) );

	}
}
