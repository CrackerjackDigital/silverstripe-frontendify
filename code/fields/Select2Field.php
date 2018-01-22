<?php
/**
 * Field which implements Select2 functionality (http://http://ivaynberg.github.io/select2/)
 *
 * By default expects select2 to be installed via composer to component/select2/
 */

class FrontendifySelect2Field extends ListboxField {
	use frontendify_field, frontendify_requirements;

	const FrontendifyType = 'Select2Field';

	// define this in derived classes so can put on options in template
	// otherwise you'll have to call 'setModelClass'
	const ModelClass = '';

	private static $frontendify_require = [
		self::FrontendifyType => [
			"js/lib/lodash.min.js",
			"js/lib/postal/postal.min.js",
			"js/lib/select2/select2.min.js",
			"css/select2.min.css",
			"css/frontendify.css",
			"css/Select2Field.css",
			"js/Select2Field.js"
		]
	];

	private static $items = [];

	protected $template = self::class;

	private $modelClass = '';

	/**
	 * Auto-populate from current controller's model
	 *
	 * @param string $name
	 * @param null   $title
	 * @param null   $source
	 * @param null   $value
	 * @param null   $form
	 * @param null   $emptyString
	 *
	 * @throws \Exception
	 * @throws \InvalidArgumentException
	 */
	public function __construct( $name, $title = null, $source = null, $value = null, $form = null, $emptyString = null ) {
		parent::__construct( $name, $title );
		$source = $this->decode_list( $source ?: ( $this->config()->get( 'items' ) ?: [] ) );
		if ($source) {
			$this->setSource( $source );
		}
		if ($value) {
			$this->setValue( $value );
		}
		if (static::ModelClass) {
			$this->setModelClass(static::ModelClass);
		}
		$this->requirements();
	}

	public function setModelClass($className) {
		$this->modelClass = $className;
		$this->setAttribute( 'data-model-class', $className);
		return $this;
	}

	public function ModelClass() {
		return $this->modelClass;
	}

	public function setTagField($isTagField) {
		if ($isTagField) {
			$this->addExtraClass( 'frontendify-tagfield');
		} else {
			$this->removeExtraClass( 'frontendify-tagfield');
		}
	}

	public function setMultiple($isMultiple) {
		if ($isMultiple) {
			$this->setAttribute('multiple', 'multiple');
		} else {
			$this->setAttribute( 'multiple', null);
		}
		return parent::setMultiple( $isMultiple);
	}

	public function setSource( $source ) {
		return parent::setSource( $this->decode_list( $source ) );
	}


	protected static function decode_values( $list ) {
		$decoded = static::decode_list( $list );
		return is_array( $decoded ) ? array_keys( $decoded ) : $decoded;
	}

}