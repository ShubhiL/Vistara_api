<?php
require_once 'include/DB_Functions.php';
    $db = new DB_Functions();
 
     $response = array("error" => FALSE);
	
    $foodlistArray = $db->fetchFoodContent();
    $number = count($foodlistArray);
    if($foodlistArray){
    	$response["error"] = FALSE;
    	for ($i=0; $i< $number; $i++){
		   
		     $response["food"][$i]= array("food_id"=>$foodlistArray[$i][0],"food_name"=>$foodlistArray[$i][1]);

		}
		echo json_encode($response);die;

    }
    else{
    	$response["error"] = TRUE;
        $response["error_msg"] = "error in fetching";
        echo json_encode($response);
    }

	
?>
