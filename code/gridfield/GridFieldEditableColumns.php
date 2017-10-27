<?php

class FrontendifyGridFieldEditableColumns extends GridFieldEditableColumns {
	public function __construct($displayFields = []) {
		$this->displayFields = $displayFields;
	}
}