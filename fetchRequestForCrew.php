<?php
require_once 'include/DB_Functions.php';
    $db = new DB_Functions();
 
     $response = array("error" => FALSE);
	
    $RequestArray = $db->fetchRequestContent();
    $number = count($RequestArray);
    if($RequestArray){
    	$response["error"] = FALSE;
    	for ($i=0; $i< $number; $i++){
		   
		     $response["request"][$i]= array("request_id"=>$RequestArray[$i][0],"seat_no"=>$RequestArray[$i][1], "request_made"=>$RequestArray[$i][2]);

		}
		echo json_encode($response);die;

    }
    else{
    	$response["error"] = TRUE;
        $response["error_msg"] = "error in fetching";
        echo json_encode($response);
    }

	
?>
