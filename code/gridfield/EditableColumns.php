<?php

class FrontendifyGridFieldEditableColumns extends GridFieldEditableColumns {
	public function __construct( $displayFields = [] ) {
		$this->displayFields = $displayFields;
	}

	public function getHTMLFragments( $grid ) {
		$grid->addExtraClass( 'ss-gridfield-editable' );
	}

	public function getColumnAttributes( $gridField, $record, $columnName ) {
		return parent::getColumnAttributes( $gridField, $record, $columnName );
	}

	/**
	 * Add exception handling around each row save and add any errors to $errors
	 *
	 * @param \GridField           $grid
	 * @param \DataObjectInterface $record
	 * @param int                  $line
	 * @param array                $results
	 */
	public function handleSave( GridField $grid, DataObjectInterface $record, &$line = 0, &$results = [] ) {
		$list  = $grid->getList();
		$value = $grid->Value();

		$dataKey = GridFieldEditableColumns::class;

		if ( ! isset( $value[ $dataKey ] ) || ! is_array( $value[ $dataKey ] ) ) {
			return;
		}
		/** @var GridFieldOrderableRows $sortable */
		$sortable = $grid->getConfig()->getComponentByType( 'GridFieldOrderableRows' );

		foreach ( $value[ $dataKey ] as $id => $fields ) {
			$line ++;

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

				$results[ $line ] = [
					'id'      => $item->ID,
					'index'   => $line,
					'type'    => 'success',
					'message' => 'updated',
				];

			} catch ( ValidationException $e ) {
				$results[ $line ] = [
					'id'      => $item->ID,
					'index'   => $line,
					'type'    => 'error',
					'message' => join( ',', $e->getResult()->messageList() ),
				];

			} catch ( Exception $e ) {
				$results[ $line ] = [
					'id'      => $item->ID,
					'index'   => $line,
					'type'    => 'error',
					'message' => $e->getMessage(),
				];
			}
		}
	}

}