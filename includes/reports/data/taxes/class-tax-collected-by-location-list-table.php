<?php
/**
 * Tax Collected by Location list table.
 *
 * @package     EDD
 * @subpackage  Reports/Data/Customers
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data\Taxes;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Admin\List_Table;
use EDD\Reports;

/**
 * Tax_Collected_by_Location class.
 *
 * @since 3.0
 */
class Tax_Collected_By_Location extends List_Table {

	/**
	 * Get things started
	 *
	 * @since 1.5
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'report-tax-collected-by-location',
			'plural'   => 'report-tax-collected-by-locationss',
			'ajax'     => false,
		) );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 2.5
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'label';
	}

	/**
	 * Render each column.
	 *
	 * @since 1.5
	 *
	 * @param array $item Contains all the data of the downloads
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Column names.
	 *
	 * @since 3.0
	 *
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		return array(
			'country'  => __( 'Country/Region', 'easy-digital-downloads' ),
			'from'     => __( 'From', 'easy-digital-downloads' ),
			'to'       => __( 'To', 'easy-digital-downloads' ),
			'gross'    => __( 'Gross', 'easy-digital-downloads' ),
			'tax'      => __( 'Tax', 'easy-digital-downloads' ),
			'net'      => __( 'Net', 'easy-digital-downloads' ),
 		);
	}

	/**
	 * Query data for the list table.
	 *
	 * @since 3.0
	 *
	 * @return array $data All the data for the list table.
	 */
	public function get_data() {
		global $wpdb;

		$data        = array();
		$countries   = array();
		$regions     = array();
		$tax_rates   = edd_get_tax_rates( array(), OBJECT );
		$date_filter = Reports\get_filter_value( 'dates' );

		// Date query.
		$date_query  = '';

		if ( ! empty( $date_filter['from'] ) && '0000-00-00 00:00:00' !== $date_filter['from'] ) {
			$date_query .= $wpdb->prepare( " AND {$wpdb->edd_orders}.date_created >= %s", esc_sql( date( 'Y-n-d H:i:s', EDD()->utils->date( $date_filter['from'], null, true )->endOfDay()->timestamp ) ) );
		}

		if ( ! empty( $date_filter['to'] ) && '0000-00-00 00:00:00' !== $date_filter['to'] ) {
			$date_query .= $wpdb->prepare( " AND {$wpdb->edd_orders}.date_created <= %s", esc_sql( date( 'Y-n-d H:i:s', EDD()->utils->date( $date_filter['to'], null, true )->endOfDay()->timestamp ) ) );
		}

		$from = empty( $date_filter['from'] ) || '0000-00-00 00:00:00' === $date_filter['from']
				? '&mdash;'
				: edd_date_i18n( EDD()->utils->date( $date_filter['from'], null, true )->startOfDay()->timestamp );

		$to = empty( $date_filter['to'] ) || '0000-00-00 00:00:00' === $date_filter['to']
			? '&mdash;'
			: edd_date_i18n( EDD()->utils->date( $date_filter['to'], null, true )->endOfDay()->timestamp );

		/*
		 * We need to first calculate the total tax collected for all orders so we can determine the amount of tax collected for the global rate
		 *
		 * The total determined here will be reduced by the amount collected for each specified tax rate/region.
		 */
		$all_orders = $wpdb->get_results( "
			SELECT SUM(tax) as tax, SUM(total) as total
			FROM {$wpdb->edd_orders}
			WHERE 1=1 {$date_query}
		", ARRAY_A );

		foreach ( $tax_rates as $tax_rate ) {

			$location = edd_get_country_name( $tax_rate->name );

			if ( ! empty( $tax_rate->description ) ) {
				$location .= ' &mdash; ' . edd_get_state_name( $tax_rate->name, $tax_rate->description );
			}

			$region = ! empty( $tax_rate->description )
				? $wpdb->prepare( ' AND region = %s', esc_sql( $tax_rate->description ) )
				: '';

			$results = $wpdb->get_results( $wpdb->prepare( "
				SELECT SUM(tax) as tax, SUM(total) as total, country, region
				FROM {$wpdb->edd_orders}
				INNER JOIN {$wpdb->edd_order_addresses} ON {$wpdb->edd_order_addresses}.order_id = {$wpdb->edd_orders}.id
				WHERE {$wpdb->edd_order_addresses}.country = %s {$region} {$date_query}
			", esc_sql( $tax_rate->name ) ), ARRAY_A );

			$all_orders[0]['tax']   -= $results[0]['tax'];
			$all_orders[0]['total'] -= $results[0]['total'];

			$data[] = array(
				'country'  => $location,
				'from'     => $from,
				'to'       => $to,
				'gross'    => edd_currency_filter( edd_format_amount( floatval( $results[0]['total'] ) ) ),
				'tax'      => edd_currency_filter( edd_format_amount( floatval( $results[0]['tax'] ) ) ),
				'net'      => edd_currency_filter( edd_format_amount( floatval( $results[0]['total'] - $results[0]['tax'] ) ) ),
			);
		}

		if( $all_orders[0]['total'] > 0 && $all_orders[0]['tax'] > 0 ) {

			$data[] = array(
				'country'  => __( 'Global Rate', 'easy-digital-downloads' ),
				'from'     => $from,
				'to'       => $to,
				'gross'    => edd_currency_filter( edd_format_amount( floatval( max( 0, $all_orders[0]['total'] ) ) ) ),
				'tax'      => edd_currency_filter( edd_format_amount( floatval( max( 0, $all_orders[0]['tax'] ) ) ) ),
				'net'      => edd_currency_filter( edd_format_amount( floatval( max( 0, $all_orders[0]['total'] - $all_orders[0]['tax'] ) ) ) ),
			);

		}

		return $data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @since 1.5
	 * @uses EDD_Gateway_Reports_Table::get_columns()
	 * @uses EDD_Gateway_Reports_Table::get_sortable_columns()
	 * @uses EDD_Gateway_Reports_Table::reports_data()
	 * @return void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->get_data();
	}

	/**
	 * Return empty array to disable sorting.
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array();
	}

	/**
	 * Return empty array to remove bulk actions.
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array();
	}

	/**
	 * Hide pagination.
	 *
	 * @since 3.0
	 *
	 * @param string $which
	 */
	protected function pagination( $which ) {

	}

	/**
	 * Hide table navigation.
	 *
	 * @since 3.0
	 *
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {

	}
}