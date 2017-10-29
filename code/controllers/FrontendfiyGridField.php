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

		Requirements::css('themes/shared/css/frontendify.css');

		$this->activeLink = 'scheduling';
		$this->setField( 'Title', 'Scheduling' );

		$this->addToCrumb( 'Crew Schedules', CrewSchedule_Controller::URLSegment );
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
			if ( $request->isPOST() ) {
				$data = $request->postVars();
				$field->handleAlterAction( 'save', [], $data );
			}
			if ( $request->isAjax() ) {
				$this->getResponse()->setStatusCode( 200 );

				return $field->forTemplate();
			}
		}

		return $this->renderWith( [
			'CrewSchedule_edit', 'Page'
		]);
	}

	public function edit( SS_HTTPRequest $request ) {
		return $this->renderWith( [ static::ModelClass . '_edit', 'FrontendifyGridField_edit', 'Page' ] );
	}

	public function view( SS_HTTPRequest $request ) {
		return $this->renderWith( [ static::ModelClass . '_view', 'FrontendifyGridField_view', 'Page' ] );
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
		$form->addExtraClass( 'frontendify');
		return $form;
	}

	public function EditGridField() {
		$model = singleton( static::ModelClass );

		$grid = FrontendifyGridField::create(
			static::ModelClass,
			$model->i18n_plural_name(),
			$this->gridFieldData(),
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

	public function ViewGridField() {
		$model = singleton( static::ModelClass );

		$grid = FrontendifyGridField::create(
			static::ModelClass,
			$model->i18n_plural_name(),
			$this->gridFieldData()
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