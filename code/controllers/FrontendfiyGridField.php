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

		Requirements::css( 'themes/shared/css/frontendify.css' );
	}

	/**
	 * @param DataList $data
	 *
	 * @param array    $extraFilters e.g. [ 'EntryDate:GreaterThan' => null, 'Status' => 'Pending' ]
	 *
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public function filterData( $data, $extraFilters = [] ) {
		$request = $this->getRequest();

		$filters = array_merge(
			$this->config()->get( 'filters' ),
			$extraFilters
		);

		foreach ( $filters as $filterName => $filterSpec ) {
			$filterValue = $request->postVar( $filterName );

			if ( ! empty( $filterValue ) ) {
				$data = $data->filter(
					$filterSpec,
					$request->postVar( $filterName )
				);
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
			return $this->renderWith([ static::GridModelClass, 'Page']);
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
			$errors = [];

			if ( $request->isPOST() ) {
				$data = $request->postVars();
				$field->handleAlterAction( 'save', [], $data, $errors );
			}
			$response = $this->getResponse();

			if ( $request->isAjax() ) {
				$response->setStatusCode( 200 );
				if ( $errors ) {
					$response->addHeader( 'X-Errors', json_encode( $errors ) );
				}

				return $field->forTemplate();
			}
		}

		return $this->edit( $request );
	}

	public function edit( SS_HTTPRequest $request ) {
		return $this->renderWith( [ static::GridModelClass . '_edit', static::GridModelClass, 'Page' ] );
	}

	public function view( SS_HTTPRequest $request ) {
		return $this->renderWith( [ static::GridModelClass . '_view', static::GridModelClass, 'Page' ] );
	}

	public function Form() {
		if ( $this->canEdit() ) {
			return $this->EditForm();
		} else {
			return $this->ViewForm();
		}
	}

	public function EditForm() {
		if ($this->canEdit()) {
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
	 * @return \FrontendifyGridField
	 * @throws \InvalidArgumentException
	 */

	public function EditGridField() {
		if ($this->canEdit()) {
			$model = singleton( static::GridModelClass );

			$grid = FrontendifyGridField::create(
				static::GridModelClass,
				$model->i18n_plural_name(),
				$this->filterData( $this->gridFieldData() ),
				$this->getEditableColumns()

			);

			return $grid;

		}
	}

	public function getEditableColumns() {
		$model   = singleton( static::GridModelClass );
		$columns = array_merge(
			[
				'ID'     => [
					'title'    => '',
					'callback' => function ( $item ) {
						$field = new HiddenField( 'ID', '' );

						return $field;
					},
				],
				'Status' => [
					'title'    => '',
					'callback' => function ( $item ) {
						$field = (new LiteralField( 'Status', '<i>&nbsp;</i>' ))->setAllowHTML( true);


						return $field;
					},
				],
			],
			$model->provideEditableColumns()
		);

		return $columns;
	}

	public function ViewForm() {
		if ($this->canView()) {
			$grid = $this->ViewGridField();

			$form = new Form(
				$this,
				'Form',
				new FieldList( [ $grid ] ),
				new FieldList()
			);
			$form->addExtraClass( 'frontendify' );

			return $form;

		}
	}

	/**
	 * @return \FrontendifyGridField
	 * @throws \InvalidArgumentException
	 */

	public function ViewGridField() {
		if ($this->canView()) {
			$model = singleton( static::GridModelClass );

			$grid = FrontendifyGridField::create(
				static::GridModelClass,
				$model->i18n_plural_name(),
				$this->filterData( $this->gridFieldData() )
			);

			return $grid;

		}
	}

}