<?php

abstract class FrontendifyGridField_Controller extends Page_Controller {
	const GridModelClass = '';
	const GridFieldClass = '';

	// if set then will override use of GridModelClass to figure out template to use
	const TemplateName = '';
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

	public function init() {
		parent::init();
	}
	public function canView() {
		return Permission::check( 'CAN_VIEW_' . static::GridModelClass );
	}

	public function canEdit() {
		return Permission::check( 'CAN_EDIT_' . static::GridModelClass );
	}

	abstract protected function gridFieldData();

	/**
	 * If no action is provided then default to either 'edit' mode if can edit or 'View' mode if can view
	 * @return \HTMLText
	 * @throws \UnexpectedValueException
	 */
	public function index() {
		$template = static::TemplateName ?: static::GridModelClass;

		return $this->renderWith( [ $template, 'Page' ] );
	}

	public function field( SS_HTTPRequest $request ) {
		$fieldName = $request->param( 'Name' );

		/** @var \GridField $gridField */
		$gridField = $this->EditForm()->Fields()->dataFieldByName( $fieldName );

		return $gridField;
	}

	public function edit( SS_HTTPRequest $request ) {
		$template = static::TemplateName ?: static::GridModelClass;
		return $this->renderWith( [ $template . '_edit', $template, 'Page' ], [ 'Mode' => 'edit'] );
	}

	public function view( SS_HTTPRequest $request ) {
		$template = static::TemplateName ?: static::GridModelClass;

		return $this->renderWith( [ $template . '_view', $template, 'Page' ], [ 'Mode' => 'view' ] );
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

	/**
	 * Return a form suitable for mode and permissions, preferring 'edit' mode if user can edit, otherwise 'view' mode
	 *
	 * Mode can be specified in template e.g. 'view' which will always be honoured if can view,
	 * otherwise it can be specified in request with get or post var '_mode'. When template is rendered it also
	 * received a variable 'Mode' which can be passed back in to this call.
	 *
	 * @param string $mode
	 *
	 * @return \Form in 'edit' or 'view' mode
	 */
	public function Form($mode = '') {
		$mode = $mode ?: $this->getRequest()->requestVar( '_mode');

		if ( ( $mode == '' || $mode == 'edit' ) && $this->canEdit() ) {
			return $this->EditForm();
		} elseif ( ( $mode == '' || $mode == 'view' ) && $this->canView() ) {
			return $this->ViewForm();
		} else {
			$this->setSessionMessage( 'Sorry you aren\'t able to do that, try <a href="/Security/login?BackURL=$url">logging in</a>', 'bad');
		}
	}

	protected function EditForm() {
		/** @var \FrontendifyGridField $gridFieldClass */
		$gridFieldClass = static::GridFieldClass;

		/** @var \FrontendifyGridField $grid */
		$grid = $gridFieldClass::edit_mode();

		$this->customiseFilters( $grid);

		$grid->setList(
			$this->applyFilters( $grid, $this->gridFieldData() )
		);

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

	protected function ViewForm() {
		/** @var \FrontendifyGridField $gridFieldClass */
		$gridFieldClass = static::GridFieldClass;

		/** @var \FrontendifyGridField $grid */
		$grid           = $gridFieldClass::view_mode();

		$this->customiseFilters( $grid );

		$grid->setList(
			$this->applyFilters( $grid, $this->gridFieldData() )
		);

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

	/**
	 * Add extra filters etc in derived class, or dont' do anything to remove filters
	 *
	 * @param \GridField $grid
	 */
	protected function customiseFilters( GridField $grid ) {
		$grid->getConfig()->addComponents(
			new FrontendifyGridFieldDateFilter(),
			new FrontendifyApplyFilterButton()
		);
	}
	/**
	 * Enumerate grid components and call applyFilter on those which implement the GridFieldFilterInterface
	 *
	 * @param \GridField $grid
	 * @param DataList   $data
	 *
	 * @param array      $extraFilters e.g. [ 'EntryDate:GreaterThan' => null, 'Status' => 'Pending' ]
	 *
	 * @return mixed
	 */
	protected function applyFilters( $grid, $data, $extraFilters = [] ) {
		$request = $this->getRequest();

		$components = $grid->getComponents();
		foreach ( $components as $component ) {
			if ( $component instanceof GridFieldFilterInterface ) {
				$component->applyFilter( $request, $data, $extraFilters );
			}
		}

		return $data;

	}


}