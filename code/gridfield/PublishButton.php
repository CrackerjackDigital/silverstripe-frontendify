<?php

class FrontendifyGridFieldPublishButton extends FrontendifyGridFieldSaveAllButton {
	protected $actionName = 'publish';

	public function __construct( $targetFragment = 'before', $publish = true, $action = 'publish' ) {
		parent::__construct($targetFragment, $publish, $action);
	}

	public function getActions( $gridField ) {
		return [ $this->actionName ] + parent::getActions( $gridField );
	}

	public function handleAction( GridField $gridField, $actionName, $arguments, $data, &$line = 0, &$results = [] ) {
		if ( in_array( $actionName, $this->getActions( $gridField ) ) ) {
			$this->publishRecords( $gridField, $arguments, $data, $line, $results );
		}

		return true;
	}

	public function publishRecords( GridField $grid, $arguments, $data, &$line = 0, &$results = [] ) {
		if ( isset( $data[ $grid->Name ] ) ) {
			$currValue = $grid->Value();
			$grid->setValue( $data[ $grid->Name ] );
			$model = singleton( $grid->List->dataClass() );

			$this->unpublishAllRecords($grid);

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

	/**
	 * Unpublish all published records for the grid model.
	 *
	 * @param \GridField $grid
	 *
	 * @throws \LogicException
	 */
	public function unpublishAllRecords(GridField $grid) {
		$model = singleton( $grid->getModelClass() );

		if ($model->hasExtension( Versioned::class)) {
			if ($model->canPublish()) {

				$liveStage = Versioned::get_live_stage();

				$oldMode = Versioned::get_reading_mode();
				Versioned::reading_stage( $liveStage );

				$published = $model::get();

				/** @var \Versioned $model */
				foreach ( $published as $model ) {
					$model->deleteFromStage( $liveStage );
				}

				Versioned::reading_stage( $oldMode );
			}

		}
	}

	public function getHTMLFragments( $gridField ) {
		$singleton = singleton( $gridField->getModelClass() );

		if ( $singleton->hasExtension( 'Versioned' ) && ! $singleton->canPublish() ) {
			return [];
		}

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
		$button->setAttribute( 'type', 'button' );

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