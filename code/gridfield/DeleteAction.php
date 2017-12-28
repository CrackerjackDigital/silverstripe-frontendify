<?php

class FrontendifyGridFieldDeleteAction extends GridFieldDeleteAction {

	/**
	 * Which GridField actions are this component handling
	 *
	 * @param GridField $gridField
	 *
	 * @return array
	 */
	public function getActions( $gridField ) {
		return parent::getActions( $gridField );
	}

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

		$field = ( new GridField_FormAction( $gridField, 'DeleteRow' . $record->ID, 'deleterecord', "deleterecord",
			[ 'RecordID' => $record->ID ]
		) )
			->addExtraClass( 'frontendify-delete-row glyphicon glyphicon-remove' )
			->setAttribute( 'title', _t( 'GridAction.Delete', "Delete" ) )
			->setAttribute( 'data-icon', 'cross-circle' )
			->setDescription( _t( 'GridAction.DELETE_DESCRIPTION', 'Delete' ) );

		return $field->Field();
	}

	/**
	 * Handle the actions and apply any changes to the GridField
	 *
	 * @param GridField $gridField
	 * @param string    $actionName
	 * @param mixed     $arguments
	 * @param array     $data - form data
	 *
	 * @return void
	 * @throws \LogicException
	 * @throws \ValidationException
	 */
	public function handleAction( GridField $gridField, $actionName, $arguments, $data ) {
		// get ID from either data or from arguments
		$recordID = isset( $data['RecordID'] )
			? $data['RecordID']
			: (isset($arguments['RecordID']) ? $arguments['RecordID'] : null);

		if ($recordID) {
			if ($actionName == 'deleterecord') {

				if ($recordID) {
					/** @var \DataObject|\Versioned $item */
					$item = $gridField->getList()->byID( $recordID );

					if ( $item ) {
						if ( ! $item->canDelete() ) {
							throw new ValidationException(
								_t( 'GridFieldAction_Delete.DeletePermissionsFailure', "No delete permissions" ), 0 );
						}
						if ( $item->hasExtension( Versioned::class ) ) {
							if ( $item->canPublish() ) {
								$item->deleteFromStage( 'Live' );
							}
						}
						$item->delete();
					}
				} else {
					throw new ValidationException("No such record anymore");
				}

			} else {
				parent::handleAction( $gridField, $actionName, $arguments, $data);
			}
		} else {
			throw new ValidationException("No record ID provided!");
		}


	}
}