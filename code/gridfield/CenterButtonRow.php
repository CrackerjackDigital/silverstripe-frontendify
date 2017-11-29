<?php

/**
 * Adding this class to a {@link GridFieldConfig} of a {@link GridField} adds
 * a button row to that field.
 *
 * The filter row provides a space for filters on this grid.
 *
 * This row provides two new HTML fragment spaces: 'filters-header-left' and
 * 'filters-header-right'.
 *
 * @package    forms
 * @subpackage fields-gridfield
 */
class FrontendifyGridFieldCentreButtons implements GridField_HTMLProvider {

	protected $targetFragment;

	public function __construct( $targetFragment = 'before' ) {
		$this->targetFragment = $targetFragment;
	}

	public function getHTMLFragments( $gridField ) {
		$data = new ArrayData( [
			"TargetFragmentName" => $this->targetFragment,
			"CentreFragment"       => "\$DefineFragment(buttons-{$this->targetFragment}-centre)",
		] );

		return [
			$this->targetFragment => $data->renderWith( 'FrontendifyGridFieldCentreButtons' )
		];
	}
}
