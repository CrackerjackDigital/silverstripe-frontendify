<?php
/**
 * Field which implements Select2 functionality (http://http://ivaynberg.github.io/select2/)
 *
 * By default expects select2 to be installed via composer to component/select2/
 */

class FrontendifySelect2Field extends DropdownField {
	use frontendify_select2field, frontendify_field, frontendify_requirements;

	const FrontendifyType = 'Select2Field';

	private static $frontendify_requirements = [
		self::FrontendifyType => [
			"js/select2/select2.js",
			"js/select2/select2.css"
		]
	];

	/**
	 * Auto-populate from current controller's model
	 *
	 * @param string $name
	 * @param null   $title
	 * @param null   $source
	 * @param array  $values
	 * @param bool   $isMultiple
	 * @param null   $emptyString
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( $name, $title = null, $source = null, $values = [], $form = null, $emptyString = null ) {
		parent::__construct( $name, $title );
		if ($source) {
			$this->setSource( $source );
		}
		if ($values) {
			$this->setValue( $values );
		}
	}

	public function setSource( $source ) {
		parent::setSource( $this->decodeList( $source ) );

		return $this;
	}

	/**
	 * implode value if it is an array.
	 *
	 * @param mixed $values
	 *
	 * @param null  $obj
	 *
	 * @return $this|\FormField
	 * @throws \InvalidArgumentException
	 */
	public function setValue( $values, $obj = null ) {
		$val = $this->decodeValues( $values );

		// If we're not passed a value directly,
		// we can look for it in a relation method on the object passed as a second arg
		if ( ! $val && $obj && $obj instanceof DataObject && $obj->hasMethod( $this->name ) ) {
			$funcName = $this->name;
			$val      = array_values( $obj->$funcName()->getIDList() );
		}

		if ( $val ) {
			if ( ! $this->multiple && is_array( $val ) ) {
				throw new InvalidArgumentException( 'Array values are not allowed (when multiple=false).' );
			}

			if ( $this->multiple ) {
				$parts = ( is_array( $val ) ) ? $val : preg_split( "/ *, */", trim( $val ) );
				if ( ArrayLib::is_associative( $parts ) ) {
					// This is due to the possibility of accidentally passing an array of values (as keys) and titles (as values) when only the keys were intended to be saved.
					throw new InvalidArgumentException( 'Associative arrays are not allowed as values (when multiple=true), only indexed arrays.' );
				}

				// Doesn't check against unknown values in order to allow for less rigid data handling.
				// They're silently ignored and overwritten the next time the field is saved.
				$this->value = $val;
			} else {
				if ( ! in_array( $val, array_keys( $this->getSource() ) ) ) {
					throw new InvalidArgumentException( sprintf(
						'Invalid value "%s" for multiple=false',
						Convert::raw2xml( $val )
					) );
				}

				$this->value = $val;
			}
		} else {
			$this->value = $val;
		}

		return $this;
	}

	protected function decodeValues( $list ) {
		$decoded = $this->decodeList( $list );

		return is_array( $decoded ) ? array_keys( $decoded ) : $decoded;
	}

}