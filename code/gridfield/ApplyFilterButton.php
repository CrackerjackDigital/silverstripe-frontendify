<?php

class FrontendifyApplyFilterButton
	implements GridField_HTMLProvider, GridFieldFilterInterface {

	public function getHTMLFragments($gridField) {
		$field = ( new GridField_FormAction( $gridField, 'FilterDate', 'Apply', 'filterdate', [] ) )
			->addExtraClass( 'frontendify-datefilter-apply btn ui-state-default' )
			->addExtraClass( 'frontendify-filterbutton' );

		return [
			self::TargetFragment => $field->SmallFieldHolder(),
		];
	}

	public function applyFilter( $request, &$data ) {

	}
}