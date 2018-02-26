<?php

class FrontendifyGridFieldDateFilter implements GridField_HTMLProvider, GridFieldFilterInterface {
	use gridfield_filter {
		filterValue as fv;
	}

	const FilterFieldName = 'DateFilter';

	protected $modelFields = [];

	// can be a value or a callable
	protected $filterDefaultValue;

	public function __construct($modelFields, $defaultValue = null) {
		$this->modelFields = $modelFields;
		$this->filterDefaultValue = $defaultValue;
	}

	/**
	 * Always return the value as yyyy-mm-dd
	 *
	 * @param bool $wasSet true if a non-null value was passed in request, otherwise false
	 *
	 * @return mixed should be null if no value to filter on
	 */
	public function filterValue(&$wasSet = false) {
		if ($value = $this->fv($wasSet)) {
			$field = new FrontendifyDateField('asd');
			$field->setValue($value);
			$value = $field->dataValue();
		}
		return $value;
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
	public function getHTMLFragments($gridField) {
		$value = $this->filterValue();

		$field = (new FrontendifyDateField($this->filterName(), '', $value))
			->addExtraClass('frontendify-filter')
			->addExtraClass('frontendify-datefilter-date')
			->setAttribute('frontendify-default-value', $this->filterDefaultValue())
			->setAttribute('frontendify-display-format', 'ddd d mmmm')
			->setAttribute('frontendify-clear-value', ' ')
			->setAttribute('placeholder', 'on date')
			->setAttribute('data-value', $this->filterDefaultValue());

		return [
			self::TargetFragment => $field->SmallFieldHolder(),
		];
	}
}