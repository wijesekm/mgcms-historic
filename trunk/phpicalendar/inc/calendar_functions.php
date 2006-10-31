<?php

// This function returns a list of all calendars that the current user
// has access to. Basically, all local calendars found in the calendar
// directory, plus any webcals listed in the configuration file, but
// excluding blacklisted calendars and locked calendars which the user,
// if logged in, does not have access to.
//
// $username		= The username. Empty if no username provided.
// $password		= The password. Empty if no password provided.
// $cal_filename	= The calendar name without .ics.
// $config 			= The config array
//$ALL_CALENDARS_COMBINE = the all cals
// $admin			= True if this is an administrative request, in
//					  which case all local calendars only will be
//					  returned.
function availableCalendars($username, $cal_filename,$config,$ALL_CALENDARS_COMBINE,$admin = false) {

	// Create the list of available calendars.
	$calendars = array();

	// Make a local copy of the requested calendars.
	if (!is_array($cal_filename)){
		$cal_filename_local = array($cal_filename);
	}
	else{
		$cal_filename_local = $cal_filename;
	}

	// Create the list of available calendars.
	$calendars = array();
	
	// This array keeps track of paths we need to search.
	$search_paths = array($GLOBALS["MANDRIGO_CONFIG"]["PLUGIN_PATH"].ICAL_CAL_PATH);
		
	// Add web calendars.
	if ($cal_filename_local[0] == $ALL_CALENDARS_COMBINED || $admin)	{
		if (!$admin) {
			foreach ($config['list_webcals'] as $file) {
				// Make sure the URL ends with .ics.
				if (!preg_match("/.ics$/i", $file)) continue;
				
				// Add this calendar.
				array_push($calendars, $file);
			}
		}
	}
	
	// Set some booleans that will dictate our search.
	$find_all = ($cal_filename_local[0] == $ALL_CALENDARS_COMBINED || $admin);
	
	// Process all search paths.
	while (!empty($search_paths)) {
		// Read the next search path.
		$search_path = array_pop($search_paths);
			
		// This array keeps track of filenames we need to look at.
		$files = array();
		
		// Build the list of files we need to check.
		//
		// We do a full directory search if we are supposed to find all
		// calendars, the calendar we're looking for may be in a
		// subdirectory, or we are supporting the iCal repository format.
		// The latter is necessary because the calendar name cannot be
		// used to identify the calendar filename.
		if ($find_all || $config['recursive_path'] == 'yes' || $config['support_ical'] == 'yes') {
			// Open the directory.
			$dir_handle = @opendir($search_path)
				or return false;
			if ($dir_handle === false){
				return false;
			}
				
			// Add each file in the directory that does not begin with a dot.
			while(false !== ($file = readdir($dir_handle))){
				// Make sure this is not a dot file.
				if (preg_match("/^\./", $file)) continue;
				array_push($files, "$search_path/$file");
			}
		} 
		else {
			// The file process block below expects actual filenames. So
			// we have to append '.ics' to the passed in calendar names.
			foreach ($cal_filename_local as $filename) {
				array_push($files, "$search_path/$filename".".ics");
			}
		}
		
		// Process files.
		foreach ($files as $file) {
			// Push directories onto the search paths if recursive paths is
			// turned on.
			if (is_dir($file)) {
				if ($config['recursive_path'] == 'yes') {
					array_push($search_paths, $file);
				}
				continue;
			}
			
			// Make sure the file is real.
			if (!is_file($file)) continue;
			
			// Make sure the file ends in .ics.
			if (!preg_match("/^.*\.ics$/i", $file)) continue;
			
			// Make sure this is not a blacklisted calendar.
			$cal_name = getCalendarName($file);
			if (in_array($cal_name, $config['blacklisted_cals'])) continue;
			
			// Add this calendar if we're looking for it, and remove it's name
			// from the local list because we've found it.
			if ($find_all || in_array($cal_name, $cal_filename_local)) {
				array_push($calendars, $file);
				$cal_filename_local = array_diff($cal_filename_local, array($cal_name));
				
				// If the local list is empty, we're done.
				if (empty($cal_filename_local))
					break 2;
			}
		}
	}

	// Return the sorted calendar list.
	natcasesort($calendars);
	return $calendars;
}

// This function returns the result of the availableCalendars function
// but only includes the calendar names.
//
// $username		= The username. Empty if no username provided.
// $password		= The password. Empty if no password provided.
// $cal_filename	= The calendar name without .ics.
// $admin			= True if this is an administrative request, in
//					  which case all local calendars only will be
//					  returned.
function availableCalendarNames($username,$cal_filename, $admin = false) {
	// Grab the available calendar paths.
	$calendars = availableCalendars($username, $cal_filename, $admin);

	// Strip the paths off the calendars.
	foreach (array_keys($calendars) as $key) {
		$calendars[$key] = getCalendarName($key);
	}
	
	// Return the sorted calendar names.
	natcasesort($calendars);
	return $calendars;
}

