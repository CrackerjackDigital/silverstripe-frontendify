<?php

abstract class FrontendfiyGridField_Controller extends Page_Controller {
	const ModelClass        = '';
	const URLSegment        = '';
	const SecurityGroupCode = '';

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

	abstract public function gridFieldData();

	public function init() {
		parent::init();

		Requirements::css( 'themes/shared/css/frontendify.css' );
	}

	/**
	 * @param DataList $data
	 *
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public function filterData( $data ) {
		$request = $this->getRequest();

		$filterDate = $request->postVar( FrontendifyGridFieldDateFilter::DateFieldName )
			?: ($request->isGet() ? date( 'Y-m-d', strtotime( 'monday this week' ) ) : '');

		if ($filterDate) {
			$data = $data->filter( [
				'StartDate:GreaterThan' => $filterDate
			] );

		}

		return $data;
	}

	public function index() {
		if ( $this->canEdit() ) {
			return $this->edit( $this->getRequest() );
		} elseif ( $this->canView() ) {
			return $this->view( $this->getRequest() );
		} else {
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
		return $this->renderWith( [ static::ModelClass . '_edit', static::ModelClass, 'Page' ] );
	}

	public function view( SS_HTTPRequest $request ) {
		return $this->renderWith( [ static::ModelClass . '_view', static::ModelClass, 'Page' ] );
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
		$model = singleton( static::ModelClass );

		$grid = FrontendifyGridField::create(
			static::ModelClass,
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
		$model = singleton( static::ModelClass );

		$grid = FrontendifyGridField::create(
			static::ModelClass,
			$model->i18n_plural_name(),
			$this->filterData( $this->gridFieldData() )
		);

		return $grid;
	}

	public function canView() {
		return Permission::check( 'CAN_VIEW_' . static::ModelClass );
	}

	public function canEdit() {
		return Permission::check( 'CAN_EDIT_' . static::ModelClass );
	}

}