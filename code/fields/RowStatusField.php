<?php

class RowStatusField extends LiteralField {
	public function __construct( $name, $content ) {
		parent::__construct( $name, $content );
	}

	public function getName() {
		return $this->name;
	}
}