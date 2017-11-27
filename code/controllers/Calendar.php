<?php

abstract class FrontendifyCalendar_Controller extends FrontendifyGridField_Controller {

	private static $allowed_actions = [
		"calendar",
		"calendar_update", //ajax update on date changes
		"get_schedule", //json
		"get_resourcing",
		"post"               => 'isUserAdmin',
		"monthly_resourcing" => 'isUserAdmin',
		"send_conflict_msg"  => 'isUserAdmin',
	];

	public function init() {
		parent::init();
		Requirements::javascript( "themes/geeves/js/moment.min.js" );
		Requirements::javascript( "themes/geeves/js/fullcalendar.min.js" );
		Requirements::javascript( "themes/geeves/js/scheduler.min.js" );
		Requirements::css( 'themes/geeves/css/fullcalendar.min.css' );
		Requirements::css( 'themes/geeves/css/scheduler.min.css' );
		Requirements::css( 'themes/geeves/css/fullcalendar.print.css', "print" );

	}

	//Get all required data
	public function get_schedule() {
		$this->response->addHeader( 'Content-Type', 'application/json' );

		$currentMonth     = date( "m" );
		$currentYear      = date( "Y" );
		$FirstWeekOfMonth = ltrim( date( "W", strtotime( $currentYear . "-" . $currentMonth . "-01 - 1 day" ) ), '0' );
		$currentWeek      = date( "W" );
		$totalWeeks       = $this->weeks_in_month( $currentYear, $currentMonth );

		$data = [];

		for ( $i = 0; $i < $totalWeeks; $i ++ ) {
			$start_end_date                     = $this->getStartAndEndDate( $FirstWeekOfMonth, $currentYear );
			$data["weeks"][ $FirstWeekOfMonth ] = [
				"startDate" => $start_end_date['start'],
				"endDate"   => $start_end_date['end'],
				"title"     => $this->weekTitle( $start_end_date['start'], $start_end_date['end'] ),
			];

			for ( $day = 1; $day <= 6; $day ++ ) {
				$actual_week                                        = $FirstWeekOfMonth + 1;
				$day_timestamp                                      = strtotime( $currentYear . "W" . str_pad( $actual_week, 2, '0', STR_PAD_LEFT ) . $day );
				$day_name                                           = date( 'D', $day_timestamp );
				$schedule_date                                      = date( "j-n-Y", $day_timestamp );
				$data["weeks"][ $FirstWeekOfMonth ]['days'][ $day ] = [
					"title"    => $day_name,
					"date"     => $schedule_date,
					"randomid" => str_replace( "-", "", $schedule_date ),
				];

				//Get job schedules
				$schedule_date_filter                                        = date( "Y-n-j", strtotime( $schedule_date ) );
				$schedules                                                   = CrewSchedule::get()->filter( [ "StartDate" => $schedule_date_filter ] );
				$data["weeks"][ $FirstWeekOfMonth ]['days'][ $day ]['count'] = (int) $schedules->count();
				if ( $schedules ) {
					foreach ( $schedules as $item ) {
						$data["weeks"][ $FirstWeekOfMonth ]['days'][ $day ]['crew'][] = [
							"id"            => $item->ID,
							"crewTitle"     => strtoupper( $item->Crew()->FirstName ),
							"crewId"        => $item->CrewID,
							"colorCode"     => $this->genColorCodeFromText( $item->Crew()->FirstName ),
							"jobId"         => (int) $item->JobID,
							"jobTitle"      => $item->Job()->Title,
							"jobStage"      => $item->JobStage,
							"jobStageTitle" => $this->getStageTitle( $item->JobStage ),
							"duration"      => (double) $item->Duration,
						];
					}

				}

			}

			if ( $currentWeek == $FirstWeekOfMonth ) {
				$data["currentWeekSlide"] = $i;
			}

			$FirstWeekOfMonth = intval( date( 'W', strtotime( $start_end_date['end'] . " + 1 day" ) ) );

		}
		/* no more crews
			//Get Crew list
			$crews = Crew::get_for_users_city();
			foreach ( $crews as $crew ) {
				$data["crews"][] = array(
					"ID"        => $crew->ID,
					"title"     => strtoupper( $crew->FirstName ),
					"colorCode" => $this->genColorCodeFromText( $crew->FirstName ),
				);
			}
		*/

		//Get job list
		$jobs = Job::get_for_users_city();
		foreach ( $jobs as $job ) {
			$selectableStages = '';
			$build            = $job->inJobStage( 'B' );
			$dismantle        = $job->inJobStage( 'D' );

			$selectableStages .= $build ? '' : 'B';
			if ( $build ) {
				$selectableStages .= $dismantle ? '' : 'D';
			}

			if ( $build ) {
				$selectableStages .= "A";
			}

			$data["jobs"][] = [
				"ID"               => $job->ID,
				"title"            => $job->title,
				'selectableStages' => $selectableStages,
			];
		}

		return json_encode( $data );
	}

