<?php

class FrontendifyGridFieldDateFilter
	implements GridField_HTMLProvider, GridFieldFilterInterface {
	use gridfield_filter;

	const FilterFieldName = 'DateFilter';

	protected $modelFields = [];

	protected $filterDefaultValue;

	public function __construct( $modelFields, $defaultValue = null) {
		$this->modelFields        = $modelFields;
		$this->filterDefaultValue = $defaultValue;
	}

	public function filterName() {
		return static::FilterFieldName;
	}

	public function filterDefaultValue() {
		return $this->filterDefaultValue;
	}

	public function filterAllValue() {
		return null;
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
		$value = $this->filterValue();

		$field = ( new FrontendifyDateField( $this->filterName(), '', $value ) )
			->addExtraClass( 'frontendify-filter' )
			->addExtraClass( 'frontendify-datefilter-date' )
			->setAttribute('placeholder', 'on date');

		return [
			self::TargetFragment => $field->SmallFieldHolder(),
		];
	}
}