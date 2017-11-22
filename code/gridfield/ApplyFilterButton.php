<?php

class FrontendifyApplyFilterButton
	implements GridField_HTMLProvider {

	public function getHTMLFragments($gridField) {
		$field = ( new GridField_FormAction( $gridField, 'FilterDate', 'Apply', 'filterdate', [] ) )
			->addExtraClass( 'frontendify-datefilter-apply btn ui-state-default' )
			->addExtraClass( 'frontendify-filterbutton' );

		return [
			'before' => $field->SmallFieldHolder(),
		];
	}
}