	public function getStageTitle( $stage = '' ) {
		switch ( $stage ) {
			case 'A':
				return "Stage ALTERATION";
				break;

			case 'B':
				return "Stage BUILD";
				break;

			default:
				return "Stage DISMANTLE";
				break;
		}
	}

//Post new schedules
	public function post() {
		$this->response->addHeader( 'Content-Type', 'application/json' );
		$job_duration   = $this->request->postVar( "duration" );
		$start_date     = $this->request->postVar( "job_start_date" );
		$job_id         = $this->request->postVar( "job_id" );
		$job_stage      = $this->request->postVar( "job_stage" );
		$original_date  = $this->request->postVar( "original_date" );
		$conflict_alert = false;

		if ( empty( $job_id ) || empty( $crew_id ) ) {
			return json_encode( [ "success" => false ] );
		}

		$original_date = date( "Y-m-d", strtotime( $original_date ) );

		$schedule = CrewSchedule::get()->filter(
			[
				"StartDate" => $original_date,
				"JobID"     => $job_id,
				"JobStage"  => $job_stage,
			]
		)->first();
		/*
			if ( ! $schedule ) {
			$schedule = new CrewSchedule;
			//Check conflicts
			//			$conflict = $this->isCrewFree( $crew_id, $start_date, $job_duration );
			if ( $conflict['Conflict'] != 0 ) {
			$start_date     = $conflict['NextDate'];
			$conflict_alert = true;
			}
			}
		*/
		$jobdate  = date( "Y-n-j", strtotime( $start_date ) );
		$end_date = $this->getEndDate( $start_date, $job_duration );

		$schedule->StartDate = $jobdate;
//		$schedule->CrewID    = $crew_id;
		$schedule->JobID    = $job_id;
		$schedule->JobStage = $job_stage;
		$schedule->Duration = $job_duration;
		$schedule->EndDate  = $end_date;
		$schedule->write();

		$data = [
			"message"  => "Job schedule completed",
			"success"  => true,
			"conflict" => $conflict_alert,
		];

		return json_encode( $data );
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

	public function monthly_resourcing() {
		$Title = "";

		return $this->renderWith( [ "CrewSchedule_monthly_resourcing" ] );
	}

	public function get_resourcing() {
		$this->response->addHeader( 'Content-Type', 'application/json' );
		$data = [];

		$cj          = CrewSchedule::get();
		$cjGroupList = GroupedList::create( $cj );
		$crewJobs    = $cjGroupList->GroupedBy( 'JobDate' );
		foreach ( $crewJobs as $key ) {
			$totalJobs = $key->Children->count();
			$durations = 0;
			foreach ( $key->Children as $job ) {
				$durations += $job->Duration;
			}

			$percentage = ( $durations / 20 ) * 100;

			$data[] = [
				'date'  => $key->JobDate,
				"val"   => $percentage . "%",
				'color' => $this->getColor( $percentage ),
			];
		}

		return json_encode( $data );
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

	/**
	 * Check if crew is free for a job
	 *
	 * @return Array
	 */
	public function isCrewFree( $crew_id, $job_date, $duration ) {
		$new_job_date     = date( "Y-m-d", strtotime( $job_date ) );
		$new_job_end_date = date( "Y-m-d", strtotime( $job_date . " + " . round( $duration, 0, PHP_ROUND_HALF_DOWN ) . " day" ) );

		$NextDate            = null;
		$Conflict            = 0;
		$conflictOnStartDate = false;
		//Start date check
		$startDateChk = CrewSchedule::get()->filter( [ "StartDate" => $new_job_date, "CrewID" => $crew_id ] )->first();
		if ( ! $startDateChk ) {
			$sqlQuery = new SQLQuery();
			$sqlQuery->setFrom( 'CrewSchedule' );
			$sqlQuery->setSelect( 'COUNT(*)' );
			$sqlQuery->addWhere( "CrewID = '" . $crew_id . "' AND (EndDate BETWEEN '" . $new_job_date . "' AND '" . $new_job_end_date . "')" );
			$sqlQuery->setOrderBy( "EndDate DESC" );
			$conflictChk = $sqlQuery->execute();
			$Conflict    = $conflictChk->value();
		} else {
			$Conflict            = 1;
			$conflictOnStartDate = true;
		}

		if ( $Conflict != 0 ) {
			if ( $conflictOnStartDate ) {
				$NextDate = date( "Y-n-j", strtotime( $startDateChk->EndDate . "+ 1 day" ) );
				if ( date( "w", strtotime( $NextDate ) ) == 0 ) {
					$NextDate = date( "Y-n-j", strtotime( $NextDate . " + 1 day" ) );
				}
			} else {
				$sqlQuery = new SQLQuery();
				$sqlQuery->setFrom( 'CrewSchedule' );
				$sqlQuery->setSelect( 'EndDate' );
				$sqlQuery->addWhere( "CrewID = '" . $crew_id . "' AND (EndDate BETWEEN '" . $new_job_date . "' AND '" . $new_job_end_date . "')" );
				$sqlQuery->setOrderBy( "EndDate DESC" );
				$sqlQuery->setLimit( 1 );
				$PossibleNextJobSchedule = $sqlQuery->execute();
				foreach ( $PossibleNextJobSchedule as $row ) {
					$NextDate = date( "Y-n-j", strtotime( $row['EndDate'] . "+ 1 day" ) );
					if ( date( "w", strtotime( $NextDate ) ) == 0 ) {
						$NextDate = date( "Y-n-j", strtotime( $NextDate . " + 1 day" ) );
					}
				}
			}

		}

		return compact( 'Conflict', 'NextDate' );
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

	public function calendar() {
		$cal        = $this->gridFieldData();
		$cal_array  = [];
		$jobs_array = [];
		if ( $cal->count() > 0 ) {
			foreach ( $cal as $event ) {
				$start_dateTime = new DateTime( $event->StartDate );
				$end_dateTime   = new DateTime( $event->EndDate );

				$cal_array[] = [
					"id"         => $event->ID,
					"title"      => $event->Job()->Name,
					"start"      => $start_dateTime->format( 'c' ),
					"end"        => $end_dateTime->format( 'c' ),
					"editable"   => true,
					"allDay"     => true,
					"eventColor" => $this->genColorCodeFromText( $event->Job()->Name ),
				];

			}
		}

		$output = json_encode( $cal_array );

		$dateToday = ( new DateTime() )->format( 'c' );

		Requirements::customScript( <<<JS
(function($) {
        jQuery('#calendar').fullCalendar({
            header: {
                left: 'title',
                center: 'listDay,listWeek,month',
                right: 'prev,next'
            },
            views: {
				listDay: { buttonText: 'list day' },
				listWeek: { buttonText: 'list week' }
			},
            now: '{$dateToday}',
            scrollTime: '08:00',
            aspectRatio: 1.8,
            defaultView: 'listDay',
            editable: true,
			selectable: true,
			eventLimit: true,

			displayEventTime : false,
            eventRender: function(event, element){
              element.popover({
                  animation:true,
                  delay: 300,
                  content:  event.title,
                  trigger: 'hover',
                  placement: 'top',
                  html: 'true'
              });
            },
            events:  {$output},
            schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',

            eventResize: function(event, delta, revertFunc) {
		        update_calendar(event);
		    },
		    eventDrop: function(event, delta, revertFunc) {
		    	 update_calendar(event);
		    }
        });

        function update_calendar(event){
        	var data = {
        		id: event.id,
        		start:  event.start.format(),
        		end: event.end.format()
        	};
        	$.ajax({
		        type: "POST",
		        url: "scheduling/calendar-update/",
		        data: data,
		        success: function(response){
		            console.log('calendar updated!');
		        },
		        error: function(response){
		            console.log(event);
		        },
		    });
        }

    })(jQuery);
JS
		);

		return $this->renderWith( [ "CrewSchedule_calendar", "Page" ] );

	}

	/**
	 *
	 * Calender view ajax update
	 *
	 */
	public function calendar_update( SS_HTTPRequest $request ) {
		if ( $request->isPost() ) {
			$event = CrewSchedule::get()->byID( $request->postVar( "id" ) );
			if ( $event ) {
				$event->StartDate = $request->postVar( "start" );
				$event->EndDate   = $request->postVar( "end" );
				$event->write();

				return true;
			} else {
				return false;
			}
		}

		return $this->httpError( 404 );
	}

}