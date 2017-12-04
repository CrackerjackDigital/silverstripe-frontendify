<?php

class FrontendifyGridFieldDateFilter
	implements GridField_HTMLProvider, GridFieldFilterInterface {
	const FilterFieldName = 'DateFilter';

	protected $modelFields = [];

	protected $defaultValue;

	protected $operation = 'GreaterThan';

	public function __construct( $modelFields, $defaultValue = null, $operation = 'LessThan:Not' ) {
		$this->modelFields = $modelFields;
		$this->defaultValue = $defaultValue;
		$this->operation   = $operation;
	}

	public function fieldName() {
		return static::FilterFieldName;
	}

	public function defaultValue() {
		return $this->defaultValue ?: date( 'Y-m-d' );
	}

	public function getValue() {
		$request = Controller::curr()->getRequest();
		if ($request->isPOST()) {
			$value = $request->requestVar(static::FilterFieldName);
		} else {
			$value = $this->defaultValue();
		}
		return $value;
	}

	/**
	 * @param \SS_HTTPRequest $request
	 * @param \DataList       $data
	 *
	 * @param array           $defaultFilters to apply if no value in request map of model class to filters
	 *                                        e.g. [ 'Member' => [ 'IsHappy' => 1 ]]
	 *
	 * @throws \InvalidArgumentException
	 */
	public function applyFilter( $request, &$data, $defaultFilters = [] ) {
		$value = $this->getValue();
		$modelClass = $data->dataClass();

		if ( ! empty( $value ) ) {
			if ( isset( $this->modelFields[ $modelClass ] ) ) {
				$data = $data->filter( [
					$this->modelFields[ $modelClass ] => $value,
				] );
			}
		} elseif ( isset( $defaultFilters[ $modelClass ] )) {
			$data = $data->filter( $defaultFilters[$modelClass] );
		}
	}

	/**
	 * Returns a map where the keys are fragment names and the values are
	 * pieces of HTML to add to these fragments.
	 *
	 * Here are 4 built-in fragments: 'header', 'footer', 'before', and
	 * 'after', but components may also specify fragments of their own.
	 *
	 * To specify a new fragment, specify a new fragment by including the
	 * text "$DefineFragment(fragmentname)" in the HTML that you return.
	 *
	 * Fragment names should only contain alphanumerics, -, and _.
	 *
	 * If you attempt to return HTML for a fragment that doesn't exist, an
	 * exception will be thrown when the {@link GridField} is rendered.
	 *
	 * @param GridField $gridField
	 *
	 * @return array
	 */
	public function getHTMLFragments( $gridField ) {
		$value = $this->getValue();

		$field = ( new FrontendifyDateField( $this->fieldName(), '', $value ) )
			->addExtraClass( 'frontendify-filter' )
			->addExtraClass( 'frontendify-datefilter-date' );

		return [
			self::TargetFragment => $field->SmallFieldHolder(),
		];
	}
}