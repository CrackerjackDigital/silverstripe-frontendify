<?php

abstract class FrontendfiyGridField_Controller extends Page_Controller {
	const GridModelClass    = '';
	const URLSegment        = '';

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
	private static $filters = [];

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
			$this->config()->get('filters'),
			$extraFilters
		);

		foreach ($filters as $filterSpec => $value) {
			$fieldName = current(explode(':', $filterSpec));

			if (is_null($value)) {
				$value = $request->postVar( $fieldName);

				if (is_null($value)) {
					continue;
				}
			}
			$data = $data->filter($filterSpec, $value);
		}
		return $data;
	}

	public function index() {
		if ( $this->canEdit() ) {
			return $this->edit( $this->getRequest() );
		} elseif ( $this->canView() ) {
			return $this->view( $this->getRequest() );
		} else {
			$this->setSessionMessage( "Sorry, you are not allowed to do that", "error");
			return $this->redirectBack();
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
			if ( $request->isAjax() ) {
				$this->getResponse()->setStatusCode( 200 );
				return $field->forTemplate();
			}
		}

		return $this->edit($request);
	}

	public function edit( SS_HTTPRequest $request ) {
		return $this->renderWith( [ static::GridModelClass . '_edit', static::GridModelClass, 'Page' ] );
	}

	public function view( SS_HTTPRequest $request ) {
		return $this->renderWith( [ static::GridModelClass . '_view', static::GridModelClass, 'Page' ] );
	}

	public function Form() {
		if ($this->canEdit()) {
			return $this->EditForm();
		} else {
			return $this->ViewForm();
		}
	}

	public function EditForm() {
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

	/**
	 * @return \FrontendifyGridField
	 */

	public function EditGridField() {
		$model = singleton( static::GridModelClass );

		$grid = FrontendifyGridField::create(
			static::GridModelClass,
			$model->i18n_plural_name(),
			$this->filterData( $this->gridFieldData() ),
			$model->provideEditableColumns()

		);

		return $grid;
	}

	public function ViewForm() {
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

	/**
	 * @return \FrontendifyGridField
	 */

	public function ViewGridField() {
		$model = singleton( static::GridModelClass );

		$grid = FrontendifyGridField::create(
			static::GridModelClass,
			$model->i18n_plural_name(),
			$this->filterData( $this->gridFieldData() )
		);

		return $grid;
	}

	public function canView() {
		return Permission::check( 'CAN_VIEW_' . static::GridModelClass );
	}

	public function canEdit() {
		return Permission::check( 'CAN_EDIT_' . static::GridModelClass );
	}

}