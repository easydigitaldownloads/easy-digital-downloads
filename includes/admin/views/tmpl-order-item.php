<?php
/**
 * Order Overview: Item
 *
 * @package     EDD
 * @subpackage  Admin/Views
 * @copyright   Copyright (c) 2020, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

$view_url = add_query_arg(
	array(
		'action' => 'edit',
	),
	admin_url( 'post.php' )
);
?>

<td class="has-row-actions column-primary">
	<div class="removable">
		<# if ( true === data.state.isAdding ) { #>
		<button class="button-link delete">
			<span class="dashicons dashicons-no"></span>
		</button>
		<# } #>

		<div class="edd-order-overview-summary__items-name">
			<a
				href="<?php echo esc_url( $view_url ); ?>&post={{ data.productId }}"
				class="row-title"
			>
				{{{ data.productName }}}
			</a>
			<# if ( 'refunded' === data.status ) { #>
				&mdash; <?php esc_html_e( 'Refunded', 'easy-digital-downloads' ); ?>
			<# } #>

			<div class="row-actions">
				<# if ( data.discount > 0 ) { #>
				<span class="text"><strong><?php esc_html_e( 'Discount:', 'easy-digital-downloads' ); ?></strong> {{ data.discountCurrency }}</span>
				<# } #>

				<# if ( false !== data.state.hasTax ) { #>
				<span class="text">
					<strong><?php esc_html_e( 'Tax:', 'easy-digital-downloads' ); ?></strong>
					{{ data.taxCurrency }}
					<# if ( true === data.config.isAdjustingManually ) { #>&dagger;<# } #>
				</span>
				<# } #>

				<# if ( false === data.state.isAdding && 'complete' === data.status ) { #>
				<span>
					<button class="button-link copy-download-link">
						<?php echo esc_html( sprintf( __( 'Copy %s Links', 'easy-digital-downloads' ), edd_get_label_singular() ) ); ?>
					</button>
				</span>
				<# } #>
			</div>
		</div>

		<button type="button" class="toggle-row">
			<span class="screen-reader-text">Show more details</span>
		</button>
	</div>
</td>

<td data-colname="<?php esc_html_e( 'Unit Price', 'easy-digital-downloads' ); ?>">
	{{ data.amountCurrency }}
	<# if ( true === data.config.isAdjustingManually ) { #>&dagger;<# } #>
</td>

<# if ( true === data.state.hasQuantity ) { #>
<td data-colname="<?php esc_html_e( 'Quantity', 'easy-digital-downloads' ); ?>">
	{{ data.quantity }}
</td>
<# } #>

<td class="column-right" data-colname="<?php esc_html_e( 'Amount', 'easy-digital-downloads' ); ?>">
	{{ data.subtotalCurrency }}
	<# if ( true === data.config.isAdjustingManually ) { #>&dagger;<# } #>
</td>

<input type="hidden" value="{{ data.productId }}" name="downloads[{{ data.id }}][id]" />
<input type="hidden" value="{{ data.priceId }}" name="downloads[{{ data.id }}][price_id]" />
<input type="hidden" value="{{ data.quantity }}" name="downloads[{{ data.id }}][quantity]" />
<input type="hidden" value="{{ data.amount }}" name="downloads[{{ data.id }}][amount]" />
<input type="hidden" value="{{ data.discount }}" name="downloads[{{ data.id }}][discount]" />
<input type="hidden" value="{{ data.tax }}" name="downloads[{{ data.id }}][tax]" />
<input type="hidden" value="{{ data.subtotal }}" name="downloads[{{ data.id }}][subtotal]" />
<input type="hidden" value="{{ data.total }}" name="downloads[{{ data.id }}][total]" />

<# _.each ( data.adjustments, function( adjustment ) { #>
	<input type="hidden" value="{{ adjustment.objectId }}" name="downloads[{{ data.id }}][adjustments][{{ adjustment.id }}][object_id]" />
	<input type="hidden" value="{{ adjustment.objectType }}" name="downloads[{{ data.id }}][adjustments][{{ adjustment.id }}][object_type]" />
	<input type="hidden" value="{{ adjustment.type }}" name="downloads[{{ data.id }}][adjustments][{{ adjustment.id }}][type]" />
	<input type="hidden" value="{{ adjustment.description }}" name="downloads[{{ data.id }}][adjustments][{{ adjustment.id }}][description]" />
	<input type="hidden" value="{{ adjustment.subtotal }}" name="downloads[{{ data.id }}][adjustments][{{ adjustment.id }}][subtotal]" />
	<input type="hidden" value="{{ adjustment.total }}" name="downloads[{{ data.id }}][adjustments][{{ adjustment.id }}][total]" />
<# } ); #>
