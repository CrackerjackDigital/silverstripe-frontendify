<?php

class FrontendifySelect2ColourPicker extends FrontendifySelect2Field {
	const FrontendifyType = 'Select2ColourPicker';

	// map of [ hex value => colour name ], will be set as source if no source is passed in constructor
	private static $colours = [];

	public function __construct( $name, $title = null, $source = null, $value = null, $form = null, $emptyString = null ) {
		$source = $this->decodeList(
			$source ?: ( $this->config()->get( 'colours' ) ?: [] )
		);
		parent::__construct( $name, $title, $source, $value, $form, $emptyString );
	}

}