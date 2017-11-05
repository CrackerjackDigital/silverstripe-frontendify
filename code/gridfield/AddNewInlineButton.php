<?php

class FrontendifyGridFieldAddNewInlineButton extends GridFieldAddNewInlineButton {
	use frontendify_requirements, frontendify_config;

	const FrontendifyType = 'GridField';

	private $editableColumns;

	public function __construct( $editableColumns, $fragment = 'toolbar-before-right' ) {
		parent::__construct( $fragment );
		$this->editableColumns = $editableColumns;
	}

	public function handleSave( GridField $grid, DataObjectInterface $record, &$errors = [] ) {
		$list  = $grid->getList();
		$value = $grid->Value();

		if ( ! isset( $value[ __CLASS__ ] ) || ! is_array( $value[ __CLASS__ ] ) ) {
			return;
		}

		$class = $grid->getModelClass();
		/** @var GridFieldEditableColumns $editable */
		$editable = $grid->getConfig()->getComponentByType( 'GridFieldEditableColumns' );
		/** @var GridFieldOrderableRows $sortable */
		$sortable = $grid->getConfig()->getComponentByType( 'GridFieldOrderableRows' );

		if ( ! singleton( $class )->canCreate() ) {
			return;
		}

		foreach ( $value[ __CLASS__ ] as $fields ) {
			$item  = $class::create();
			$extra = array();

			$form = $editable->getForm( $grid, $item );
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
			} catch (Exception $e) {
				$errors[] = $e->getMessage();
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
			return array();
		}

		$fragment = $this->getFragment();

		if ( ! $editable = $grid->getConfig()->getComponentByType( 'GridFieldEditableColumns' ) ) {
			throw new Exception( 'Inline adding requires the editable columns component' );
		}

		Requirements::javascript( THIRDPARTY_DIR . '/javascript-templates/tmpl.js' );
//		GridFieldExtensions::include_requirements();
		$this->requirements();

		$data = new ArrayData( array(
			'Title' => $this->getTitle(),
		) );

		return array(
			$fragment => $data->renderWith( __CLASS__ ),
			'after'   => $this->getRowTemplate( $grid, $editable )
		);
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

		foreach ( $grid->getColumns() as $column ) {
			if ( in_array( $column, $handled ) ) {
				$field = $fields->fieldByName( $column );
				if ($field) {
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

			$columns->push( new ArrayData( array(
				'Content'    => $content,
				'Attributes' => $attrs,
				'IsActions'  => $column == 'Actions'
			) ) );
		}

		return $columns->renderWith( 'FrontendifyGridFieldAddNewInlineRow' );
	}

}