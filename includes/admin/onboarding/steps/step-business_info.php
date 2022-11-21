<?php
/**
 * Onboarding Wizard Business Info Step.
 *
 * @package     EDD
 * @subpackage  Onboarding
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.2
 */

namespace EDD\Onboarding\Steps\BusinessInfo;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Onboarding\Helpers;

/**
 * Initialize step.
 *
 * @since 3.2
 */
function initialize() {}

/**
 * Get step view.
 *
 * @since 3.2
 */
function step_html() {
	$sections    = array(
		'edd_settings_general_main'     => array(
			'business_settings',
			'entity_name',
			'entity_type',
			'business_address',
			'business_address_2',
			'business_city',
			'business_postal_code',
			'base_country',
			'base_state',
		),
		'edd_settings_general_currency' => array(
			'currency_settings',
			'currency',
			'currency_position',
			'thousands_separator',
			'decimal_separator',
		),
	);
	ob_start();
	?>
	<form method="post" action="options.php" class="edd-settings-form">
		<?php settings_fields( 'edd_settings' ); ?>
		<table class="form-table" role="presentation">
			<tbody>
				<?php echo Helpers\settings_html( Helpers\extract_settings_fields( $sections ) ); ?>
			</tbody>
		</table>
	</form>
	<?php

	return ob_get_clean();
}
