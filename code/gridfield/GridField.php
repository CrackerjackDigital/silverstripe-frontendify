<?php

use Milkyway\SS\GridFieldUtils\DisplayAsTimeline;
use Milkyway\SS\GridFieldUtils\FormatSwitcher;
use Milkyway\SS\GridFieldUtils\SaveAllButton;

class FrontendifyGridField extends FrontEndGridField {
	public function __construct( $name, $title, \SS_List $dataList, $editableColumns = null, \GridFieldConfig $config = null ) {
		$config = $config ?: new FrontEndGridFieldConfig_RecordEditor( 10 );

		parent::__construct( $name, $title, $dataList, $config );

		$config = $this->getConfig();
		if ( $editableColumns ) {
			$config
				->removeComponentsByType( GridFieldAddExistingSearchButton::class )
				->removeComponentsByType( GridFieldPaginator::class )
				->removeComponentsByType( GridFieldPageCount::class )
				->removeComponentsByType( GridFieldDataColumns::class )
				->removeComponentsByType( GridFieldAddNewButton::class )
				->removeComponentsByType( GridFieldEditButton::class )
				->removeComponentsByType( GridFieldDeleteAction::class )
//				->removeComponentsByType( GridFieldSortableHeader::class )
//				->addComponent( new GridFieldTitleHeader())
				->addComponent( new FrontendifyGridFieldEditableColumns( $editableColumns ) )
				->addComponent( new FrontendifyGridFieldAddNewInlineButton( $editableColumns, 'buttons-before-right' ) )
				->addComponent( new FrontendifyGridFieldSaveAllButton( 'buttons-before-right' ) )
				->addComponent( new FrontendifyGridFieldDateFilter())
				->addComponent( new GridFieldDeleteAction() )
				->addComponent( new GridFieldEditButton() );
		} else {
			$config
				->removeComponentsByType( GridFieldEditButton::class )
				->removeComponentsByType( GridFieldDeleteAction::class )
				->removeComponentsByType( GridFieldAddNewButton::class );

		}
		$this->addExtraClass( 'frontendify-gridfield' );
		$this->setTitle( '' );

	}
}
