<?php

class FrontendifyGridFieldPublishButton extends FrontendifyGridFieldSaveAllButton
{
	public function getActions( $gridField ) {
		return [ 'publish' ] + parent::getActions( $gridField);
	}

	public function handleAction( GridField $gridField, $actionName, $arguments, $data, &$line = 0, &$results = [] ) {
		if ( in_array($actionName, $this->getActions( $gridField))) {
			$this->publishRecords( $gridField, $arguments, $data, $line, $results );
		}
		return true;
	}

	public function publishRecords( GridField $grid, $arguments, $data, &$line = 0, &$results = [] ) {
		if ( isset( $data[ $grid->Name ] ) ) {
			$currValue = $grid->Value();
			$grid->setValue( $data[ $grid->Name ] );
			$model = singleton( $grid->List->dataClass() );

			foreach ( $grid->getConfig()->getComponents() as $component ) {
				if ( $component instanceof GridField_SaveHandler ) {
					$component->handlePublish( $grid, $model, $line, $results );
				}
			}
			$grid->setValue( $currValue );

			if ( Controller::curr() && $response = Controller::curr()->getResponse() ) {
				if ( ! $this->completeMessage ) {
					$this->completeMessage = _t( 'GridField.DONE', 'Done.' );
				}
				$response->addHeader( 'X-Status', rawurlencode( $this->completeMessage ) );
			}
		}
	}

	public function getHTMLFragments( $gridField ) {
		$singleton = singleton( $gridField->getModelClass() );

		if ( ! $singleton->canEdit() && ! $singleton->canCreate() ) {
			return [];
		}

		if ( ! $this->buttonName ) {
			$this->buttonName = _t( 'GridField.PUBLISH', 'Publish' );
		}

		$button = GridField_FormAction::create(
			$gridField,
			$this->actionName,
			$this->buttonName,
			$this->actionName,
			null
		);
		$button->setAttribute('type', 'button');

		$button->setAttribute( 'data-icon', 'disk' )
			->addExtraClass( 'btn frontendify-publishbutton ui-state-default new new-link ui-button-text-icon-primary' );

		if ( $this->removeChangeFlagOnFormOnSave ) {
			$button->addExtraClass( 'js-mwm-gridfield--publish' );
		}

		return [
			$this->targetFragment => $button->Field(),
		];
	}

}