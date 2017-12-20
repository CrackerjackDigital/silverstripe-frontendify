<?php
/**
 * Field which implements Select2 functionality (http://http://ivaynberg.github.io/select2/)
 *
 * By default expects select2 to be installed via composer to component/select2/
 */

class FrontendifyMultiSelect2Field extends ListboxField {
	use frontendify_field, frontendify_requirements;

	const FrontendifyType = 'Select2Field';

	private static $frontendify_require = [
		self::FrontendifyType => [
			"js/lib/select2/select2.min.js",
			"css/select2.min.css"
		]
	];

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
	 * @throws \InvalidArgumentException
	 */
	public function __construct( $name, $title = null, $source = [], $values = [], $form = null, $emptyString = null ) {
		$this->setMultiple( true );
		$this->setAttribute('multiple', 'multiple');
		$source = $this->decode_list( $source ?: ( $this->config()->get( 'items' ) ?: [] ) );
		parent::__construct( $name, $title, $source, $values, $form, $emptyString );
	}

	public function setSource( $source ) {
		parent::setSource( $this->decode_list( $source ) );
		return $this;
	}

	public function getTemplate() {
		return parent::getTemplate();
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

	protected function decode_list( $list ) {
		if ( $list instanceof SS_Map ) {
			$list = $list->toArray();
		} elseif ( $list instanceof ArrayList ) {
			$list = $list->map();
		} elseif ( $list instanceof DataList ) {
			$list = $list->map()->toArray();
		} elseif ( $list instanceof SS_List ) {
			$list = $list->map();
		}

		return $list;
	}

}