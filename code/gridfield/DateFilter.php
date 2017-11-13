<?php

class FrontendifyGridFieldDateFilter
	implements GridField_HTMLProvider, GridFieldFilterInterface {
	const FilterFieldName = 'DateFilter';

	protected $modelFieldName = 'StartDate';

	protected $operation = 'GreaterThan';

	public function __construct( $dateFieldName = 'StartDate', $operation = 'GreaterThan' ) {
		$this->modelFieldName = $dateFieldName;
		$this->operation      = $operation;
	}

	public function filterFieldName() {
		return self::FilterFieldName . '_' . $this->modelFieldName;
	}

	public function modelFilter() {
		return $this->modelFieldName
		       . ( $this->operation
				? ":$this->operation"
				: '' );
	}

	public function defaultValue() {
		return date( 'Y-m-d' );
	}

	public function getValue() {
		$request = Controller::curr()->getRequest();
		if ($request->isPOST()) {
			$value = $request->postVar($this->filterFieldName());
		} else {
			$value = $this->defaultValue();
		}
		return $value;
	}

	/**
	 * @param \SS_HTTPRequest $request
	 * @param \DataList       $data
	 *
	 * @throws \InvalidArgumentException
	 */
	public function applyFilter( $request, &$data ) {
		$value = $this->getValue();
		if ( ! empty( $value ) ) {
			$data = $data->filter( [
				$this->modelFilter() => $value,
			] );
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

		$group = ( new FieldGroup( [
			( new FrontendifyDateField( $this->filterFieldName(), '', $value ) )->addExtraClass( 'frontendify-datefilter-date' ),
			( new GridField_FormAction( $gridField, 'FilterDate', 'Apply', 'filterdate', [] ) )->addExtraClass( 'frontendify-datefilter-apply btn ui-state-default' ),

		] ) )->addExtraClass( 'frontendify-datefilter' );

		return [
			'header' => $group->SmallFieldHolder(),
		];
	}
}