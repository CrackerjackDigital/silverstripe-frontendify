<?php

class FrontendifyGridFieldEditableColumns extends GridFieldEditableColumns {

	// set in constructor to 'edit' or 'view'
	protected $mode;

	public function __construct( $fields = [] ) {
		$this->displayFields = $fields;
	}

	public function getHTMLFragments( $grid ) {
		// override requirements we don't need
		$grid->addExtraClass( 'ss-gridfield-editable' );
	}

	/**
	 * Add exception handling around each row save and add any errors to $errors
	 *
	 * @param \GridField                                  $grid
	 * @param \DataObjectInterface|\DataObject|\Versioned $record
	 * @param int                                         $line
	 * @param array                                       $results
	 */
	public function handlePublish( GridField $grid, DataObjectInterface $record, &$line = 0, &$results = [] ) {
		$publish = $record->hasExtension( 'Versioned' )
		           && $record->canPublish();

		return $this->process( $grid, $record, $publish, $line, $results );

	}

	public function handleSave( GridField $grid, DataObjectInterface $record, &$line = 0, &$results = [] ) {
		return $this->process( $grid, $record, false, $line, $results );

	}

	/**
	 * @param \FrontendifyGridField $grid
	 * @param \DataObjectInterface  $record
	 * @param                       $publish
	 * @param int                   $line
	 * @param array                 $results
	 *
	 * @throws \LogicException
	 */
	public function process( FrontendifyGridField $grid, DataObjectInterface $record, $publish, &$line = 0, &$results = [] ) {
		$modelClass = $grid->getModelClass();
		$model      = singleton( $modelClass );

		if ( ! $model->canEdit() ) {
			return;
		}

		$value = $grid->Value();

		$dataKey = static::class;

		if ( ! isset( $value[ $dataKey ] ) || ! is_array( $value[ $dataKey ] ) ) {
			return;
		}
		$rows = $value[ $dataKey ];

		$list = $grid->getList();

		/** @var GridFieldOrderableRows $sortable */
		$sortable = $grid->getConfig()->getComponentByType( 'GridFieldOrderableRows' );

		foreach ( $rows as $rowID => $row ) {
			$line ++;

			if ( ! is_numeric( $rowID ) || ! is_array( $row ) ) {
				continue;
			}
			$id = $row['ID'];

			if ( ! $id ) {
				// this is a new model, create it
				$item = new $modelClass( $row );
			} else {
				$item = $list->byID( $id );
			}

			try {

				$extra = [];
				// get form once we will load each item
				$form = $this->getForm( $grid, $item );

				$form->saveInto( $item );

				// Check if we are also sorting these records
				if ( $sortable ) {
					$sortField = $sortable->getSortField();
					$item->setField( $sortField, $row[ $sortField ] );
				}

				if ( $list instanceof ManyManyList ) {
					$extra = array_intersect_key( $form->getData(), (array) $list->getExtraFields() );
				}

				// give us a chance to do custom logic on the row
				$grid->beforeRowSave( $row, $item, $line, $results );

				$item->write();

				// give us a chance to do custom logic on the row
				$grid->afterRowSave( $row, $item, $line, $results );

				if ( $publish ) {
					// give us a chance to do custom logic on the row
					$grid->beforeRowPublish( $row, $item, $line, $results );

					$item->publish( 'Stage', 'Live' );

					// give us a chance to do custom logic on the row
					$grid->afterRowPublish( $row, $item, $line, $results );

					$results[ $line ] = [
						'id'      => $item->ID,
						'index'   => $line,
						'type'    => 'success',
						'message' => 'published',
					];
				} else {
					$results[ $line ] = [
						'id'      => $item->ID,
						'index'   => $line,
						'type'    => 'success',
						'message' => $id ? 'updated' : 'scheduled',
					];

				}
				$list->add( $item, $extra );

			} catch ( ValidationException $e ) {
				if ( $id ) {
					$results[ $line ] = [
						'id'      => $item->ID,
						'index'   => $line,
						'type'    => 'error',
						'message' => join( ',', $e->getResult()->messageList() ),
					];
				} else {

					$results[ $line ] = [
						'id'      => $item->ID,
						'index'   => $line,
						'type'    => 'warning',
						'message' => $publish ? 'not published' : 'not scheduled',
					];

				}

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
		if ( $field ) {
			$field = clone $field;
		} else {
			$field = new ReadonlyField( $col );
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

	/**
	 * Gets the form instance for a record.
	 *
	 * @param GridField           $grid
	 * @param DataObjectInterface $record
	 *
	 * @return \Form
	 * @throws \Exception
	 */
	public function getForm( GridField $grid, DataObjectInterface $record ) {
		$fields = $this->getFields( $grid, $record );

		$form = new Form( $this, null, $fields, new FieldList() );

		$form->loadDataFrom( $record );

		$form->setFormAction( Controller::join_links(
			$grid->Link(), 'editable/form', $record->ID
		) );

		return $form;
	}

	/**
	 * Gets the field list for a record.
	 *
	 * @param GridField           $grid
	 * @param DataObjectInterface $record
	 *
	 * @return \FieldList
	 * @throws \Exception
	 */
	public function getFields( GridField $grid, DataObjectInterface $record ) {
		$cols   = $this->getDisplayFields( $grid );
		$fields = new FieldList();

		$list  = $grid->getList();
		$class = $list ? $list->dataClass() : null;

		foreach ( $cols as $col => $info ) {
			$field = null;

			if ( $info instanceof Closure ) {
				$field = call_user_func( $info, $record, $col, $grid );
			} elseif ( is_array( $info ) ) {
				if ( isset( $info['callback'] ) ) {
					$field = call_user_func( $info['callback'], $record, $col, $grid );
				} elseif ( isset( $info['field'] ) ) {
					if ( $info['field'] == 'LiteralField' ) {
						$field = new $info['field']( $col, null );
					} else {
						$field = new $info['field']( $col );
					}
				}

				if ( ! $field instanceof FormField ) {
					throw new Exception( sprintf(
						'The field for column "%s" is not a valid form field',
						$col
					) );
				}
			}

			if ( ! $field && $list instanceof ManyManyList ) {
				$extra = $list->getExtraFields();

				if ( $extra && array_key_exists( $col, $extra ) ) {
					$field = Object::create_from_string( $extra[ $col ], $col )->scaffoldFormField();
				}
			}

			if ( ! $field ) {
				if ( ! $this->displayFields ) {
					// If setDisplayFields() not used, utilize $summary_fields
					// in a way similar to base class
					//
					// Allows use of 'MyBool.Nice' and 'MyHTML.NoHTML' so that
					// GridFields not using inline editing still look good or
					// revert to looking good in cases where the field isn't
					// available or is readonly
					//
					$colRelation = explode( '.', $col );
					if ( $class && $obj = singleton( $class )->dbObject( $colRelation[0] ) ) {
						$field = $obj->scaffoldFormField();
					} else {
						$field = new ReadonlyField( $colRelation[0] );
					}
				} elseif ( $class && $obj = singleton( $class )->dbObject( $col ) ) {
					$field = $obj->scaffoldFormField();
				} else {
					$field = new ReadonlyField( $col );
				}
			}

			if ( ! $field instanceof FormField ) {
				throw new Exception( sprintf(
					'Invalid form field instance for column "%s"', $col
				) );
			}

			// Add CSS class for interactive fields
			if ( ! ( $field->isReadOnly() || $field instanceof LiteralField ) ) {
				$field->addExtraClass( 'editable-column-field' );
			}

			$fields->push( $field );
		}

		return $fields;
	}

	protected function getFieldName( $name, GridField $grid, DataObjectInterface $record ) {
		return sprintf(
			'%s[%s][%s][%s]', $grid->getName(), __CLASS__, $record->ID ?: $record->RowIndex, $name
		);
	}
}