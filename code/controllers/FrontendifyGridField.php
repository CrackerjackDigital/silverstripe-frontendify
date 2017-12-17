<?php

abstract class FrontendifyGridField_Controller extends Page_Controller {
	const GridModelClass = '';
	const GridFieldClass = '';

	// if set then will override use of GridModelClass to figure out template to use
	const TemplateName = '';
	const URLSegment   = '';

	private static $url_handlers = [
		'grid-edit/field/$Name!' => 'field',
		'grid-save/field/$Name!' => 'grid_save',
		'grid-view/field/$Name!' => 'grid_refresh',
		'grid-save'              => 'grid_save',
		'grid-view'              => 'grid_view',
		'grid-edit'              => 'grid_edit',
		'grid-refresh'           => 'grid_refresh',
		''                       => 'index',
	];
	private static $allowed_actions = [
		'index'        => true,
		'field'        => true,
		'grid_view'    => true,
		'grid_edit'    => true,
		'grid_save'    => true,
		'grid_refresh' => true,
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

	abstract protected function gridFieldData( GridField $grid );

	/**
	 * If no action is provided then default to either 'edit' mode if can edit or 'View' mode if can view
	 *
	 * @return \HTMLText
	 * @throws \UnexpectedValueException
	 */
	public function index() {
		$template = static::TemplateName ?: static::GridModelClass;

		return $this->renderWith( [ $template, 'Page' ], [ 'ExtraPageClass' => 'frontendify-grid-page' ] );
	}

	public function field( SS_HTTPRequest $request ) {
		$fieldName = $request->param( 'Name' );

		/** @var \GridField $gridField */
		$gridField = $this->Form()->Fields()->dataFieldByName( $fieldName );

		return $gridField;
	}

	public function grid_edit( SS_HTTPRequest $request ) {
		$template = static::TemplateName ?: static::GridModelClass;

		return $this->renderWith( [ $template . '_edit', $template, 'Page' ], [ 'ExtraPageClass' => 'frontendify-grid-page' ] );
	}

	public function grid_view( SS_HTTPRequest $request ) {
		$template = static::TemplateName ?: static::GridModelClass;

		return $this->renderWith( [ $template . '_view', $template, 'Page' ], [ 'ExtraPageClass' => 'frontendify-grid-page' ] );
	}

	public function grid_save( SS_HTTPRequest $request ) {
		/** @var \FrontEndGridField $field */
		if ( $field = $this->field( $request ) ) {
			$messages = [];

			if ( $request->isPOST() ) {
				$data = $request->postVars();
				// default to url action, can be overriden by alter action post var
				$action = $request->param( 'Action' );

				foreach ( $data as $name => $value ) {
					// this seems really messy way to figure out?
					if ( substr( $name, 0, strlen( 'action_gridFieldAlterAction' ) ) == 'action_gridFieldAlterAction' ) {
						$action = strtolower( $value );
						break;
					}
				}
				$field->handleAlterAction( $action, [], $data, $messages );
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

		return $this->grid_edit( $request );
	}

	/**
	 * Return the grid contents as a PJax response
	 *
	 * @param \SS_HTTPRequest $request
	 *
	 * @return \HTMLText|string
	 */
	public function grid_refresh( SS_HTTPRequest $request ) {
		/** @var \FrontEndGridField $field */
		if ( $field = $this->field( $request ) ) {
			$messages = [];

			$response = $this->getResponse();

			if ( $request->isAjax() ) {
				$response->setStatusCode( 200 );
				if ( $messages ) {
					$response->addHeader( 'X-Messages', json_encode( $messages ) );
				}

				return $field->forTemplate();
			}
		}

		return $this->grid_view( $request );

	}

	/**
	 * Return a form suitable for mode and permissions, preferring 'edit' mode if user can edit, otherwise 'view' mode
	 *
	 * Mode can be specified in template e.g. 'view' which will always be honoured if can view,
	 * otherwise it can be specified in request with get or post var '_mode'.
	 *
	 * @param string $mode
	 *
	 * @return \Form in 'edit' or 'view' mode
	 */
	public function Form( $mode = '' ) {
		$mode = $this->getRequest()->requestVar( '_mode' ) ?: $mode;

		if ( ( $mode == '' || $mode == 'edit' ) && $this->canEdit() ) {
			return $this->EditForm();
		} elseif ( ( $mode == '' || $mode == 'view' ) && $this->canView() ) {
			return $this->ViewForm();
		} else {
			$this->setSessionMessage( 'Sorry you aren\'t able to do that, try <a href="/Security/login?BackURL=$url">logging in</a>', 'bad' );
		}
	}

	protected function EditForm() {
		/** @var \FrontendifyGridField $gridFieldClass */
		$gridFieldClass = static::GridFieldClass;

		/** @var \FrontendifyGridField $grid */
		$grid = $gridFieldClass::edit_mode();

		$config = $grid->getConfig();

		$this->customiseButtons( $grid, FrontendifyGridField::ModeEdit );
		$this->customiseFilters( $grid, FrontendifyGridField::ModeEdit );

		if ($columns = $grid->editableColumns()) {
			$config->removeComponentsByType( GridFieldDataColumns::class )
			       ->addComponent( new FrontendifyGridFieldEditableColumns( $columns ) );
		}

		$data = $this->gridFieldData( $grid );
		if ( ! $grid->config()->get( 'does_own_filtering' ) ) {
			$data = $this->applyFilters( $grid, $data );
		}

		$grid->setList( $data );

		$form = new Form(
			$this,
			'Form',
			new FieldList( [ $grid ] ),
			new FieldList()
		);

		$form->setFormAction( '/' . static::URLSegment . '/grid-save' );
		$form->addExtraClass( 'frontendify' );

		return $form;
	}

	protected function ViewForm() {
		/** @var \FrontendifyGridField $gridFieldClass */
		$gridFieldClass = static::GridFieldClass;

		/** @var \FrontendifyGridField $grid */
		$grid = $gridFieldClass::view_mode();

		$this->customiseButtons( $grid, FrontendifyGridField::ModeView );
		$this->customiseFilters( $grid, FrontendifyGridField::ModeView );

		$config = $grid->getConfig();

		$columns = $grid->viewableColumns();
		if ($columns) {
			/** @var \GridFieldDataColumns $dataColumns */
			$dataColumns = $config->getComponentByType( GridFieldDataColumns::class );
			$dataColumns->setDisplayFields( $columns );
		}


		$grid->setList(
			$this->applyFilters( $grid, $this->gridFieldData( $grid ) )
		);

		$form = new Form(
			$this,
			'Form',
			new FieldList( [ $grid ] ),
			new FieldList()
		);
		$form->setFormAction( '/' . static::URLSegment . '/grid-view' );
		$form->addExtraClass( 'frontendify' );

		return $form;
	}

	/**
	 * Change buttons on grid for this controller.
	 *
	 * @param \GridField $grid
	 * @param int        $mode one of the FrontendifyGridField::ModeABC constants
	 */
	protected function customiseButtons( GridField $grid, $mode ) {
		//
	}

	/**
	 * Add extra filters etc in derived class, or override to NOP to not add filters
	 *
	 * @param \GridField $grid
	 * @param int        $mode one of the FrontendifyGridField::ModeABC constants
	 */
	protected function customiseFilters( GridField $grid, $mode ) {
		$grid->getConfig()->addComponents(
			new FrontendifyGridFieldDateFilter( [
				CrewSchedule::class => function ( CrewSchedule $item, $date ) {
					return $date >= $item->StartDate && $date <= $item->EndDate;
				},
			] ),
			new FrontendifyApplyFilterAction()
		);
	}

	/**
	 * Enumerate grid components and call applyFilter on those which implement the GridFieldFilterInterface
	 * passing in the data for the gridfield population data
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
				$component->applyFilter( $request, static::GridModelClass, $data, $extraFilters );
			}
		}

		return $data;

	}

}