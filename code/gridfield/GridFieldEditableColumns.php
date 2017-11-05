<?php

class FrontendifyGridFieldEditableColumns extends GridFieldEditableColumns {
	public function __construct($displayFields = []) {
		$this->displayFields = $displayFields;
	}

	/**
	 * Add exception handling around each row save and add any errors to $errors
	 *
	 * @param \GridField           $grid
	 * @param \DataObjectInterface $record
	 * @param array                $errors
	 */
	public function handleSave( GridField $grid, DataObjectInterface $record, &$errors = [] ) {
		$list  = $grid->getList();
		$value = $grid->Value();

		$dataKey = GridFieldEditableColumns::class;

		if ( ! isset( $value[ $dataKey ] ) || ! is_array( $value[ $dataKey ] ) ) {
			return;
		}
		/** @var GridFieldOrderableRows $sortable */
		$sortable = $grid->getConfig()->getComponentByType( 'GridFieldOrderableRows' );

		foreach ( $value[ $dataKey ] as $id => $fields ) {
			if ( ! is_numeric( $id ) || ! is_array( $fields ) ) {
				continue;
			}

			$item = $list->byID( $id );

			if ( ! $item || ! $item->canEdit() ) {
				continue;
			}

			$form = $this->getForm( $grid, $item );

			$extra = array();

			$form->loadDataFrom( $fields, Form::MERGE_CLEAR_MISSING );
			$form->saveInto( $item );

			// Check if we are also sorting these records
			if ( $sortable ) {
				$sortField = $sortable->getSortField();
				$item->setField( $sortField, $fields[ $sortField ] );
			}

			if ( $list instanceof ManyManyList ) {
				$extra = array_intersect_key( $form->getData(), (array) $list->getExtraFields() );
			}
			try {
				$item->write();
				$list->add( $item, $extra );
			} catch ( Exception $e ) {
				$errors[] = $e->getMessage();
			}
		}
	}

}