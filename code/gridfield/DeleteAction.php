<?php

class FrontendifyGridFieldDeleteAction extends GridFieldDeleteAction {

	/**
	 *
	 * @param GridField  $gridField
	 * @param DataObject $record
	 * @param string     $columnName
	 *
	 * @return string|null - the HTML for the column
	 */
	public function getColumnContent( $gridField, $record, $columnName ) {
		if ( ! $record->canDelete() ) {
			return;
		}

		$field = (new GridField_FormAction( $gridField, 'DeleteRow' . $record->ID, false, "deleterow",
			array( 'RecordID' => $record->ID )
		))
			->addExtraClass( 'frontendify-delete-row glyphicon glyphicon-remove' )
			->setAttribute( 'title', _t( 'GridAction.Delete', "Delete" ) )
			->setAttribute( 'data-icon', 'cross-circle' )
			->setDescription( _t( 'GridAction.DELETE_DESCRIPTION', 'Delete' ) );

		return $field->Field();
	}
}