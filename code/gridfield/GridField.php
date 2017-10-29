<?php

class FrontendifyGridField extends FrontEndGridField {
	use frontendify_requirements, frontendify_config;

	const FrontendifyType = 'FrontEndGridField';

	private static $frontendify_requirements = [
		self::FrontendifyType => [ '/themes/default/css/frontendify.css' ]
	];

	public function __construct( $name, $title, \SS_List $dataList, $editableColumns = null, $allowNew = true, \GridFieldConfig $config = null ) {
		$config = $config ?: new FrontEndGridFieldConfig_RecordEditor( 10 );

		parent::__construct( $name, $title, $dataList, $config );

		$config = $this->getConfig();
		if ( $editableColumns ) {
			$config
				->removeComponentsByType( GridFieldAddExistingSearchButton::class )
				->removeComponentsByType( GridFieldPaginator::class )
				->removeComponentsByType( GridFieldPageCount::class )
				->addComponent( new FrontendifyGridFieldSaveAllButton( 'toolbar-header-right' ) );


			if ($allowNew) {
				$config->addComponent( new FrontendifyGridFieldAddNewInlineButton( $editableColumns, 'toolbar-header-right' ) );
			}
			$config->addComponent( new FrontendifyGridFieldEditableColumns( $editableColumns ) )
				->removeComponentsByType( GridFieldDataColumns::class )
				->removeComponentsByType( GridFieldAddNewButton::class )
				->removeComponentsByType( GridFieldEditButton::class )
				->removeComponentsByType( GridFieldDeleteAction::class )
				->addComponent( new FrontendifyGridFieldEditableColumns( $editableColumns ) )
				->addComponent( new FrontendifyGridFieldAddNewInlineButton( $editableColumns, 'buttons-before-right' ) )
				->addComponent( new FrontendifyGridFieldSaveAllButton( 'buttons-before-right' ) )
				->addComponent( new FrontendifyGridFieldDateFilter())
				->addComponent( new GridFieldDeleteAction() )
				->addComponent( new GridFieldEditButton() );
		} else {
			$config
				->removeComponentsByType( GridFieldEditButton::class )
				->removeComponentsByType( GridFieldDeleteAction::class );

			if (!$allowNew) {
				$config->removeComponentsByType( GridFieldAddNewButton::class );
			}

		}
		$this->addExtraClass( 'frontendify-gridfield');
		$this->setTitle('');
		$this->requirements();
	}
}
