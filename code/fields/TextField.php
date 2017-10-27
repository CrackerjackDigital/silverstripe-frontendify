<?php

class FrontendifyTextField extends TextField {
	use frontendify_field, frontendify_requirements;

	const FrontendifyType = 'TextField';

	private static $frontendify_requirements = [
		self::FrontendifyType => [
			'css/TextField.css'
		]
	];
}