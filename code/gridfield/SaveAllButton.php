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

}