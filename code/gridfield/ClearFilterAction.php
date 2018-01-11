<?php

class FrontendifyClearFilterAction
	implements GridField_HTMLProvider, GridFieldFilterInterface {

	public function getHTMLFragments($gridField) {
		$field = ( new GridField_FormAction( $gridField, 'ClearFilter', 'Clear', 'clear', [] ) )
			->addExtraClass( 'frontendify-filter-clear btn ui-state-default' )
			->addExtraClass( 'frontendify-filterbutton' );

		return [
			self::TargetFragment => $field->SmallFieldHolder(),
		];
	}

	public function applyFilter( $request, $modelClass, &$data, $defaultFilters = [] ) {
		// does nothing
	}
}