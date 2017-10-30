<?php

class FrontendifyGridField extends FrontEndGridField {
	use frontendify_requirements, frontendify_config;

	const FrontendifyType = 'FrontEndGridField';

	private static $frontendify_require = [
		self::FrontendifyType => [
			'/themes/default/css/frontendify.css',
		],
	];
	private static $frontendify_block = [
		'/frontendgridfield/css/FrontEndGridField.css',
	];

	public function __construct( $name, $title, \SS_List $dataList, $editableColumns = null, $allowNew = true, \GridFieldConfig $config = null ) {
		$config = $config ?: new FrontEndGridFieldConfig_RecordEditor( 10 );

		parent::__construct( $name, $title, $dataList, $config );

		$config = $this->getConfig();
		if ( $editableColumns ) {
			$config
				->removeComponentsByType( GridFieldAddExistingSearchButton::class )
				->removeComponentsByType( GridFieldPaginator::class )
				->removeComponentsByType( GridFieldPageCount::class );

			if ( $allowNew ) {
				$config->addComponent( new FrontendifyGridFieldAddNewInlineButton( $editableColumns, 'buttons-before-right' ) );
			}
			$config->addComponent( new FrontendifyGridFieldEditableColumns( $editableColumns ) )
				->removeComponentsByType( GridFieldDataColumns::class )
				->removeComponentsByType( GridFieldAddNewButton::class )
				->removeComponentsByType( GridFieldEditButton::class )
				->removeComponentsByType( GridFieldDeleteAction::class )
				->addComponent( new FrontendifyGridFieldEditableColumns( $editableColumns ) )
				->addComponent( new FrontendifyGridFieldSaveAllButton( 'buttons-before-right' ) )
				->addComponent( new FrontendifyGridFieldDateFilter() )
				->addComponent( new GridFieldDeleteAction() )
				->addComponent( new GridFieldEditButton() );
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
}
