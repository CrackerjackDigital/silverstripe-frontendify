<?php

/**
 * Field which implements Select2 tag functionality (http://http://ivaynberg.github.io/select2/)
 *
 * By default expects select2 to be installed via composer to component/select2/
 */
class FrontendifySelect2TagField extends TextField {
	use frontendify_field, frontendify_requirements;

	const FrontendifyType = 'Select2Field';

    private static $frontendify_tag_seperator = ',';

	private static $frontendify_require = [
		self::FrontendifyType => [
			"js/select2/select2.js",
			"js/select2/select2.css"
		]
	];

	public function __construct( $name, $title = null, $source = null, $value = '', $maxLength = null, $form = null ) {
    	if ($source) {
    		$this->setOptions( $source);
	    }
	    if ($value) {
    		$this->setValue($value);
	    }
	    parent::__construct( $name, $title, $value, $maxLength, $form );
    }

	/**
     * implode value if it is an array.
     *
     * @param mixed $values
     * @return $this|FormField
     */
    public function setValue($values) {
	    if ( $values instanceof SS_Map ) {
		    $values = $values->toArray();
	    } elseif ( $values instanceof ArrayList ) {
		    $values = $values->map();
	    } elseif ( $values instanceof DataList ) {
		    $values = $values->map()->toArray();
	    }
	    parent::setValue(is_array($values) ? implode(self::tag_seperator(), $values) : $values);
        return $this;
    }

    /**
     * Set the available options.
     *
     * @param array|SS_Map $options
     * @return $this
     */
    public function setOptions($options) {
        if ($options instanceof SS_Map) {
            $options = $options->toArray();
        } elseif ($options instanceof ArrayList) {
        	$options = $options->map();
        } elseif ($options instanceof DataList) {
        	$options = $options->map()->toArray();
        }
        $this->setFieldData('tags', implode(static::tag_seperator(), $options ?: []));
        return $this;
    }

    public static function tag_seperator() {
        return static::config()->get('frontendify_tag_seperator');
    }

}