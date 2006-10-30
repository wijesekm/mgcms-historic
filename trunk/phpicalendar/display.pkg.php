<?php
/**********************************************************
    globals.pkg.php
    ical ver 2.22
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 10-30-06

	PHP iCalendar is copyright the PHP iCalendar team (http://phpicalendar.net/)
	and is published under the GNU General Public License
	
	MandrigoCMS is Copyright (C) 2005-2006 the MandrigoCMS Group
	
    ##########################################################
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

	###########################################################

**********************************************************/

//
//To prevent direct script access
//
if(!defined('START_MANDRIGO')){
    die('<html><head>
            <title>Forbidden</title>
        </head><body>
            <h1>Forbidden</h1><hr width="300" align="left"/><p>You do not have permission to access this file directly.</p>
        </html></body>');
}

//this file will contain display functionality which will be called by the
//{packagename}_display_hook and {packagename}_vars_hook function which you will write.
//Basically do what ever you want with it.
//as far as formatting goes please follow the mandrigo coding guidelines
class phpical_display{
	
	var $config
	function phpical_display(){
		$config_default('default_view'=>'day'
    			,'minical_view'=>'current'
       			,'default_cal'=>'all'
       			,'week_start_day'=>'Sunday'
       			,'week_length'=>'7'
       			,'day_start'=>'0700'
       			,'day_end'=>'2300'
       			,'gridlength'=>'15'
       			,'num_gears'=>'1'
			    ,'month_event_lines'=>'1'
				,'tomorrows_events_lines'=>'1'
		       	,'allday_week_lines'=>'1'
		      	,'week_events_lines'=>'1'
		        ,'seconds_offset'=>''
		        ,'bleed_time'=>'-1'
		        ,'allow_webcals'=>'no'
		        ,'this_months_events'=>'yes'
		        ,'enable_rss'=>'yes'
		        ,'show_search'=>'yes'
		        ,'allow_preferences'=>'no'
		        ,'printview_default'=>'no'
		        ,'show_todos'=>'no'
		        ,'show_completed'=>'no'
		        ,'support_ical'=>'no'
		        ,'recursive_path'=>'no'
		        ,'save_parsed_cals'=>'yes'
		        ,'webcal_hours'=>'4'
		        ,'unique_colors'=>'7'
		        ,'phpicalendar_publishing'=>'');
	}
    function phpical_day($i){
		if(isset($GLOBALS['HTTP_GET']['JUMPTO_DAY'])){
			$jumpto_day_time = strtotime($GLOBALS['HTTP_GET']['JUMPTO_DAY']);
			if ($jumpto_day_time == -1){
				$getdate = date('Ymd', time() + $second_offset); 
			} 
			else{
				$getdate = date('Ymd', $jumpto_day_time);
			}
		}
		if (!isset($getdate)) {
			if(isset($GLOBALS['HTTP_GET']['GETDATE']) && ($GLOBALS['HTTP_GET']['GETDATE'] !== '')){
				$getdate = $GLOBALS['HTTP_GET']['GETDATE'];
			} 
			else{
				$getdate = date('Ymd', time() + $second_offset);
			}
		}
		$current_view = 'day';
		include_once($GLOBALS["MANDRIGO_CONFIG"]["PLUGIN_PATH"].ICAL_BASE_PATH.'date_functions.'.PHP_EXT);
		include_once($GLOBALS["MANDRIGO_CONFIG"]["PLUGIN_PATH"].ICAL_BASE_PATH.'draw_functions.'.PHP_EXT);
		include_once($GLOBALS["MANDRIGO_CONFIG"]["PLUGIN_PATH"].ICAL_BASE_PATH.'overlapping_events.'.PHP_EXT);
		require_once($GLOBALS["MANDRIGO_CONFIG"]["PLUGIN_PATH"].ICAL_BASE_PATH.'ical_parser.'.PHP_EXT);
		require_once($GLOBALS["MANDRIGO_CONFIG"]["PLUGIN_PATH"].ICAL_BASE_PATH.'list_functions.'.PHP_EXT);
		require_once($GLOBALS["MANDRIGO_CONFIG"]["PLUGIN_PATH"].ICAL_BASE_PATH.'template.'.PHP_EXT);

		if ($this->config['minical_view'] == 'current')}{
			$this->config['minical_view'] = 'day';	
		}
		$weekstart 		= 1;
		$unix_time 		= strtotime($getdate);
		$today_today 	= date('Ymd', time() + $second_offset);  
		$next_day		= date('Ymd', strtotime("+1 day",  $unix_time));
		$prev_day 		= date('Ymd', strtotime("-1 day",  $unix_time));
		
		$display_date = localizeDate($GLOBALS['LANGUAGE']['ICAL_FORMAT_DAY'], $unix_time);//fix $dateFormat_day
		$sidebar_date = localizeDate($GLOBALS['LANGUAGE']['ICAL_FORMAT_WEEKLIST'], $unix_time);//fix $dateFormat_week_list
		$start_week_time = strtotime(dateOfWeek($getdate, $this->config['week_start_day']));
		
		$username='';
		$password='';
		
		// select for calendars
		$list_icals 	= display_ical_list(availableCalendars($username, $password, $ALL_CALENDARS_COMBINED));
		$list_years 	= list_years();
		$list_months 	= list_months();
		$list_weeks 	= list_weeks();
		$list_jumps 	= list_jumps();
		$list_calcolors = list_calcolors();
		$list_icals_pick = display_ical_list(availableCalendars($username, $password, $ALL_CALENDARS_COMBINED), TRUE);
		$tpl = new ical_page($GLOBALS['MANDRIGO_CONFIG']['TEMPLATE_PATH'].$GLOBALS['PAGE_DATA']['DATAPATH'].$GLOBALS['PAGE_DATA']['ID'].'_'.$i.'_day'.TPL_EXT);
		
		$page->replace_files(array(
			'header'			=> $GLOBALS['MANDRIGO_CONFIG']['TEMPLATE_PATH'].$GLOBALS['PAGE_DATA']['DATAPATH'].$GLOBALS['PAGE_DATA']['ID'].'_'.$i.'_header'.TPL_EXT,
			'event_js'			=> $GLOBALS['MANDRIGO_CONFIG']['TEMPLATE_PATH'].$GLOBALS['PAGE_DATA']['DATAPATH'].$GLOBALS['PAGE_DATA']['ID'].'_'.$i.'_event.js',
			'footer'			=> $GLOBALS['MANDRIGO_CONFIG']['TEMPLATE_PATH'].$GLOBALS['PAGE_DATA']['DATAPATH'].$GLOBALS['PAGE_DATA']['ID'].'_'.$i.'_footer'.TPL_EXT,
		    'sidebar'           => $GLOBALS['MANDRIGO_CONFIG']['TEMPLATE_PATH'].$GLOBALS['PAGE_DATA']['DATAPATH'].$GLOBALS['PAGE_DATA']['ID'].'_'.$i.'_sidebar'.TPL_EXT,
		    'search_box'        => $GLOBALS['MANDRIGO_CONFIG']['TEMPLATE_PATH'].$GLOBALS['PAGE_DATA']['DATAPATH'].$GLOBALS['PAGE_DATA']['ID'].'_'.$i.'_search_box'.TPL_EXT
			));
		
		$page->replace_tags(array(
			'version'			=> ICAL_VERSION,
			'charset'			=> $GLOBALS["LANGUAGE"]["CHARSET"],
			'default_path'		=> '',
			'template'			=> $template,
			'cal'				=> $cal,
			'getdate'			=> $getdate,
			'getcpath'			=> "&cpath=$cpath",
			'cpath'				=> $cpath,
			'calendar_name'		=> $cal_displayname,
			'current_view'		=> $current_view,
			'display_date'		=> $display_date,
			'sidebar_date'		=> $sidebar_date,
			'rss_powered'	 	=> '',//todo
			'rss_available' 	=> '',//todo
			'rss_valid' 		=> '',//todo
			'show_search' 		=> $show_search,
			'next_day' 			=> $next_day,
			'prev_day'	 		=> $prev_day,
			'show_goto' 		=> '',
			'list_icals' 		=> $list_icals,
			'list_icals_pick' 	=> $list_icals_pick,
			'list_years' 		=> $list_years,
			'list_months' 		=> $list_months,
			'list_weeks' 		=> $list_weeks,
			'list_jumps' 		=> $list_jumps,
			'legend'	 		=> $list_calcolors,
			'style_select' 		=> $style_select,
			'l_goprint'			=> $GLOBALS['LANGUAGE']['ICAL_GOPRINT'],
			'l_preferences'		=> '',
			'l_calendar'		=> $GLOBALS['LANGUAGE']['ICAL_CALENDAR'],
			'l_legend'			=> $GLOBALS['LANGUAGE']['ICAL_LENGEND'],
			'l_tomorrows'		=> $GLOBALS['LANGUAGE']['ICAL_TOMORROWS'],
			'l_jump'			=> $GLOBALS['LANGUAGE']['ICAL_JUMP'],
			'l_todo'			=> $GLOBALS['LANGUAGE']['ICAL_TODO'],
			'l_day'				=> $GLOBALS['LANGUAGE']['ICAL_DAY'],
			'l_week'			=> $GLOBALS['LANGUAGE']['ICAL_WEEK'],
			'l_month'			=> $GLOBALS['LANGUAGE']['ICAL_MONTH'],
			'l_year'			=> $GLOBALS['LANGUAGE']['ICAL_YEAR'],
			'l_pick_multiple'	=> $GLOBALS['LANGUAGE']['ICAL_PICK_MULTIPLE'],
			'l_powered_by'		=> $GLOBALS['LANGUAGE']['ICAL_POWERED_BY'],
			'l_subscribe'		=> $GLOBALS['LANGUAGE']['ICAL_SUBSCRIBE'],
			'l_download'		=> $GLOBALS['LANGUAGE']['ICAL_DOWNLOAD'],
			'l_search'			=> $GLOBALS['LANGUAGE']['ICAL_SEARCH'],
			'l_this_site_is'	=> $GLOBALS['LANGUAGE']['ICAL_THIS_SITE'],
			'allow_preferences'	=> ''
			));		
		if ($show_search != 'yes') {
			$page->nosearch($page);
		}
			
		$page->draw_day($page);
		$page->tomorrows_events($page);
		$page->get_vtodo($page);
		$page->draw_subscribe($page);
		return $page->output();
	}
	function ical_init(){
		$GLOBALS['LANGUAGE']['ICAL_DAYSOFWEEK']=explode(",",$GLOBALS['LANGUAGE']['ICAL_DAYSOFWEEK']);
 		$GLOBALS['LANGUAGE']['ICAL_DAYSOFWEEK_SHORT']=explode(",",$GLOBALS['LANGUAGE']['ICAL_DAYSOFWEEK_SHORT']);
 		$GLOBALS['LANGUAGE']['ICAL_DAYSOFWEEK_RSHORT']=explode(",",$GLOBALS['LANGUAGE']['ICAL_DAYSOFWEEK_RSHORT']);
 		$GLOBALS['LANGUAGE']['ICAL_MONTHS']=explode(",",$GLOBALS['LANGUAGE']['ICAL_MONTHS']);
 		$GLOBALS['LANGUAGE']['ICAL_MONTHS_SHORT']=explode(",",$GLOBALS['LANGUAGE']['ICAL_MONTHS_SHORT']);
	}
	function ical_calinit(){
		$cal_filenames = array();
		if (isset($_GET['cal'])) {
			// If the cal value is not an array, split it into an array on
			// commas.
			if (!is_array($_GET['cal']))
				$_GET['cal'] = explode(',', $_GET['cal']);
			
			// Grab the calendar filenames off the cal value array.
			$cal_filenames = $_GET['cal'];
		} else {
			if (isset($default_cal_check)) {
				if ($default_cal_check != $ALL_CALENDARS_COMBINED) {
					$calcheck = $calendar_path.'/'.$default_cal_check.'.ics';
					$calcheckopen = @fopen($calcheck, "r");
					if ($calcheckopen == FALSE) {
						$cal_filenames[0] = $default_cal;
					} else {
						$cal_filenames[0] = $default_cal_check;
					}
				} else {
					$cal_filenames[0] = $ALL_CALENDARS_COMBINED;
				}
			} else {
				$cal_filenames[0] = $default_cal;
			}
		}
		//load cal_filenames if $ALL_CALENDARS_COMBINED
		if ($cal_filenames[0] == $ALL_CALENDARS_COMBINED){
			$cal_filenames = availableCalendars($username, $password, $ALL_CALENDARS_COMBINED);
		}
		// Separate the calendar identifiers into web calendars and local
		// calendars.
		$web_cals = array();
		$local_cals = array();
		foreach ($cal_filenames as $cal_filename) {
			// If the calendar identifier begins with a web protocol, this is a web
			// calendar.
			$cal_filename = urldecode($cal_filename); #need to decode for substr statements to identify webcals
			$cal_filename = str_replace(' ','%20', $cal_filename); #need to reencode blank spaces for matching with $list_webcals
			if (substr($cal_filename, 0, 7) == 'http://' ||
				substr($cal_filename, 0, 8) == 'https://' ||
				substr($cal_filename, 0, 9) == 'webcal://')
			{
				$web_cals[] = $cal_filename;
			}
			
			// Otherwise it is a local calendar.
			else {
				// Check blacklisted.
				if (in_array($cal_filename, $blacklisted_cals)  && $cal_filename !='') {
					exit(error($lang['l_error_restrictedcal'], $cal_filename));
				}
				$local_cals[] = urldecode(str_replace(".ics", '', basename($cal_filename)));
			}
		}
		$cal_displaynames = array();
		$cal_filelist = array();
		$cals = array();
		foreach ($web_cals as $web_cal) {
			// Make some protocol alternatives, and set our real identifier to the
			// HTTP protocol.
			$cal_webcalPrefix = str_replace('http://','webcal://',$web_cal);
			$cal_httpPrefix = str_replace('webcal://','http://',$web_cal);
			$cal_httpsPrefix = str_replace('webcal://','https://',$web_cal);
			$cal_httpsPrefix = str_replace('http://','https://',$web_cal);
			$web_cal = $cal_httpPrefix;
				
			// We can only include this web calendar if we allow all web calendars
			// (as defined by $allow_webcals) or if the web calendar shows up in the
			// list of web calendars defined in config.inc.php.
			if ($allow_webcals != 'yes' &&
				!in_array($cal_webcalPrefix, $list_webcals) &&
				!in_array($cal_httpPrefix, $list_webcals) &&
				!in_array($cal_httpsPrefix, $list_webcals))
			{
				exit(error($lang['l_error_remotecal'], $web_cal));
			}
			
			// Pull the display name off the URL.
			$cal_displaynames[] = substr(str_replace('32', ' ', basename($web_cal)), 0, -4);
			
			// FIXME
			$cals[] = urlencode($web_cal);
			//$filename = $cal_filename;
			$subscribe_path = $cal_webcalPrefix;
			
			// Add the webcal to the available calendars.
			$cal_filelist[] = $web_cal;
		}
		
		// Process the local calendars.
		if (count($local_cals) > 0) {
			$local_cals = availableCalendars($username, $password, $local_cals);
			foreach ($local_cals as $local_cal) {
				$cal_displaynames[] = str_replace('32', ' ', getCalendarName($local_cal));
			}
			$cal_filelist = array_merge($cal_filelist, $local_cals);
			$cals = array_merge($cals, array_map("urlencode", array_map("getCalendarName", $local_cals)));
			
			// Set the download and subscribe paths from the config, if there is
			// only one calendar being displayed and those paths are defined.
			if (count($local_cals) == 1) {
				$filename = $local_cals[0];
				if (($download_uri == '') && (preg_match('/(^\/|\.\.\/)/', $filename) == 0)) {
					$subscribe_path = 'webcal://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/'."$cpath/".$filename;
					$download_filename = $filename;
				} elseif ($download_uri != '') {
					$newurl = eregi_replace("^(http://)", "", $download_uri); 
						$subscribe_path = 'webcal://'.$newurl.'/'."$cpath/".basename($filename);
						$download_filename = $download_uri.'/'."$cpath/".basename($filename);
				} else {
					$subscribe_path = "$cpath/";
					$download_filename = "$cpath/";
				}
			}
		}
		
		// We should only allow a download filename and subscribe path if there is
		// only one calendar being displayed.
		if (count($cal_filelist) > 1) {
			$subscribe_path = '';
			$download_filename = '';
		}
		
		// Build the final cal list. This is a comma separated list of the
		// url-encoded calendar names and web calendar URLs.
		$cal = implode(',', $cals);
		
		// Build the final display name used for template substitution.
		asort($cal_displaynames);
		$cal_displayname = implode(', ', $cal_displaynames);
	}
}
?>
