<?php

trait gridfield_filter {

	abstract public function filterName();

	abstract public function filterDefaultValue();

	/**
	 * @return mixed should be null if no value to filter on
	 */
	public function filterValue() {
		$request = Controller::curr()->getRequest();

		$value = $request->requestVar(
			$this->filterName()
		) ?: $this->filterDefaultValue();

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
		$value = $this->filterValue();

		if ( isset( $this->modelFields[ $modelClass ] ) ) {
			$filter = $this->modelFields[ $modelClass ];
		} elseif ( isset( $defaultFilters[ $modelClass ] ) ) {
			$filter = $defaultFilters[ $modelClass ];
		}

		if ( ! is_null( $value ) && isset( $filter ) ) {
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