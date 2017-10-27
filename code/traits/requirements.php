<?php

trait frontendify_requirements {

	abstract public function config();

	public function requirements() {
		$type = self::FrontendifyType;

		// get requirements for ths component added via e.g. frontendify_reqreuirments['Select2Field'] = [ 'js/Select2Field.js' ]
		$requirements = ( $all = ( self::config()->get( "frontendify_requirements" ) ?: [] ) )
			? ( isset( $all[ self::FrontendifyType ] ) ? $all[ self::FrontendifyType ] : [] )
			: [];

		$requirements = array_merge(
			[
				'css/frontendify.css',
			],
			$requirements,
			[
				"css/$type.css",
				"js/$type.js",
			]
		);

		foreach ( $requirements as $requirement ) {
			if ( substr( $requirement, 0, 1 ) != '/' ) {
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