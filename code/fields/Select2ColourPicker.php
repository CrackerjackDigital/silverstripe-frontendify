<?php

class FrontendifySelect2ColourPicker extends FrontendifySelect2Field {
	const FrontendifyType = 'Select2ColourPicker';

	// map of [ hex value => colour name ], will be set as source if no source is passed in constructor
	private static $colours = [];

	// set to a css selector jquery can use to apply the chosen colour as a css value, e.g. in Select2ColourPicker.js as background-color.
	private static $colourpicker_container = '';

	// override the config.colourpicker_container
	protected $colourPickerContainer = '';

	private static $frontendify_require = [
		self::FrontendifyType => [
			'/frontendify/js/Select2ColourPicker.js',
		],
	];

	public function __construct( $name, $title = null, $source = null, $value = null, $form = null, $emptyString = null ) {
		$source = $this->decode_list(
			$source ?: ( $this->config()->get( 'colours' ) ?: [] )
		);
		if ($containerSelector = $this->config()->get('colourpicker_container')) {
			$this->setColourPickerContainer( $containerSelector );
		}
		parent::__construct( $name, $title, $source, $value, $form, $emptyString );
	}

	public function setColourPickerContainer($containerSelector) {
		$this->setFieldData( 'colourpicker-container', $containerSelector);
	}

}