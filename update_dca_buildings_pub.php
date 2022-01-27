<?php
// update_dca_buildings.php - Steven Machonis 2021
// Function to update our Cyber School Dual-Enrolled students in Campus to be dual-enrolled in Schoology.
// Schoology OneRoster Sync only syncs one building, usually their home building

	require_once('schoology_sdk/SchoologyApi.class.php');
	CONST CONSUMER_KEY = '';
	CONST CONSUMER_SECRET = '';
	$schoology = new SchoologyApi(CONSUMER_KEY,CONSUMER_SECRET,'','','',TRUE);
	$studentBuildingArray = array_map('str_getcsv',file('c:\Integrations\Schoology\in\dca_student_buildings.csv'));
	//File contains student's uniqueID, home building, and cyber building
	//Schoology API code, which is a custom field in our SIS

	$firstrow = 1;
	//echo "<pre>";
	//print_r($studentBuildingArray);
	//echo "</pre>";

	$f = fopen('c:\Integrations\Schoology\changelog\update_dca_buildings_log.txt','a');

	foreach($studentBuildingArray as $stu) {  
			if($firstrow == 1) {
				$firstrow = 0;
			} else {
				usleep( 250000 );  //delay to prevent too many API requests to Schoology in short amount of time
				$schoologyID = $schoology->api('/users&school_uids='.$stu[0]);
				
				if($schoologyID->result->total == 1){
					$userID = $schoologyID->result->user[0]->uid;
					$currentBld = $schoologyID->result->user[0]->building_id;
					$currentAddBld = $schoologyID->result->user[0]->additional_buildings;

					//echo "<pre>";
					//print_r($schoologyID);
					//echo "</pre>";

					//Confirms current buildings to see if correct, otherwise it will update them
					if($currentBld != $stu[1] || $currentAddBld != $stu[2]) {
						$update_stud_obj = ['building_id'=>$stu[1] , 'additional_buildings'=>$stu[2]];
						fwrite($f,date("Y-m-d H:i:s").' Updated '.$stu[0].PHP_EOL);
						$schoology_update = $schoology->api('/users/'.$userID,'PUT',$update_stud_obj);
					} else {
						//echo 'Skipped '.$stu[0].'<br />';  SKip if already correct.  No logging needed.
					}
				} else {
					fwrite($f,date("Y-m-d H:i:s").' '.$stu[0].' Not Found'.PHP_EOL);
				}
			}		
	}

	fclose($f);

?>