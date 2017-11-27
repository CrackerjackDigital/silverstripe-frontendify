<?php

class FrontendifyViewSwitcher implements GridField_HTMLProvider {
	const TargetFragment = 'buttons-before-centre';

	private $targetFragment = self::TargetFragment;

	public function __construct( $targetFragment = self::TargetFragment) {
		$this->targetFragment = $targetFragment;
	}

	/**
	 * Returns a map where the keys are fragment names and the values are
	 * pieces of HTML to add to these fragments.
	 *
	 * Here are 4 built-in fragments: 'header', 'footer', 'before', and
	 * 'after', but components may also specify fragments of their own.
	 *
	 * To specify a new fragment, specify a new fragment by including the
	 * text "$DefineFragment(fragmentname)" in the HTML that you return.
	 *
	 * Fragment names should only contain alphanumerics, -, and _.
	 *
	 * If you attempt to return HTML for a fragment that doesn't exist, an
	 * exception will be thrown when the {@link GridField} is rendered.
	 *
	 * @return array
	 */
	public function getHTMLFragments( $gridField ) {
		$buttons = Config::inst()->get( get_called_class(), 'buttons' ) ?: [];
		$fragment = '';

		foreach ($buttons as $name => $info) {
			list ($title, $url, $props) = $info + [[]];

			$field = (new GridField_FormAction( $gridField, $name, $title, $name, [] ) )
				->setAttribute('data-url', $url)
				->addExtraClass( 'frontendify-view-switcher btn ui-state-default' )
				->addExtraClass( "frontendify-view-switcher-$name" );

			$fragment .= $field->SmallFieldHolder($props);
		}
		return [
			$this->targetFragment => $fragment
		];
	}
}