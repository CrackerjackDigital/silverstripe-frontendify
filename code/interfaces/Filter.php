<?php

interface GridFieldFilterInterface {
	const TargetFragment = 'filters-before-left';

	public function applyFilter($request, $modelClass, &$data, $defaultFilters = []);
}