<?php

class RowStatusField extends LiteralField {
	public function __construct( $name, $content = '<i></i>') {
		parent::__construct( $name, $content );
	}

	public function getName() {
		return $this->name;
	}
}