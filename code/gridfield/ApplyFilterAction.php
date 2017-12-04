<?php

class FrontendifyApplyFilterAction
	implements GridField_HTMLProvider, GridFieldFilterInterface {

	public function getHTMLFragments($gridField) {
		$field = ( new GridField_FormAction( $gridField, 'ApplyFilter', 'Apply', 'apply', [] ) )
			->addExtraClass( 'frontendify-filter-apply btn ui-state-default' )
			->addExtraClass( 'frontendify-filterbutton' );

		return [
			self::TargetFragment => $field->SmallFieldHolder(),
		];
	}

	public function applyFilter( $request, &$data, $defaultFilters = [] ) {

	}
}