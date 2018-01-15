 <?php
 
class DB_Functions {
 
    private $conn;
 
    // constructor
    function __construct() {
        require_once 'include/DB_Connect.php';
        // connecting to database
        $db = new Db_Connect();
        $this->conn = $db->connect();
    }
 
    // destructor
    function __destruct() {    
    }
 
    /**
     * Storing new user
     * returns user details
     */
    public function storeUser($seat_no) {
        
     //Fetching employee's details from MAIN table
        $stmt1 = $this->conn->prepare("SELECT * FROM main WHERE seat_no = ?");
        $stmt1->bind_param("s", $seat_no);
 
        if ($stmt1->execute()) {
            $data_main = $stmt1->get_result()->fetch_assoc();
            $stmt1->close();            
        }

        else
            echo "NO ENTRY FOUND";
 
     //Insert into USERS table
        $stmt = $this->conn->prepare("INSERT INTO users (seat_no, name) VALUES (?, ?)");
        $stmt->bind_param("ss", $data_main['seat_no'], $data_main['name']);
        $result = $stmt->execute();
        $stmt->close();
 
        // check for successful store
        if ($result) {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE seat_no = ?");
            $stmt->bind_param("s", $seat_no);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
 
            return $user;
        } 
        else {
             return false;
        }
    }
 
    /**
     * Get user by employee id and password (Used for login)
     */
    public function getUserByEmployeeIdAndPassword($employee_id, $password) {
 
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE employee_id = ?");
        $stmt->bind_param("s", $employee_id);
 
        if ($stmt->execute()) {
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
 
            // verifying user password
            $salt = $user['salt'];
            $encrypted_password = $user['encrypted_password'];
            $hash = $this->checkhashSSHA($salt, $password);

            // check for password equality
            if ($encrypted_password == $hash) {
                // user authentication details are correct
                return $user;
            }
        } else {
            return NULL;
        }
    }
 
    /**
     * Check user is existed or not (Used in Registration)
     */
    public function isUserExisted($seat_no) {
        $stmt = $this->conn->prepare("SELECT name from users WHERE seat_no = ?");
        $stmt->bind_param("s", $seat_no);
        $stmt->execute();
        $stmt->store_result();
 
        if ($stmt->num_rows > 0) {
            // user existed 
            $stmt->close();
            return true;
        } else {
            // user not existed
            $stmt->close();
            return false;
        }
    }
 
    /**
     * Encrypting password
     * @param password
     * returns salt and encrypted password
     */
    public function hashSSHA($password) {
 
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }
 
    /**
     * Decrypting password
     * @param salt, password
     * returns hash string
     */
    public function checkhashSSHA($salt, $password) {
 
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
        return $hash;
    }

    // Verifying employee 
    public function isEmployeeVerified($employee_id, $email, $unique_id, $created_at, $updated_at = NULL){

        if ($updated_at!=NULL){

        $stmt = $this->conn->prepare("SELECT * from users WHERE email = ? AND employee_id = ? AND unique_id = ? AND created_at = ? AND updated_at = ? AND active='0'");
        $stmt->bind_param("sssss", $email, $employee_id, $unique_id, $created_at, $updated_at);
    }
    else{

        $stmt = $this->conn->prepare("SELECT * from users WHERE email = ? AND employee_id = ? AND unique_id = ? AND created_at = ? AND active='0'");
        $stmt->bind_param("ssss", $email, $employee_id, $unique_id, $created_at);
    }
 
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
            
        // user existed 
        $stmt = $this->conn->prepare("UPDATE users SET active = '1' WHERE employee_id = ?");
        $stmt->bind_param("s", $employee_id);
        $result = $stmt->execute();
        $stmt->close();   
        return true;
        } 