// This function returns the calendar name for the specified calendar
// path.
//
// $cal_path	= The path to the calendar file.
function getCalendarName($cal_path,$config) {

	// If iCal is supported, check the directory for an Info.plist.
	if (config['support_ical'] == 'yes') {
		// Look for the Info.plist file.
		$plist_filename = dirname($cal_path)."/Info.plist";
		if (is_file($plist_filename)) {
			// Read the Info.plist.
			$handle = fopen($plist_filename, 'r');
			$contents = fread($handle, filesize($plist_filename));
			fclose($handle);
						
			// Pull out the calendar name.
			$num_matches = preg_match("/<key>Title<\/key>\s*?<string>(.+?)<\/string>/i", $contents, $matches);
			if ($num_matches > 0)
				return $matches[1];
		}
	}
	
	// At this point, just pull the name off the file.
	return str_replace(".ics", '', basename($cal_path));
}

// This function prints out the calendars available to the user, for
// selection. Should be enclosed within a <select>...</select>, which
// is not printed out by this function.
//
// $cals	= The calendars (entire path, e.g. from availableCalendars).
function display_ical_list($cals, $pick=FALSE) {
	global $cal, $ALL_CALENDARS_COMBINED, $current_view, $getdate, $calendar_lang, $all_cal_comb_lang, $cal_filelist, $cal_displaynames;
	// Print each calendar option.
	foreach ($cals as $cal_tmp) {
		// Format the calendar path for display.
		//
		// Only display the calendar name, replace all instances of "32" with " ",
		// and remove the .ics suffix.
		$cal_displayname_tmp = getCalendarName($cal_tmp);
		$cal_displayname_tmp = str_replace("32", " ", $cal_displayname_tmp);
		#overwrite the display name if we already have a real name
		if (is_numeric(array_search($cal_tmp, $cal_filelist))){
			$cal_displayname_tmp = $cal_displaynames[array_search($cal_tmp,$cal_filelist)];
		}else{
			# pull the name from the $cal_tmp file
			$ifile = @fopen($cal_tmp, "r");
			if ($ifile == FALSE) exit(error($lang['l_error_cantopen'], $cal_tmp));
			while (!feof($ifile)) {
				$line = fgets($ifile, 1024);
				$line = trim($line);
				if (ereg ("([^:]+):(.*)", $line, $regs)){
					$field = $regs[1];
					$data = $regs[2];
					$property = $field;
					$prop_pos = strpos($property,';');
					if ($prop_pos !== false) $property = substr($property,0,$prop_pos);
					$property = strtoupper($property);
					if ($property == "X-WR-CALNAME"){
						$cal_displayname_tmp = $data;
						break;
					}
				}	
				#stop reading if we find an event or timezone before there's a name
				if ($line == "BEGIN:VTIMEZONE" ||$line == "BEGIN:VEVENT") break;
			}
			echo "</pre>";

		}

		// If this is a webcal, add 'Webcal' to the display name.
		if (preg_match("/^(https?|webcal):\/\//i", $cal_tmp)) {
			$cal_displayname_tmp .= " Webcal";
		}

		// Otherwise, remove all the path information, since that should
		// not be used to identify local calendars. Also add the calendar
		// label to the display name.
		else {
			// Strip path and .ics suffix.
			$cal_tmp = getCalendarName($cal_tmp);

			// Add calendar label.
			$cal_displayname_tmp .= " $calendar_lang";
		}

		// Encode the calendar path.
		$cal_encoded_tmp = urlencode($cal_tmp);

		// Display the option.
		//
		// The submitted calendar will be encoded, and always use http://
		// if it is a webcal. So that is how we perform the comparison when
		// trying to figure out if this is the selected calendar.
		if($pick) {
			if (in_array($cal_encoded_tmp, explode(",", $cal)) || count($cals) == count(explode(",", $cal))) {
					$return .= "<option value=\"$cal_encoded_tmp\" selected=\"selected\">$cal_displayname_tmp</option>\n";
			} else {
					$return .= "<option value=\"$cal_encoded_tmp\">$cal_displayname_tmp</option>\n";	
			}
		} else {
		$cal_httpPrefix_tmp = str_replace('webcal://', 'http://', $cal_tmp);
		if ($cal_httpPrefix_tmp == urldecode($cal)) {
			$return .= "<option value=\"$current_view.php?cal=$cal_encoded_tmp&amp;getdate=$getdate\" selected=\"selected\">$cal_displayname_tmp</option>";
		} else {
			$return .= "<option value=\"$current_view.php?cal=$cal_encoded_tmp&amp;getdate=$getdate\">$cal_displayname_tmp</option>";	
		}
	 }			
	}			

	// option to open all (non-web) calenders together
	if (!$pick) {
		if ($cal == $ALL_CALENDARS_COMBINED) {
			$return .=  "<option value=\"$current_view.php?cal=$ALL_CALENDARS_COMBINED&amp;getdate=$getdate\" selected=\"selected\">$all_cal_comb_lang</option>";
		} else {
			$return .=  "<option value=\"$current_view.php?cal=$ALL_CALENDARS_COMBINED&amp;getdate=$getdate\">$all_cal_comb_lang</option>";
		}
	}
	return $return;
}
