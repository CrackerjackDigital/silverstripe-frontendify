<?php

trait frontendify_requirements {

	abstract public function config();

	public function requirements() {
		$type = self::FrontendifyType;

		$blocks = self::config()->get('frontendify_block') ?: [];
		foreach ($blocks as $block) {
			if ( substr( $block, 0, 1 ) == '/' ) {
				$block = substr( $block, 1 );
			} else {
				$block = FRONTENDIFY_DIR . '/' . $block;
			}
			Requirements::block($block);
		}

		// get requirements for ths component added via e.g. frontendify_reqreuirments['Select2Field'] = [ 'js/Select2Field.js' ]
		$requirements = ( $all = ( self::config()->get( "frontendify_require" ) ?: [] ) )
			? ( isset( $all[ self::FrontendifyType ] ) ? $all[ self::FrontendifyType ] : [] )
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
	}
}