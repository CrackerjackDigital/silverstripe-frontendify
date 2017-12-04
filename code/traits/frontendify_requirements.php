<?php

trait frontendify_requirements {

	abstract public function config();

	/**
	 * Return an array of closures which can be called to generate the actual javascript.
	 * The closure will be passed a unique ID which can be used on the page and in the script.
	 *
	 * e.g. [ function($id) { return <<<SCRIPT ...
	 * >>> ];
	 *
	 * @return array
	 */
	protected function custom_javascripts() {
		return [];
	}

	public function requirements() {
		$type = static::FrontendifyType;

		$blocks = static::config()->get('frontendify_block') ?: [];
		foreach ($blocks as $block) {
			if ( substr( $block, 0, 1 ) == '/' ) {
				$block = substr( $block, 1 );
			} else {
				$block = FRONTENDIFY_DIR . '/' . $block;
			}
			Requirements::block($block);
		}

		// get requirements for ths component added via e.g. frontendify_reqreuirments['Select2Field'] = [ 'js/Select2Field.js' ]
		$requirements = ( $all = ( static::config()->get( "frontendify_require" ) ?: [] ) )
			? ( isset( $all[ static::FrontendifyType ] ) ? $all[ static::FrontendifyType ] : [] )
			: [];

		$requirements = array_merge(
			[
				'css/frontendify.css',
			],
			$requirements ?: [],
			[
				"css/$type.css",
				"js/$type.js",
			]
		);

		foreach ( $requirements as $requirement ) {
			if ( substr( $requirement, 0, 1 ) == '/' ) {
				$requirement = substr( $requirement, 1 );
			} else {
				$requirement = FRONTENDIFY_DIR . '/' . $requirement;
			}
			switch ( substr( $requirement, - 3, 3 ) ) {
				case '.js':
					Requirements::javascript( $requirement );
					break;
				case 'css':
					Requirements::css( $requirement );
					break;
				default:
					throw new FrontendifyException( "Can't handle '$requirement'" );
			}
		}
		$id = uniqid();

		foreach ($this->custom_javascripts() as $function) {
			Requirements::customScript( $function($id));
		}
	}
}