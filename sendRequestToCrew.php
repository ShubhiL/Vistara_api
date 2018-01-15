<?php
 
require_once 'include/DB_Functions.php';
$db = new DB_Functions();
 
// json response array
$response = array("error" => FALSE);
 
if (isset($_POST['seat_no']) && isset($_POST['request_made'])) {
 
    // receiving the post params
    $seat_no= $_POST['seat_no'];
    $request_made = $_POST['request_made'];

        // create a new user
        $user = $db->storeRequest($seat_no, $request_made);
        if ($user) {
            // user stored successfully
            $response["error"] = FALSE;
            $response["seat_no"] = $seat_no;
            echo json_encode($response);
        } else {
            // user failed to store
            $response["error"] = TRUE;
            $response["error_msg"] = "Unknown error occurred in making request";
            echo json_encode($response);
        }
    
}else {
    $response["error"] = TRUE;
    $response["error_msg"] = "Required parameters (seat_no or request_made) is missing!";
    echo json_encode($response);
}
?>
