<?php

interface FrontendifyIconsInterface {
	// action taken successfully should be a 'sign' (round)
	const IconAdded     = 'plus';
	const IconUpdated   = 'ok';
	const IconPublished = 'eye-open';
	const IconUnchanged = 'retweet';
	const IconWarning   = 'warning-sign';
	const IconError     = 'fire';

	// colour
	const TypeSuccess = 'success';
	const TypeWarning = 'warning';
	const TypeError   = 'error';

}