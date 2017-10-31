<?php

class FrontendifyGridField extends FrontEndGridField {
	use frontendify_requirements, frontendify_config;

	const FrontendifyType = 'GridField';

	private static $frontendify_require = [
		self::FrontendifyType => [
			'/themes/default/css/frontendify.css',
		],
	];
	private static $frontendify_block = [
		'/framework/css/GridField.css',
		'/frontendgridfield/css/GridField.css',
		'/gridfieldextensions/javascript/GridFieldExtensions.js'
	];

	public function __construct( $name, $title, \SS_List $dataList, $editableColumns = null, $allowNew = null, \GridFieldConfig $config = null ) {
		$config = $config ?: new FrontEndGridFieldConfig_RecordEditor( 10 );

		parent::__construct( $name, $title, $dataList, $config );

		$model = singleton( $this->getModelClass() );
		$editableColumns = $editableColumns ?: $model->provideEditableColumns();

		$config = $this->getConfig();
		if ( $editableColumns ) {
			$config->removeComponentsByType( GridFieldAddExistingSearchButton::class )
				->removeComponentsByType( GridFieldPaginator::class )
				->removeComponentsByType( GridFieldPageCount::class );

			$config->removeComponentsByType( GridFieldAddNewButton::class )
				->removeComponentsByType( GridFieldEditButton::class )
				->removeComponentsByType( GridFieldDeleteAction::class );

			if ( $allowNew || ( is_null( $allowNew ) && $model->canCreate() ) ) {
				$config->addComponent( new FrontendifyGridFieldAddNewInlineButton( $editableColumns, 'buttons-before-right' ) );
			}

			if ($model->canEdit()) {

				$config->removeComponentsByType( GridFieldDataColumns::class )
					->addComponent( new FrontendifyGridFieldEditableColumns( $editableColumns ) )
					->addComponent( new FrontendifyGridFieldSaveAllButton( 'buttons-before-right' ) );
			}
			if ( $model->canDelete() ) {
				$config->addComponent( new FrontendifyGridFieldDeleteAction() );
			}
			$config->addComponent( new FrontendifyGridFieldDateFilter() );

		} else {
			$config
				->removeComponentsByType( GridFieldEditButton::class )
				->removeComponentsByType( GridFieldDeleteAction::class );

			if ( ! $allowNew ) {
				$config->removeComponentsByType( GridFieldAddNewButton::class );
			}

		}
		$this->addExtraClass( 'frontendify-gridfield responsive' );
		$this->setTitle( '' );
	}

	public function FieldHolder($properties = []) {
		$this->requirements();
		return parent::FieldHolder($properties);
	}

	public function XLink($action = null) {
//		if ($this->form->Form)
	}
}
