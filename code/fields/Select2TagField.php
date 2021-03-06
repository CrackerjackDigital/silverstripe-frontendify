<?php

/**
 * Field which implements Select2 tag functionality (http://http://ivaynberg.github.io/select2/)
 *
 * By default expects select2 to be installed via composer to component/select2/
 */
class FrontendifySelect2TagField extends FrontendifySelect2Field {
	use frontendify_field, frontendify_requirements;

	const FrontendifyType = 'Select2Field';

	private static $frontendify_tag_seperator = ',';

	protected $allTags;

	public function __construct( $name, $title = null, $source = [], $value = [], $maxLength = null, $form = null ) {
		$this->setMultiple( true );
		$this->setTagField( true );
		parent::__construct( $name, $title, $source, $value, $maxLength, $form );

		// preload requirements
		$this->requirements();

	}

	/**
	 * Save the current value of this field into a DataObject.
	 * If the field it is saving to is a has_many or many_many relationship,
	 * it is saved by setByIDList(), otherwise it creates a comma separated
	 * list for a standard DB text/varchar field.
	 *
	 * @param DataObject $record The record to save into
	 */
	public function saveInto( DataObjectInterface $record ) {
		if ( $this->multiple ) {
			$fieldName = $this->name;

			$modelClass = $record->getManyManyComponents( $fieldName )->dataClass();
			$allTags    = $modelClass::get()->filter( 'Source', $record->ClassName );

			$relation = ( $fieldName && $record && $record->hasMethod( $fieldName ) )
				? $record->$fieldName()
				: null;

			if ( $fieldName && $record && $relation &&
			     ( $relation instanceof RelationList || $relation instanceof UnsavedRelationList ) ) {

				$record->$fieldName()->removeAll();

				foreach ( $this->value as $value ) {
					if ( is_numeric( $value ) ) {
						$model = $allTags->find( 'ID', $value );
					} else {
						$model = $allTags->find( 'Title:nocase', $value );
					}
					if ( ! $model ) {
						$model = new $modelClass( [
							'Title'  => $value,
							'Source' => $record->ClassName,
						] );
						$model->write();
					}
					$record->$fieldName()->add( $model );
				}

			} elseif ( $fieldName && $record ) {
				if ( $this->value ) {
					$this->value        = str_replace( ',', '{comma}', $this->value );
					$record->$fieldName = implode( ",", $this->value );
				} else {
					$record->$fieldName = null;
				}
			}
		} else {
			parent::saveInto( $record );
		}
	}

	/**
	 * set selected options
	 *
	 * @param mixed $values
	 * @param null  $obj
	 *
	 * @return $this|\FormField
	 * @throws \InvalidArgumentException
	 */
	public function setValue( $values, $obj = null ) {
		if ( $values instanceof SS_Map ) {
			$values = $values->toArray();
		} elseif ( $values instanceof ArrayList ) {
			$values = $values->map();
		} elseif ( $values instanceof DataList ) {
			$values = $values->map()->toArray();
		}
		$values = is_array( $values ) ? $values : [];
		if ( $values ) {
			$this->setFieldData( 'tag-seperator', $this->tagSeperator() );
			$this->setFieldData( 'data', implode( $this->tagSeperator(), $values ?: [] ) );
		}
		$this->value = $values;

		return $this;
	}

	/**
	 * Set all available options.
	 *
	 * @param array|SS_Map $options
	 *
	 * @return $this
	 * @throws \InvalidArgumentException
	 */
	public function setSource( $options ) {
		if ( $options instanceof SS_Map ) {
			$options = $options->toArray();
		} elseif ( $options instanceof ArrayList ) {
			$options = $options->map();
		} elseif ( $options instanceof DataList ) {
			$options = $options->map()->toArray();
		}

		return parent::setSource( $options );
	}

	/**
	 * Returns a <select> tag containing all the appropriate <option> tags
	 */
	public function Field( $properties = [] ) {
		if ( $this->multiple ) {
			$this->name .= '[]';
		}
		$options = [];

		$allValues = $this->getSource();
		$selected  = is_array( $this->value ) ? $this->value : [ $this->value ];
		$disabled  = $this->disabledItems;
		$defaults  = $this->defaultItems;

		// Loop through and figure out which values were selected.
		foreach ( $allValues as $value => $title ) {
			$isSelected = ( isset( $selected[ $value ] ) || isset( $defaults[ $value ] ) );
			$isDisabled = $this->disabled || in_array( $value, $disabled );

			$options[] = new ArrayData( [
				'Title'    => $title,
				'Value'    => $value,
				'Selected' => $isSelected,
				'Disabled' => $isDisabled,
			] );
		}

		$properties = array_merge( $properties, [
			'Options' => new ArrayList( $options ),
		] );

		$this->frontendify();

		return FormField::Field( $properties );
	}

	protected function tagSeperator() {
		return $this->config()->get( 'frontendify_tag_seperator' );
	}

}