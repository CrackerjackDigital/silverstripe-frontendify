<?php
/**
 * Field which implements Select2 functionality (http://http://ivaynberg.github.io/select2/)
 *
 * By default expects select2 to be installed via composer to component/select2/
 */

class FrontendifyMultiSelect2Field extends FrontendifySelect2Field {
	use frontendify_field, frontendify_requirements;

	// load items from this array in constructor if source is not passed
	private static $items = [];

	protected $template = 'FrontendifyMultiSelect2Field';

	/**
	 * Auto-populate from current controller's model
	 *
	 * @param string     $name
	 * @param null       $title
	 * @param array|null $source
	 * @param array      $values
	 * @param null       $form
	 * @param null       $emptyString
	 *
	 * @throws \Exception
	 * @throws \InvalidArgumentException
	 */
	public function __construct( $name, $title = null, $source = [], $values = [], $form = null, $emptyString = null ) {
		$this->setMultiple( true );
		$this->setAttribute('data-frontendify-keep-open', true);
		parent::__construct( $name, $title, $source, $values, $form, $emptyString );
	}

	public function setValue( $values, $obj = null ) {
		$values = $this->decode_list( $values );

		if ( is_array( $obj ) ) {
			$values = is_array( $values ) ? array_values( $values ) : $values;
		} elseif ( is_null( $obj ) ) {
			$values = is_array( $values ) ? array_keys( $values ) : $values;
		}

		parent::setValue( $values, $obj );

		return $this;
	}


}