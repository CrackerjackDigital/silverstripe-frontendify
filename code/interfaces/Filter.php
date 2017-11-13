<?php

interface GridFieldFilterInterface {
	public function applyFilter($request, &$data);
}