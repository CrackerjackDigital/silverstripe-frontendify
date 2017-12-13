<?php

class FrontendifyGridField extends FrontEndGridField {
	use frontendify_requirements, frontendify_config;

	const ModeRead   = 0;
	const ModeUpdate = 1;
	const ModeCreate = 2;
	const ModeDelete = 4;

	// convenience
	const ModeView = self::ModeRead;
	const ModeEdit = self::ModeUpdate | self::ModeCreate | self::ModeDelete;

	const GridModelClass  = '';
	const FrontendifyType = 'GridField';

	private static $frontendify_block = [
		'/framework/css/GridField.css',
		'/framework/javascript/GridField.js',
		'/frontendgridfield/css/FrontEndGridField.css',
		'/frontendgridfield/javascript/FrontEndGridField.js',
		'/gridfieldextensions/javascript/GridFieldExtensions.js',
	];

	private static $frontendify_require = [
		self::FrontendifyType => [
			'js/lib/lodash.min.js',
			'/themes/default/css/frontendify.css',
		],
	];

	private static $does_own_filtering = false;

	// set in constructor to current mode, one of the self::ModeXYZ constants
	protected $mode;

	/**
	 * FrontendifyGridField constructor.
	 *
	 * @param DataObject            $model
	 * @param \SS_List|null         $dataList
	 * @param array|boolean|null    $columns if false then read-only mode, null means get from model, otherwise use these
	 * @param int|null              $mode
	 * @param \GridFieldConfig|null $config
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( $model, \SS_List $dataList = null, $columns = false, $mode = self::ModeRead, \GridFieldConfig $config = null ) {
		$this->setModelClass( static::GridModelClass );
		$this->mode = $mode;

		$config = $config ?: new FrontEndGridFieldConfig_RecordEditor( 25 );

		$modelClass = get_class( $model );

		// no title by default
		parent::__construct( $modelClass, '', $dataList, $config );

		$model = singleton( $modelClass );

		$columns = $columns ?: ($this->mode ? $this->editableColumns() : $this->viewableColumns());

		$canCreate = $model->canCreate();

		$canEdit = $model->canEdit();

		$canDelete = $model->canDelete();

		$config = $this->getConfig();

		$config
			->removeComponentsByType( GridFieldPageCount::class )
			->removeComponentsByType( GridFieldPaginator::class );

		if ( $mode ) {
			$config->removeComponentsByType( GridFieldAddExistingSearchButton::class )
			       ->removeComponentsByType( GridFieldAddNewButton::class )
			       ->removeComponentsByType( GridFieldEditButton::class )
			       ->removeComponentsByType( GridFieldDeleteAction::class )
			       ->removeComponentsByType( GridFieldButtonRow::class );

			$config->addComponent( new FrontendifyGridFieldFilterRow() );
//			$config->addComponent( new FrontendifyGridFieldCentreButtons() );
			$config->addComponent( new GridFieldButtonRow() );

			if ( ( $mode & self::ModeUpdate ) && $canEdit ) {
				$config->removeComponentsByType( GridFieldDataColumns::class )
				       ->addComponent( new FrontendifyGridFieldEditableColumns( $columns ) );
			}

			// add new needs to come after editable columns so saving is kept in line order
			if ( ( $mode & self::ModeCreate ) && $canCreate ) {
				$config->addComponent( new FrontendifyGridFieldAddNewInlineButton( $columns, 'buttons-before-right' ) );
			}
			if ( ( $mode & self::ModeDelete ) && $canDelete ) {
				$config->addComponent( new FrontendifyGridFieldDeleteAction() );
			}
			if ( $model->hasExtension( 'Versioned' ) && $model->canPublish() ) {
				$config->addComponent( new FrontendifyGridFieldPublishButton( 'buttons-before-right' ) );
			}

			if ( $mode ) {
				$config->addComponent( new FrontendifyGridFieldSaveAllButton( 'buttons-before-right' ) );
			}

		} else {
			$config
				->removeComponentsByType( GridFieldEditButton::class )
				->removeComponentsByType( GridFieldDeleteAction::class )
				->removeComponentsByType( GridFieldAddNewButton::class )
				->removeComponentsByType( GridFieldSaveRowButton::class )
				->addComponent( new FrontendifyGridFieldFilterRow() )
				->addComponent( new FrontendifyGridFieldCentreButtons() );

			if ( $columns ) {
				/** @var \GridFieldDataColumns $dataColumns */
				$dataColumns = $config->getComponentByType( GridFieldDataColumns::class );
				$dataColumns->setDisplayFields( $columns );
			}
		}

		$this->addExtraClass( 'frontendify-gridfield responsive' );
		$this->setAttribute('data-mode-name', $this->mode ? 'edit' : 'view');
		$this->setTitle( '' );
	}

	public function beforeRowSave( $row, $model, $lineNumber, &$results ) {

	}

	public function afterRowSave( $row, $model, $lineNumber, &$results ) {

	}

	public function beforeRowPublish( $row, $model, $lineNumber, &$results ) {

	}

	public function afterRowPublish( $row, $model, $lineNumber, &$results ) {

	}

	public function beforeRowRender() {

	}

	public function applyFilters( $request, &$data, $defaultFilters = [] ) {
		$components = $this->getComponents();
		foreach ( $components as $component ) {
			if ( $component instanceof GridFieldFilterInterface ) {
				$component->applyFilter( $request, $data, $defaultFilters );
			}
		}
	}

	/**
	 * Pass an action on the first GridField_ActionProvider that matches the $actionName.
	 *
	 * @param string $actionName
	 * @param mixed  $arguments
	 * @param array  $data
	 *
	 * @param array  $results
	 *
	 * @return mixed
	 * @throws \InvalidArgumentException
	 *
	 */
	public function handleAlterAction( $actionName, $arguments, $data, &$results = [] ) {
		$actionName = strtolower( $actionName );
		$line       = 0;

		foreach ( $this->getComponents() as $component ) {
			if ( $component instanceof GridField_ActionProvider ) {
				$actions = array_map( 'strtolower', (array) $component->getActions( $this ) );

				if ( in_array( $actionName, $actions ) ) {
					return $component->handleAction( $this, $actionName, $arguments, $data, $line, $results );
				}
			}
		}

		// don't know what to do with that, fail gracefully.
		return false;
	}

	/**
	 * @param bool $columns override the default 'viewable' columns
	 *
	 * @return \FrontendifyGridField
	 */

	public static function view_mode( $columns = false ) {
		$model = singleton( static::GridModelClass );

		$grid = new static(
			$model,
			null,
			$columns,
			self::ModeRead
		);

		return $grid;
	}

	/**
	 * @param null $columns override the default 'editable' columns
	 *
	 * @return \FrontendifyGridField
	 */

	public static function edit_mode( $columns = null ) {
		$model = singleton( static::GridModelClass );

		$grid = new static(
			$model,
			null,
			$columns,
			self::ModeEdit
		);

		return $grid;
	}

	public function getMode() {
		return $this->mode;
	}

	/**
	 * Add some extra columns required across all models to those provided by the model, such as
	 * 'ID' and 'Messages'.
	 *
	 * @param DataObject $model
	 *
	 * @return array
	 */
	public function editableColumns() {
		return [
			'ID'       => [
				'title'    => '',
				'callback' => function ( $item ) {
					$field = new HiddenField( 'ID', '', $item->ID ?: uniqid( static::GridModelClass ) );

					return $field->setAttribute( 'data-id', $item->ID );
				},
			],
			'Icon' => [
				'title' => '',
				'callback' => function ( $item ) {
					$field = ( new LiteralField( 'Icon', '<i></i>' ) )->setAllowHTML( true );

					return $field;
				},
			],
			'Messages' => [
				'title'    => 'Status',
				'callback' => function ( $item ) {
					$field = ( new LiteralField( 'Messages', '' ) )->setAllowHTML( true );

					return $field;
				},
			],
		];
	}

	public function viewableColumns() {
		$model = singleton($this->getModelClass());

		return array_merge([
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
		], $model->summaryFields());
	}

	public function FieldHolder( $properties = [] ) {
		$this->requirements();

		return parent::FieldHolder( $properties );
	}

	public function Link( $action = null ) {
		return $this->form ? parent::Link( $action ) : '';
	}
}
