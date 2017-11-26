<?php

class FrontendifyGridField extends FrontEndGridField {
	use frontendify_requirements, frontendify_config;

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

	/**
	 * FrontendifyGridField constructor.
	 *
	 * @param DataObject            $model
	 * @param \SS_List|null         $dataList
	 * @param array|boolean|null    $editableColumns if false then read-only mode, null means get from model, otherwise use these
	 * @param null                  $canCreate
	 * @param \GridFieldConfig|null $config
	 */
	public function __construct( $model, \SS_List $dataList = null, $editableColumns = null, $canCreate = null, \GridFieldConfig $config = null ) {
		$config = $config ?: new FrontEndGridFieldConfig_RecordEditor( 10 );

		$modelClass = get_class( $model );

		// no title by default
		parent::__construct( $modelClass, '', $dataList, $config );

		$model = singleton( $modelClass );

		$editableColumns = is_null( $editableColumns ) ? $model->provideEditableColumns() : $editableColumns;

		$canCreate = ( is_null( $canCreate ) && $model->canCreate() ) || (bool) $canCreate;

		$canEdit = $model->canEdit();

		$canDelete = $model->canDelete();

		$config = $this->getConfig();
		if ( $editableColumns ) {
			$config->removeComponentsByType( GridFieldAddExistingSearchButton::class )
			       ->removeComponentsByType( GridFieldPaginator::class )
			       ->removeComponentsByType( GridFieldPageCount::class );

			$config->removeComponentsByType( GridFieldAddNewButton::class )
			       ->removeComponentsByType( GridFieldEditButton::class )
			       ->removeComponentsByType( GridFieldDeleteAction::class );

			if ( $canCreate || $canEdit ) {
				$config->removeComponentsByType( GridFieldDataColumns::class )
				       ->addComponent( new FrontendifyGridFieldEditableColumns( $editableColumns ) );
			}

			$config->addComponent( new FrontendifyGridFieldFilterRow() );

			// add new needs to come after editable columns so saving is kept in line order
			if ( $canCreate ) {
				$config->addComponent( new FrontendifyGridFieldAddNewInlineButton( $editableColumns, 'buttons-before-right' ) );
			}
			if ( $canDelete ) {
				$config->addComponent( new FrontendifyGridFieldDeleteAction() );
			}
			if ( $model->hasExtension( 'Versioned' ) && $model->canPublish() ) {
				$config->addComponent( new FrontendifyGridFieldPublishButton( 'buttons-before-right' ) );
			}

			if ( $canCreate || $canEdit ) {
				$config->addComponent( new FrontendifyGridFieldSaveAllButton( 'buttons-before-right' ) );
			}

		} else {
			$config
				->removeComponentsByType( GridFieldEditButton::class )
				->removeComponentsByType( GridFieldDeleteAction::class )
				->removeComponentsByType( GridFieldAddNewButton::class )
				->addComponent( new FrontendifyGridFieldFilterRow() );

		}

		$this->addExtraClass( 'frontendify-gridfield responsive' );
		$this->setTitle( '' );
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

		throw new InvalidArgumentException( sprintf(
			'Can\'t handle action "%s"',
			$actionName
		) );
	}

	/**
	 * @return \FrontendifyGridField
	 * @throws \InvalidArgumentException
	 */

	public static function view_mode() {
		$model = singleton( static::GridModelClass );

		$grid = static::create(
			$model,
			null,
			false
		);

		return $grid;
	}

	/**
	 * @return \FrontendifyGridField
	 * @throws \InvalidArgumentException
	 */

	public static function edit_mode() {
		$model = singleton( static::GridModelClass );

		$grid = static::create(
			$model,
			null,
			static::edit_columns( $model )
		);

		return $grid;
	}

	/**
	 * Add some extra columns required across all models to those provided by the model, such as
	 * 'ID' and 'Messages'.
	 *
	 * @param DataObject $model
	 *
	 * @return array
	 */
	public static function edit_columns( $model ) {
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

	public function FieldHolder( $properties = [] ) {
		$this->requirements();

		return parent::FieldHolder( $properties );
	}

	public function Link( $action = null ) {
		return $this->form ? parent::Link( $action ) : '';
	}
}