    else {
            // user not existed
            $stmt->close();
            return false;
        }
    }

    //Fetching data of user
    public function dataFetch($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users where email = ?");
        $stmt->bind_param("s", $email);
 
        if ($stmt->execute()) {
            $data = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $data;
        } else {
            $stmt->close();
            return NULL;
        }
    }

    //Fetching Drivers in same zone
    public function getSameZoneDrivers($employee_id){ 
        
        $stmt = $this->conn->prepare("SELECT zone_id FROM users where employee_id = ?");
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $data= $stmt->get_result()->fetch_assoc()["zone_id"];
        $stmt->close();     

        $stmt = $this->conn->prepare("SELECT employee_id, employee_name, address, designation, owns_vehicle, mobile_number, gender FROM users where zone_id= ? AND owns_vehicle = 1 AND employee_id != ? AND visibility_as_driver = 1");
        $stmt->bind_param("ss", $data, $employee_id);
 
        if ($stmt->execute()) {

                $data = $stmt->get_result()->fetch_all();
                //print_r($data);                         
                $stmt->close();
                return $data;
        }              
        else
            return false;
    }   

    //Fetching people in same zone
    public function getSameZonePeople($employee_id){ 

        $stmt = $this->conn->prepare("SELECT zone_id FROM users where employee_id = ?");
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $data= $stmt->get_result()->fetch_assoc()["zone_id"];
        $stmt->close();
        
        $stmt = $this->conn->prepare("SELECT employee_id, employee_name, address, designation, owns_vehicle, mobile_number, gender FROM users where zone_id= ? AND employee_id != ? AND visibility_as_passenger = 1");
        $stmt->bind_param("ss", $data, $employee_id);
 
        if ($stmt->execute()) {

                $data = $stmt->get_result()->fetch_all();
                $stmt->close();
                return $data;
        }
                       
        else
            return false;
    }
                              
    public function storeRequest($seat_no, $request_made){

        $stmt = $this->conn->prepare("INSERT INTO request (seat_no, request_made) VALUES(?, ?)");
        $stmt->bind_param("ss", $seat_no, $request_made);
        $result = $stmt->execute();
        $stmt->close();
 
        // check for successful store
        if ($result) {
            $stmt = $this->conn->prepare("SELECT * FROM request WHERE seat_no = ? AND request_made = ?");
            $stmt->bind_param("ss", $seat_no, $request_made);
            $stmt->execute();
            $request_row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if($request_row){
             return true;
            }else{
             return false;
            }
        } else {
            return false;
        }
    }   

    public function fetchFoodContent (){
        $stmt = $this->conn->prepare("SELECT * FROM food WHERE food_state = 1 ");
        
        if($stmt->execute()){

            $accepted_rows = $stmt->get_result()->fetch_all();
            $stmt->close();
            //return $accepted_rows;
        }
        if($accepted_rows){
            
        return $accepted_rows;
    } else{
        return false;
    }
    }

    public function fetchRequestContent (){
        $stmt = $this->conn->prepare("SELECT * FROM request WHERE request_state = 0 ");
        
        if($stmt->execute()){

            $accepted_rows = $stmt->get_result()->fetch_all();
            $stmt->close();
            //return $accepted_rows;
        }
        if($accepted_rows){
            
        return $accepted_rows;
    } else{
        return false;
    }
    }


    public function getAllReceivedRequest($employee_id) {

        $stmt = $this->conn->prepare("SELECT driver_id , passenger_id FROM request WHERE (driver_id = ? OR passenger_id = ?) AND (sent_by != ?) AND status = 0 ");
        $stmt->bind_param("sss", $employee_id, $employee_id, $employee_id);
 
        if ($stmt->execute()) {

                $received_rows = $stmt->get_result()->fetch_all();
                $stmt->close();
        }
     
     //This also tells the index that we need 
        if($received_rows){
            if ($received_rows[0][0]== $employee_id)
            $is_driver = true;
        else 
            $is_driver = false;

        $size = count($received_rows);
        $a=array();

        for ($i= 0; $i<$size; $i++){

            $this->fetchFromMain($received_rows[$i][$is_driver]);
            array_push($a,$this->fetchFromMain($received_rows[$i][$is_driver]));
        }
        return $a;
       } else{
            return false;
       }  
    }
    
    public function getCurrentRideData($driver_id,$route_id)
    {
    $stmt = $this->conn->prepare("SELECT driver_id, current_loc_lat , current_loc_long , destination_lat , destination_long FROM route WHERE driver_id= ? AND route_id= ?");
        $stmt->bind_param("ss", $driver_id , $route_id);
 
        if ($stmt->execute()) {

                $data = $stmt->get_result()->fetch_all();
               // print_r($data);                         
                $stmt->close();
                return $data;
        }
                       
        else
            return false;
    }
    
    public function fetchFromMain($employee_id){

        $stmt1 = $this->conn->prepare("SELECT * FROM main WHERE employee_id = ?");
        $stmt1->bind_param("s", $employee_id);
 
        if ($stmt1->execute()) {
            $data_main = $stmt1->get_result()->fetch_assoc();
            $stmt1->close();
            //print_r($data_main);
            return $data_main;
        }

        else
            return false;

    }

    public function acceptedRequests($employee_id)
    {
        $stmt = $this->conn->prepare("SELECT driver_id, passenger_id FROM request WHERE (driver_id = ? OR passenger_id = ? ) AND (status = 1) ");
        $stmt->bind_param("ss", $employee_id, $employee_id);

        if($stmt->execute()){

            $accepted_rows = $stmt->get_result()->fetch_all();
            $stmt->close();
            //return $accepted_rows;
        }
        if($accepted_rows){
            if ($accepted_rows[0][0]== $employee_id)
            $is_driver = true;
        else 
            $is_driver = false;

        //This also tells the index that I need 

        //echo $is_driver;

        $size = count($accepted_rows);
        $a=array();

        for ($i= 0; $i<$size; $i++){

            $this->fetchFromMain($accepted_rows[$i][$is_driver]);
            array_push($a,$this->fetchFromMain($accepted_rows[$i][$is_driver]));

        }

        return $a;
    } else{
        return false;
    }
        

    }

    public function sentRequests($employee_id)
    {
        $stmt = $this->conn->prepare("SELECT driver_id, passenger_id FROM request WHERE sent_by = ? AND status = 0 ");
        $stmt->bind_param("s", $employee_id);

        if($stmt->execute()){

            $sent_rows = $stmt->get_result()->fetch_all();
            //print_r($sent_rows);
            $stmt->close();
            //return $accepted_rows;
        }

        if($sent_rows){
           if ($sent_rows[0][0]== $employee_id)
            //show passenger details
            $is_driver = true;
        else 
            //show driver details
            $is_driver = false;

        $size = count($sent_rows);
        $a=array();

        for ($i= 0; $i<$size; $i++){
            $this->fetchFromMain($sent_rows[$i][$is_driver]);
            array_push($a,$this->fetchFromMain($sent_rows[$i][$is_driver]));
        }

        return $a; 
       }
        else return null;
    }

    public function storeInRouteTable($driver_id, $string_route, $d_latitude, $d_longitude)
    {
        //CHECKING IF ENTRY ALREADY EXISTS
        $stmt = $this->conn->prepare("SELECT * from route WHERE driver_id = ?");
        $stmt->bind_param("s", $driver_id);
        $stmt->execute();
        $stmt->store_result();
 
        if ($stmt->num_rows > 0) {
            // user existed 
            $stmt = $this->conn->prepare("UPDATE route SET route = ? WHERE driver_id = ?");
            $stmt->bind_param("ss", $string_route, $driver_id);

        } else {
            // user not existed
            $stmt = $this->conn->prepare("INSERT INTO route (driver_id, route, current_latitude, current_longitude, d_latitude, d_longitude) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdddd", $driver_id, $string_route, $d_latitude, $d_longitude, $d_latitude, $d_longitude);
        }

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return true;
        } 
        else {
            return false;
        }
    }

    public function fetchOnePassengerFromRoute($driver_id)
    {
        $stmt = $this->conn->prepare("SELECT route FROM route WHERE driver_id = ?");
        $stmt->bind_param("s", $driver_id);

        if($stmt->execute()){
            $routeFetched = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            }

        if($routeFetched){
           $flag = 0;
           $passenger_list = explode(",", $routeFetched['route']);
           //print_r($passenger_list);

           for($i = 0; $i<count($passenger_list); $i++){
            //print_r($passenger_list[$i]);
            $stmt1 = $this->conn->prepare("SELECT status FROM request WHERE passenger_id = ?");
            $stmt1->bind_param("s", $passenger_list[$i]);

            if($stmt1->execute()){
                $status = $stmt1->get_result()->fetch_assoc();
                //print_r($status);
                //print_r($status['status']);
                $stmt1->close();
             
                if($status['status']==1 || $passenger_list[$i]=='shopclues'){
                    $flag = 1;
                    //print_r($passenger_list);
                    return $passenger_list[$i];
                }
            }
        else 
            return false;
        }
        if ($flag == 0){
            return false;
        }
        }

        else{
            return false;
        }
      }


    public function acceptRequest($driver_id,$passenger_id) {
    
    $stmt = $this->conn->prepare("UPDATE request SET status = 1 WHERE ( passenger_id = ? OR driver_id = ? ) OR ( driver_id = ? OR passenger_id = ?)");
        $stmt->bind_param("ssss", $employee_id, $requestee_id, $employee_id, $requestee_id);
        if ($stmt->execute()) 
        {
           $stmt->store_result();
             $stmt->close();
             return true;
             }
        else
             return false;
    
    }
 
    public function cancelRequest($sender_id,$receiver_id) {
    
    $stmt = $this->conn->prepare("UPDATE request SET status = -1 WHERE driver_id = ? AND passenger_id = ?");
     $stmt->bind_param("ss", $driver_id, $passenger_id);
      if ($stmt->execute()) 
      {
       $stmt->store_result();
         $stmt->close();
         return true;
         }
         else
         return false;
    
    }

    public function undoSentRequest($employee_id, $requestee_id)
    {
        $stmt = $this->conn->prepare("UPDATE request SET status = -1 WHERE sent_by = ? AND status = 0 AND ( passenger_id = ? OR driver_id = ? )");
        $stmt->bind_param("sss", $employee_id, $requestee_id, $requestee_id);
        if ($stmt->execute()) 
        {
           $stmt->store_result();
             $stmt->close();
             return true;
             }
        else
             return false;
    }

    public function updateDriverLatLong($driver_id,$latitude, $longitude)
    {
        $stmt = $this->conn->prepare("UPDATE route SET d_latitude = ?, d_longitude = ? WHERE driver_id = ?");
        $stmt->bind_param("dds", $latitude, $longitude, $driver_id);
      if ($stmt->execute()) 
      {
       $stmt->store_result();
         $stmt->close();
         return true;
         }
         else
         return false;
    }

    public function updateLatLong($driver_id, $current_lat, $current_long)
     {
     $stmt = $this->conn->prepare("UPDATE route SET current_latitude = ? , current_longitude = ? WHERE driver_id = ?");
     $stmt->bind_param("dds", $current_lat, $current_long, $driver_id);
      if ($stmt->execute()) 
      {
       $stmt->store_result();
        $stmt->close();
         return true;
         }
        else
        {
         $stmt->close();
         return false;
     }
     }

    public function fetchFromRoute($driver_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM route WHERE driver_id = ?");
     $stmt->bind_param("s", $driver_id);
      if ($stmt->execute()) 
      {
       $result = $stmt->get_result()->fetch_assoc();
         $stmt->close();
         return $result;
         }
         else
         return false;
    }

    public function updateAccess($employee_id, $access_as)
    {
        $stmt = $this->conn->prepare("UPDATE users SET access_as = ? WHERE employee_id = ?");
        $stmt->bind_param("ds", $access_as, $employee_id);
      if ($stmt->execute()) 
      {
       $stmt->store_result();
         $stmt->close();
         return true;
         }
         else
         return false;
    }

    

    public function updateStatus($request_id, $status){


                $stmt = $this->conn->prepare("UPDATE request SET request_state = ? WHERE request_id = ? ");
                $stmt->bind_param("ds", $status, $request_id);
                if ($stmt->execute()) 
                {
              //$stmt->store_result();
                    $stmt->close();
                 //print_r($check);
                    return true;
                 }
            else
                 return false; 
    }

    public function fetchFromRequest($driver_id, $passenger_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM request WHERE driver_id = ? AND passenger_id = ?");
        $stmt->bind_param("ss", $driver_id, $passenger_id);
        if ($stmt->execute()) 
        {
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $result;
         }
         else
            return false;
    }

    public function fetchFromUsersAccessType($employee_id)
    {
        $stmt = $this->conn->prepare("SELECT access_as FROM users WHERE employee_id = ?");
        $stmt->bind_param("s", $employee_id);
        if ($stmt->execute()) 
        {
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $type = $result['access_as'];
            
            if($type == 1){
                //access_as = 1 => DRIVER
                return ("driver");
            }elseif($type == 0){
            //access_as = 0 => PASSENGER
                return ("passenger");   
            }
            
         }
         else
            return false;
    }

    public function test($passenger_id)
    {
        $stmt = $this->conn->prepare("SELECT driver_id, route FROM route");

        $flag = 0;
        $flag1 = 0;

        if ($stmt->execute()) 
        {
            $result = $stmt->get_result()->fetch_all();
            $stmt->close();
            //print_r($result);

            foreach ($result as $value) {
              
                 $route_array = explode(',', $value[1]);
                print_r($route_array);
                 foreach ($route_array as $pass_id) {
                    // echo("\nThis is each pass_id\n");
                    print_r($pass_id);
                     if($pass_id === $passenger_id){
                  echo "\nTHE MATCHING PASSENGER ID \n".$pass_id;
                        $flag = 1;
                        return $value[0];
                        break;
                     }
                 }
                 if($flag==0){
                    return false;
                 }
                 if($flag == 1){
                    $flag1 = 1;
                    break;
                 }
             }

             if($flag1==0){
                return false;
             }
         }
         else
            return false;
    }

    public function getDriverIdFromRoutesTable($passenger_id){
        $stmt = $this->conn->prepare("SELECT driver_id, route FROM route");
        if($stmt->execute()){
            $result = $stmt->get_result()->fetch_all();
            $stmt->close();
            //print_r($result);

            foreach ($result as $value) {
                // print_r($value[1]);
                 $route_array = explode(',', $value[1]);
                //print_r($route_array);
                 foreach ($route_array as $pass_id) {
                    //print_r($pass_id);
                     if($pass_id === $passenger_id){
                        return $value[0];
                        break;
                     }
                 }
             }
        }
        else{
            return false;
        }
    }
}
 
?>
