<?php
 
require_once 'include/DB_Functions.php';
$db = new DB_Functions();
 
// json response array
$response = array("error" => FALSE);
 
if (isset($_GET['seat_no'])) {
 
    // receiving the post params
    $seat_no= $_GET['seat_no'];
    
    // check if user is already existed with the same email
    if ($db->isUserExisted($seat_no)) {
        // user already existed
        $response["error"] = TRUE;
        $response["error_msg"] = "Passenger is already registered with seat number " . $seat_no;
        echo json_encode($response);
    } 
   
    else {
        // create a new user
        $user = $db->storeUser($seat_no);
        if ($user) {
            // user stored successfully
            $response["error"] = FALSE;
            $response["seat_no"] = $user["seat_no"];
            $response["name"] = $user["name"];
            
            echo json_encode($response);
        } else {
            // user failed to store
            $response["error"] = TRUE;
            $response["error_msg"] = "Unknown error occurred in registration!";
            echo json_encode($response);
        }
    }
}else {
    $response["error"] = TRUE;
    $response["error_msg"] = "Required parameters seat_no is missing!";
    echo json_encode($response);
}
?>
