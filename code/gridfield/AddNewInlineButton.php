<?php

class FrontendifyGridFieldAddNewInlineButton extends GridFieldAddNewInlineButton
	implements FrontendifyIconsInterface {
	use frontendify_requirements, frontendify_config;

	const FrontendifyType = 'GridField';

	private $editableColumns;

	public function __construct( $editableColumns, $fragment = 'toolbar-before-right' ) {
		parent::__construct( $fragment );
		$this->editableColumns = $editableColumns;
	}

	public function handlePublish( GridField $grid, DataObjectInterface $record, &$line = 0, &$results = [] ) {
		$publish = $record->hasExtension( 'Versioned' )
		           && $record->canPublish();

		return $this->process( $grid, $publish, $line, $results );
	}

	public function handleSave( GridField $grid, DataObjectInterface $record, &$line = 0, &$results = [] ) {
		return $this->process( $grid, false, $line, $results );
	}

	/**
	 * @param \FrontendifyGridField|\GridField $grid
	 * @param                                  $publish
	 * @param int                              $line
	 * @param array                            $results
	 *
	 * @throws \LogicException
	 */
	protected function process( GridField $grid, $publish, &$line = 0, &$results = [] ) {
		$modelClass = $grid->getModelClass();
		$model      = singleton( $modelClass );

		if ( ! $model->canCreate() ) {
			return;
		}

		$value = $grid->Value();

		$dataKey = static::class;

		if ( ! isset( $value[ $dataKey ] ) || ! is_array( $value[ $dataKey ] ) ) {
			return;
		}
		$rows = $value[ $dataKey ];

		$list = $grid->getList();

		/** @var GridFieldEditableColumns $editable */
		$editable = $grid->getConfig()->getComponentByType( 'GridFieldEditableColumns' );
		/** @var GridFieldOrderableRows $sortable */
		$sortable = $grid->getConfig()->getComponentByType( 'GridFieldOrderableRows' );

		foreach ( $rows as $row ) {
			$line ++;

			$item  = $modelClass::create();
			$extra = [];

			$form = $editable->getForm( $grid, $item );
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

				$grid->beforeRowSave( $row, $item, $line, $results );

				$item->write();

				$grid->afterRowSave( $row, $item, $line, $results );

				if ( $publish ) {
					$grid->beforeRowPublish( $row, $item, $line, $results );

					$item->publish( 'Stage', 'Live' );

					$grid->afterRowPublish( $row, $item, $line, $results );
				}
				$list->add( $item, $extra );

				if ( isset( $results[ $line ]['message'] ) ) {
					$message = $results[ $line ]['message'];
				} else {
					$message = '';
				}

				$results[ $line ] = [
					'id'      => $item->ID,
					'index'   => $line,
					'type'    => $message ? 'warning' : 'success',
					'message' => $message ?: 'added',
					'icon'    => $message ? self::IconWarning : self::IconAdded,
				];

			} catch ( ValidationException $e ) {
				// validation leads to an error when adding
				$results[ $line ] = [
					'id'      => $item->ID,
					'index'   => $line,
					'type'    => 'error',
					'message' => join( ',', $e->getResult()->messageList() ),
					'icon'    => self::IconError,
				];

			} catch ( Exception $e ) {
				$results[ $line ] = [
					'id'      => $item->ID,
					'index'   => $line,
					'type'    => 'error',
					'message' => $e->getMessage(),
					'icon'    => self::IconError,
				];
			}
		}
	}

	/**
	 * @param GridField $grid
	 *
	 * @return array
	 * @throws \Exception
	 * @throws \UnexpectedValueException
	 */
	public function getHTMLFragments( $grid ) {
		if ( $grid->getList() && ! singleton( $grid->getModelClass() )->canCreate() ) {
			return [];
		}

		$fragment = $this->getFragment();

		if ( ! $editable = $grid->getConfig()->getComponentByType( 'GridFieldEditableColumns' ) ) {
			throw new Exception( 'Inline adding requires the editable columns component' );
		}

		Requirements::javascript( THIRDPARTY_DIR . '/javascript-templates/tmpl.js' );

		$this->requirements();

		$data = new ArrayData( [
			'Title' => $this->getTitle(),
		] );

		return [
			$fragment => $data->renderWith( __CLASS__ ),
			'after'   => $this->getRowTemplate( $grid, $editable ),
		];
	}

	/**
	 * @param \GridField                $grid
	 * @param \GridFieldEditableColumns $editable
	 *
	 * @return \HTMLText
	 * @throws \Exception
	 * @throws \InvalidArgumentException
	 * @throws \LogicException
	 * @throws \UnexpectedValueException
	 */
	protected function getRowTemplate( GridField $grid, GridFieldEditableColumns $editable ) {
		$columns = new ArrayList();
		$handled = array_keys( $editable->getDisplayFields( $grid ) );

		if ( $grid->getList() ) {
			$record = Object::create( $grid->getModelClass() );
		} else {
			$record = null;
		}

		$fields = $editable->getFields( $grid, $record );

		$grid->invokeWithExtensions( 'customiseAddNewFields', $fields );

		foreach ( $grid->getColumns() as $column ) {
			if ( in_array( $column, $handled ) ) {
				$field = $fields->fieldByName( $column );
				if ( $field ) {
					$field->setName( sprintf(
						'%s[%s][{%%=o.num%%}][%s]', $grid->getName(), __CLASS__, $field->getName()
					) );

					$content = $field->Field();

					// Convert HTML IDs built by FormTemplateHelper to the template format
					$content = str_replace(
						'GridFieldAddNewInlineButton_o.num_',
						'GridFieldAddNewInlineButton_{%=o.num%}_',
						$content
					);
				}
			} else {
				$content = $grid->getColumnContent( $record, $column );

				// Convert GridFieldEditableColumns to the template format
				$content = str_replace(
					'[GridFieldEditableColumns][0]',
					'[GridFieldAddNewInlineButton][{%=o.num%}]',
					$content
				);
			}

			$attrs = '';

			foreach ( $grid->getColumnAttributes( $record, $column ) as $attr => $val ) {
				$attrs .= sprintf( ' %s="%s"', $attr, Convert::raw2att( $val ) );
			}

			$columns->push( new ArrayData( [
				'Content'    => $content,
				'Attributes' => $attrs,
				'IsActions'  => $column == 'Actions',
			] ) );
		}

		return $columns->renderWith( 'FrontendifyGridFieldAddNewInlineRow' );
	}

}