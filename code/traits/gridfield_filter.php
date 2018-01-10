<?php

trait gridfield_filter {

	public function filterName() {
		return static::FilterFieldName;
	}

	public function filterDefaultValue() {
		if ( $this->filterDefaultValue ) {
			if ( is_callable( $this->filterDefaultValue ) ) {
				$callable = $this->filterDefaultValue;

				return $callable( $this );
			} else {
				return $this->filterDefaultValue;
			}
		}
	}

	public function filterAllValue() {
		return null;
	}

	public function filterIgnoreValues() {
		return [];
	}

	/**
	 * @param bool $wasSet true if a non-null value was passed in request, otherwise false
	 *
	 * @return mixed should be null if no value to filter on
	 */
	public function filterValue(&$wasSet = false) {
		$value = Controller::curr()->getRequest()->requestVar(
			$this->filterName()
		);
		$wasSet = !is_null($value);
		$value = $wasSet ? $value : $this->filterDefaultValue();
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
	 * @throws \LogicException
	 */
	public function applyFilter( $request, $modelClass, &$data, $defaultFilters = [] ) {
		$value = $this->filterValue($wasSet);

		if ($value != $this->filterAllValue() && !in_array($value, $this->filterIgnoreValues())) {

			if ( isset( $this->modelFields[ $modelClass ] ) ) {
				$filter = $this->modelFields[ $modelClass ];
			} elseif ( isset( $defaultFilters[ $modelClass ] ) ) {
				$filter = $defaultFilters[ $modelClass ];
			}

			if ( isset( $filter ) ) {
				if ( is_callable( $filter ) ) {
					$data = $data->filterByCallback( function ( $model ) use ( $filter, $value ) {
						return $filter( $model, $value );
					} );
				} else {
					$data = $data->filter( [
						$filter => $value,
					] );
				}
			}
		}
	}

}