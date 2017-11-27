<?php

abstract class FrontendifyCalendar_Controller extends FrontendifyGridField_Controller {

	// these need to be declared in derived class
	private static $allowed_actions = [
//		"calendar",
//		"calendar_update", //ajax update on date changes
	];

	abstract public function calendar( SS_HTTPRequest $request );

	abstract public function calendar_update( SS_HTTPRequest $request );

	public function init() {
		parent::init();
		Requirements::javascript( "themes/geeves/js/moment.min.js" );
		Requirements::javascript( "themes/geeves/js/fullcalendar.min.js" );
		Requirements::javascript( "themes/geeves/js/scheduler.min.js" );
		Requirements::css( 'themes/geeves/css/fullcalendar.min.css' );
		Requirements::css( 'themes/geeves/css/scheduler.min.css' );
		Requirements::css( 'themes/geeves/css/fullcalendar.print.css', "print" );

	}

	public function getEndDate( $start_date, $duration ) {
		if ( $duration > 1.5 ) {
			$duration = $duration - 1;
		}

		return date( "Y-n-j", strtotime( $start_date . " + " . ( round( $duration, 0, PHP_ROUND_HALF_DOWN ) ) . " day" ) );
	}

	/**
	 * Return the total number of weeks of a given month.
	 *
	 * @param int $year
	 * @param int $month
	 * @param int $start_day_of_week (0=Sunday ... 6=Saturday)
	 *
	 * @return int
	 */
	public function weeks_in_month( $year, $month, $start_day_of_week = 1 ) {
		// Total number of days in the given month.
		$num_of_days = date( "t", mktime( 0, 0, 0, $month, 1, $year ) );

		// Count the number of times it hits $start_day_of_week.
		$num_of_weeks = 0;
		for ( $i = 1; $i <= $num_of_days; $i ++ ) {
			$day_of_week = date( 'w', mktime( 0, 0, 0, $month, $i, $year ) );
			if ( $day_of_week == $start_day_of_week ) {
				$num_of_weeks ++;
			}

		}

		return $num_of_weeks;
	}

	public function getStartAndEndDate( $week, $year ) {

		$time            = strtotime( "1 January $year", time() );
		$day             = date( 'w', $time );
		$time            += ( ( 7 * $week ) + 1 - $day ) * 24 * 3600;
		$return["start"] = date( 'j-n-Y', $time );

		$time          += 6 * 24 * 3600;
		$end_date      = date( 'j-n-Y', $time );
		$return["end"] = date( 'j-n-Y', strtotime( $end_date . " - 1 day" ) );

		return $return;
	}

	public function weekTitle( $start, $end ) {
		return date( "d M", strtotime( $start ) ) . "-" . date( "d M", strtotime( $end ) );
	}

//Color codes for monthly resourcing
	public function getColor( $val ) {
		if ( $val <= 33 ) {
			return "#F6F4AF";
		} elseif ( $val > 33 && $val <= 80 ) {
			return "#FDDCB6";
		} else {
			return "#FBB3B3";
		}
	}

	/*
		 * Outputs a color (#000000) based Text input
		 *
		 * @param $text String of text
		 * @param $min_brightness Integer between 0 and 100
		 * @param $spec Integer between 2-10, determines how unique each color will be
	*/

	public function genColorCodeFromText( $text, $min_brightness = 15, $spec = 10 ) {
		// Check inputs
		if ( ! is_int( $min_brightness ) ) {
			throw new Exception( "$min_brightness is not an integer" );
		}

		if ( ! is_int( $spec ) ) {
			throw new Exception( "$spec is not an integer" );
		}

		if ( $spec < 2 or $spec > 10 ) {
			throw new Exception( "$spec is out of range" );
		}

		if ( $min_brightness < 0 or $min_brightness > 255 ) {
			throw new Exception( "$min_brightness is out of range" );
		}
		$text   .= "geeves_crew";
		$hash   = md5( $text ); //Gen hash of text
		$colors = [];
		for ( $i = 0; $i < 3; $i ++ ) {
			$colors[ $i ] = max( [
				round( ( ( hexdec( substr( $hash, $spec * $i, $spec ) ) ) / hexdec( str_pad( '', $spec, 'F' ) ) ) * 255 ),
				$min_brightness,
			] );
		}
		//convert hash into 3 decimal values between 0 and 255

		if ( $min_brightness > 0 ) //only check brightness requirements if min_brightness is about 100
		{
			while ( array_sum( $colors ) / 3 < $min_brightness ) //loop until brightness is above or equal to min_brightness
			{
				for ( $i = 0; $i < 3; $i ++ ) {
					$colors[ $i ] += 10;
				}
			}
		}

		//increase each color by 10

		$output = '';

		for ( $i = 0; $i < 3; $i ++ ) {
			$output .= str_pad( dechex( $colors[ $i ] ), 2, 0, STR_PAD_LEFT );
		}

		//convert each color to hex and append to output

		return '#' . $output;
	}

}