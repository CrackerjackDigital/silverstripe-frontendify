<?php

use Milkyway\SS\GridFieldUtils\SaveAllButton;

class FrontendifyGridFieldSaveAllButton extends SaveAllButton
{
	public function getActions( $gridField ) {
		return [ 'save' ] + parent::getActions( $gridField);
	}

	public function handleAction( GridField $gridField, $actionName, $arguments, $data ) {
		if ( in_array($actionName, $this->getActions( $gridField))) {
			$this->saveAllRecords( $gridField, $arguments, $data );
		}
		return true;
	}

	public function getHTMLFragments( $gridField ) {
		$singleton = singleton( $gridField->getModelClass() );

		if ( ! $singleton->canEdit() && ! $singleton->canCreate() ) {
			return [];
		}

		if ( ! $this->buttonName ) {
			if ( $this->publish && $singleton->hasExtension( 'Versioned' ) ) {
				$this->buttonName = _t( 'GridField.SAVE_ALL_AND_PUBLISH', 'Save all and publish' );
			} else {
				$this->buttonName = _t( 'GridField.SAVE_ALL', 'Save all' );
			}
		}

		$button = GridField_FormAction::create(
			$gridField,
			$this->actionName,
			$this->buttonName,
			$this->actionName,
			null
		);

		$button->setAttribute( 'data-icon', 'disk' )->addExtraClass( 'btn ui-state-default new new-link ui-button-text-icon-primary' );

		if ( $this->removeChangeFlagOnFormOnSave ) {
			$button->addExtraClass( 'js-mwm-gridfield--saveall' );
		}

		return [
			$this->targetFragment => $button->Field(),
		];
	}

}