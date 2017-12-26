<?php

trait frontendify_field {
	public function Field( $properties = [] ) {
		$this->frontendify();
		return parent::Field( $properties );
	}

	/**
	 * Add attributes and requirements (from frontendify_requirements trait).
	 * @throws \FrontendifyException
	 */
	protected function frontendify() {
		$this->requirements();
		$classes = strtolower( static::FrontendifyType );

		$this->addExtraClass( "frontendify-field frontendify-{$classes}" );

		$this->setAttribute( 'placeholder', $this->Title() );
	}

	public function setFieldData( $name, $value ) {
		$this->setAttribute( "data-frontendify-$name", $value );

		return $this;
	}

	/**
	 * Return an array from a list, or the passed value if not a 'list type'
	 *
	 * @param array|SS_List|SS_Map $list a map, array, list etc to convert to an array
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function decode_list( $list ) {
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