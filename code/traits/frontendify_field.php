<?php

trait frontendify_field {
	public function Field( $properties = [] ) {
		$this->requirements();
		$classes = strtolower( self::FrontendifyType);

		$this->addExtraClass( "frontendify-field frontendify-{$classes}");

		$this->setAttribute( 'placeholder', $this->Title() );

		return parent::Field( $properties );
	}

	public function setFieldData( $name, $value ) {
		$this->setAttribute( "data-frontendify-$name", $value );

		return $this;
	}

	protected function decodeList( $list ) {
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