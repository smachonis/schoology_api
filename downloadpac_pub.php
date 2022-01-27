<?php
// downloadpac.php - Steven Machonis Created in 2021
// Function to download Parent Access Codes from Schoology to mass-import into our SIS


require_once('schoology_sdk/SchoologyApi.class.php');
CONST CONSUMER_KEY = '';
CONST CONSUMER_SECRET = '';
$schoology = new SchoologyApi(CONSUMER_KEY,CONSUMER_SECRET,'','','',TRUE); 
//echo "<pre>";
//print_r($schoology);
//echo "</pre>";
$rowCount = 0;
$returningRows = 1;
$roles = '715101,715103,715105,715107';  //All Student Roles
//$roles = '715103';  //Testing Role
$fileOutput = [];
array_push($fileOutput, array('PersonID','Parent_Access_Code','UpdatedDate'));

while($returningRows == 1){
	$result = $schoology->api('/users&parent_access_codes=TRUE&role_ids='.$roles.'&start='.$rowCount.'&limit=200');

	//echo "<pre>Records:<br />";
	//print_r($result);
	//echo "</pre>";
	
	foreach($result->result->user as $rec) {

		if(strpos($rec->school_uid, '_') === false){     //Removes old 1_ or 2_ ids since they won't be personIDs from Campus
			if(strlen($rec->parent_access_code) === 9){  //Checks string length since there are 12 and 9 character access codes
				$cdlen = 3;                              //that need different spacing for the dash (-)
			} else {
				$cdlen = 4;
			}
			$fixedPAC = implode('-', str_split($rec->parent_access_code, $cdlen));     // Inserts dashes (-) into PAC
			$rowArray = array(ltrim($rec->school_uid,'s'), $fixedPAC, date('m/d/Y'));  //removes prefix 's' from personID
			array_push($fileOutput,$rowArray);
		}
		
	}

	if(count($result->result->user) === 0) {
		$returningRows = 0;
	} else {
		$rowCount+=200;
	}

}

//echo "Returning Rows: ".$returningRows."<br />";
//echo "Records Found: ".count($fileOutput)."<br />";
//echo "<pre>";
//var_dump($fileOutput);
//echo "</pre>";

$out = fopen('c:\Integrations\Schoology\dasd_pac.csv', 'w');  //Opens output csv file and inserts each row of the array
foreach ($fileOutput as $fileRow) {                           //into the csv file
    fputcsv($out, $fileRow);
}
fclose($out);

?>