<?php

trait frontendify_config {
	public static function config() {
		return \Config::inst()->forClass( get_called_class());
	}
}