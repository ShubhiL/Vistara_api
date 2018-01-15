<?php
 
require_once 'include/DB_Functions.php';
$db = new DB_Functions();
 
// json response array
$response = array("error" => FALSE);
 
if (isset($_GET['request_id'])) {
 
    // receiving the post params
    $request_id= $_GET['request_id'];
    

    
        if ($db->updateStatus($request_id, 2)) {
        // user already existed
        $response["response"] = TRUE;
        echo json_encode($response);
    } 

    else {
            // user failed to store
            $response["error"] = TRUE;
            $response["error_msg"] = "Updation Unsuccessful!";
            echo json_encode($response);
        }
    
    }
else {
    $response["error"] = TRUE;
    $response["error_msg"] = "Required parameters are missing!";
    echo json_encode($response);
}
?>
