<?php

class FrontendifyGridFieldEditableColumns extends GridFieldEditableColumns {

	// set in constructor to 'edit' or 'view'
	protected $mode;

	public function __construct( $fields = []) {
		$this->displayFields = $fields;
	}

	public function getHTMLFragments( $grid ) {
		// override requirements we don't need
		$grid->addExtraClass( 'ss-gridfield-editable' );
	}

	/**
	 * Add exception handling around each row save and add any errors to $errors
	 *
	 * @param \GridField           $grid
	 * @param \DataObjectInterface $record
	 * @param int                  $line
	 * @param array                $results
	 */
	public function handlePublish( GridField $grid, DataObjectInterface $record, &$line = 0, &$results = [] ) {
		$publish = $record->hasExtension('Versioned')
			&& $record->canPublish();

		return $this->process( $grid, $record, $publish, $line, $results );

	}

	public function handleSave( GridField $grid, DataObjectInterface $record, &$line = 0, &$results = [] ) {
		return $this->process( $grid, $record, false, $line, $results);

	}

	public function process( GridField $grid, DataObjectInterface $record, $publish, &$line = 0, &$results = [] ) {
		$modelClass = $grid->getModelClass();
		$model      = singleton( $modelClass );

		$value = $grid->Value();

		$dataKey = GridFieldEditableColumns::class;

		if ( ! isset( $value[ $dataKey ] ) || ! is_array( $value[ $dataKey ] ) ) {
			return;
		}
		$rows = $value[ $dataKey ];

		$list = $grid->getList();

		/** @var GridFieldOrderableRows $sortable */
		$sortable = $grid->getConfig()->getComponentByType( 'GridFieldOrderableRows' );

		foreach ( $rows as $id => $row ) {
			$line ++;

			if ( ! is_numeric( $id ) || ! is_array( $row ) ) {
				continue;
			}

			$item = $list->byID( $id );

			if ( ! $item || ! $item->canEdit() ) {
				continue;
			}

			$form = $this->getForm( $grid, $item );

			$extra = array();

			$form->loadDataFrom( $row, Form::MERGE_CLEAR_MISSING );
			$form->saveInto( $item );

			// Check if we are also sorting these records
			if ( $sortable ) {
				$sortField = $sortable->getSortField();
				$item->setField( $sortField, $row[ $sortField ] );
			}

			if ( $list instanceof ManyManyList ) {
				$extra = array_intersect_key( $form->getData(), (array) $list->getExtraFields() );
			}
			try {
				$item->write();
				if ($publish) {
					$item->publish('Stage', 'Live');
				}
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

	public function getColumnContent( $grid, $record, $col ) {
		static $fields;

		$fields = $fields ?: $this->getForm( $grid, $record )->Fields();

		$field = $fields->fieldByName( $col );
		if ($field) {
			$field = clone $field;
		} else {
			$field = new ReadonlyField( $col);
		}

		$value = $grid->getDataFieldValue( $record, $col );

		if ( array_key_exists( $col, $this->fieldCasting ) ) {
			$value = $grid->getCastedValue( $value, $this->fieldCasting[ $col ] );
		}

		$value = $this->formatValue( $grid, $record, $col, $value );

		$field->setName( $this->getFieldName( $field->getName(), $grid, $record ) );
		$field->setValue( $value );

		if ( $field instanceof HtmlEditorField ) {
			return $field->FieldHolder();
		}

		return $field->forTemplate();
	}
}