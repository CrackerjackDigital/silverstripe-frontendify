<?php

abstract class FrontendfiyGridField_Controller extends Page_Controller {
	const GridModelClass = '';
	const URLSegment     = '';

	private static $allowed_actions = [
		'index' => true,
		'field' => true,
		'view'  => true,
		'edit'  => true,
		'save'  => true,
	];

	private static $url_handlers = [
		'edit/field/$Name!' => 'field',
		'save/field/$Name!' => 'save',
		'save'              => 'save',
		'edit'              => 'edit',
		'view'              => 'view',
		''                  => 'index',
	];
	/**
	 * Default filters, if value is null then postVar for the field name will be used
	 *
	 * @var array e.g. [ 'EntryDate:GreaterThan' => null, 'Status' => 'Pending' ]
	 */
	private static $filters = [
		# e.g. 'DateFilter' => 'StartDate:GreaterThan'
	];

	abstract public function gridFieldData();

	public function init() {
		parent::init();
	}

	/**
	 * Enumerate grid components and call applyFilter on those which implement the GridFieldFilterInterface
	 * @param \GridField $grid
	 * @param DataList   $data
	 *
	 * @param array      $extraFilters e.g. [ 'EntryDate:GreaterThan' => null, 'Status' => 'Pending' ]
	 *
	 * @return mixed
	 */
	public function applyFilters( $grid, $data, $extraFilters = [] ) {
		$request = $this->getRequest();

		$components = $grid->getComponents();
		foreach ( $components as $component ) {
			if ( $component instanceof GridFieldFilterInterface ) {
				$component->applyFilter( $request, $data );
			}
		}
		return $data;

	}

	public function canView() {
		return Permission::check( 'CAN_VIEW_' . static::GridModelClass );
	}

	public function canEdit() {
		return Permission::check( 'CAN_EDIT_' . static::GridModelClass );
	}

	public function index() {
		if ( $this->canEdit() ) {
			return $this->edit( $this->getRequest() );
		} elseif ( $this->canView() ) {
			return $this->view( $this->getRequest() );
		} else {
			$this->setSessionMessage( "Sorry, you are not allowed to do that", "error" );

			return $this->renderWith( [ static::GridModelClass, 'Page' ] );
		}
	}

	public function field( SS_HTTPRequest $request ) {
		$fieldName = $request->param( 'Name' );

		/** @var \GridField $gridField */
		$gridField = $this->EditForm()->Fields()->dataFieldByName( $fieldName );

		return $gridField;
	}

	public function save( SS_HTTPRequest $request ) {
		/** @var \FrontEndGridField $field */
		if ( $field = $this->field( $request ) ) {
			$messages = [];

			if ( $request->isPOST() ) {
				$data = $request->postVars();
				$field->handleAlterAction( 'save', [], $data, $messages );
			}
			$response = $this->getResponse();

			if ( $request->isAjax() ) {
				$response->setStatusCode( 200 );
				if ( $messages ) {
					$response->addHeader( 'X-Messages', json_encode( $messages ) );
				}

				return $field->forTemplate();
			}
		}

		return $this->edit( $request );
	}

	public function edit( SS_HTTPRequest $request ) {
		return $this->renderWith( [ static::GridModelClass . '_edit', static::GridModelClass, 'Page' ], [ 'Mode' => 'edit'] );
	}

	public function view( SS_HTTPRequest $request ) {
		return $this->renderWith( [ static::GridModelClass . '_view', static::GridModelClass, 'Page' ], [ 'Mode' => 'view' ] );
	}

	public function Form() {
		if ( $this->canEdit() ) {
			return $this->EditForm();
		} elseif ( $this->canView() ) {
			return $this->ViewForm();
		}
	}

	public function GridField() {
		if ( $this->canEdit() ) {
			return $this->EditGridField();
		} elseif ( $this->canView() ) {
			return $this->ViewGridField();
		}
	}

	public function EditForm() {
		if ( $this->canEdit() ) {
			$grid = $this->EditGridField();

			$form = new Form(
				$this,
				'Form',
				new FieldList( [ $grid ] ),
				new FieldList()
			);

			$form->setFormAction( '/' . static::URLSegment . '/save' );
			$form->addExtraClass( 'frontendify' );

			return $form;

		}
	}

	/**
	 * Add extra filters etc in derived class, or dont' do anything to remove filters
	 * @param \GridField $grid
	 */
	protected function customiseFilters(GridField $grid) {
		$grid->getConfig()->addComponents(
			new FrontendifyGridFieldDateFilter(),
			new FrontendifyApplyFilterButton()
		);
	}

	/**
	 * @return \FrontendifyGridField
	 * @throws \InvalidArgumentException
	 */

	public function EditGridField() {
		if ( $this->canEdit() ) {
			$model = singleton( static::GridModelClass );

			$grid = FrontendifyGridField::create(
				static::GridModelClass,
				$model->i18n_plural_name(),
				null,
				$this->getEditColumns()
			);
			$this->customiseFilters( $grid );

			$grid->setList(
				$this->applyFilters( $grid, $this->gridFieldData() )
			);

			return $grid;
		}
	}

	public function getEditColumns() {
		$model   = singleton( static::GridModelClass );
		$columns = array_merge(
			[
				'ID'       => [
					'title'    => '',
					'callback' => function ( $item ) {
						$field = new HiddenField( 'ID', '' );

						return $field->setAttribute( 'data-id', $item->ID );
					},
				],
				'Messages' => [
					'title'    => '',
					'callback' => function ( $item ) {
						$field = ( new LiteralField( 'Messages', '<i>&nbsp;</i>' ) )->setAllowHTML( true );

						return $field;
					},
				],
			],
			$model->provideEditableColumns()
		);

		return $columns;
	}

	public function ViewForm() {
		if ( $this->canView() ) {
			$grid = $this->ViewGridField();

			$form = new Form(
				$this,
				'Form',
				new FieldList( [ $grid ] ),
				new FieldList()
			);
			$form->setFormAction( '/' . static::URLSegment . '/view' );
			$form->addExtraClass( 'frontendify' );

			return $form;

		}
	}

	/**
	 * @return \FrontendifyGridField
	 * @throws \InvalidArgumentException
	 */

	public function ViewGridField() {
		if ( $this->canView() ) {
			$model = singleton( static::GridModelClass );

			$grid = FrontendifyGridField::create(
				static::GridModelClass,
				$model->i18n_plural_name(),
				null,
				false
			);
			$this->customiseFilters( $grid );

			$grid->setList(
				$this->applyFilters( $grid, $this->gridFieldData() )
			);

			return $grid;

		}
	}


}