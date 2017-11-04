<?php


class DbHandler {

    private $conn;

    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
	}
	
	function user_Like($user_id) {
		$response = array();
		$stmt = $this->conn->prepare("INSERT INTO user_like(user_id) values('$user_id')");
			$result = $stmt->execute();

            $stmt->close();
			 if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
			
                // Failed to create user
                return USER_CREATE_FAILED;
            }
			 return $response;
    }
	
	function user_dislike($user_id) {
		$response = array();
		$stmt = $this->conn->prepare("INSERT INTO user_dislike(user_id) values('$user_id')");
			$result = $stmt->execute();

            $stmt->close();
			 if ($result) {
				
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
				
                // Failed to create user
                return USER_CREATE_FAILED;
            }
			 return $response;
    }
	
function upload_img($user_id, $fullpath) {
		$response = array();
		$stmt = $this->conn->prepare("INSERT INTO images(user_id, fullpath) values('$user_id','$fullpath')");
			$result = $stmt->execute();

            $stmt->close();
			 if ($result) {
				
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {

                // Failed to create user
                return USER_CREATE_FAILED;
            }
			 return $response;
    }
	
function download_img($user_id) {
	echo "$user_id";
		$stmt = $this->conn->prepare("SELECT user_id FROM images WHERE user_id= '$user_id'");
		
			$result = $stmt->execute();
			$response = array();
			while($row = $result->fetch()){
            array_push($response,$row['user_id']);
			}
			
			if(isset($response[0])) {
            shuffle($response);
            $image = file_get_contents("../images/$response[0]");
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $app->response->header('Content-Type', 'content-type: ' . $finfo->buffer($image));
            //$app->response->header('Content-Type', 'application/octet-stream');
            $app->response->header('Content-Disposition','attachment; filename='.$response[0]);
            //$app->response->headers->set('Content-Transfer-Encoding', 'binary');
            $app->response->headers->set('Content-Length', filesize("../images/$response[0]"));
            $app->response->setBody($image);
        } else {
            $app->response()->header("Content-Type", "application/json");
            $result = array('success' => '1',
                'code' => '200',
                'message'=> 'No Image found');
             echoRespnse(201, $response);
            return;
        }
			 if ($result) {
				
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {

                // Failed to create user
                return USER_CREATE_FAILED;
            }
			 return $response;
    }
	
	function commentImg($user_id, $comment) {
		$response = array();
		$stmt = $this->conn->prepare("INSERT INTO comment(user_id, comments) values('$user_id', '$comment')");
			$result = $stmt->execute();

            $stmt->close();
			 if ($result) {

                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
				
                // Failed to create user
                return USER_CREATE_FAILED;
            }
			 return $response;
    }
	

	
	 function deleteComment($user_id) {
		 
		 $response = array();
		 
        $stmt = $this->conn->prepare("DELETE FROM comment WHERE user_id = '$user_id'");
      $result = $stmt->execute();

       // $stmt->execute();
       // $num_affected_rows = $result->affected_rows;
        $stmt->close();
        //return $num_affected_rows > 0;
		if ($result) {
				
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
				
                // Failed to create user
                return USER_CREATE_FAILED;
            }
			 return $response;
    }
    
	
	public function getAllUserTasks($page) {
		
		$perpage = 5;
		$calc = $perpage * $page;
		$start = $calc - $perpage;
		
        $stmt = $this->conn->prepare("select * from post Limit ?, ?");
        $stmt->bind_param("ii", $start, $perpage);
        $stmt->execute();
	   $tasks = $stmt->get_result();
				
	
		$stmt->close();
        return $tasks;
	}
	  // $rows = mysqli_num_rows($result);

		/*	if($rows){
			$i = 0;
			while($post = mysqli_fetch_assoc($result)) {
				 echo "$post['title']";
				 echo "$post['detail']";
			}
			}
        $stmt->close();
        return $tasks;
    }
	function page_count($page ) {
		echo "hey";
		$response = array();
		$perpage = 5;
			
				$calc = $perpage * $page;
				$start = $calc - $perpage;
				echo "hii";
		$stmt = $this->conn->("select * from post Limit $start, $perpage");
			$result = $stmt->execute();

			$rows = mysqli_num_rows($result);

			if($rows){
			$i = 0;
			while($post = mysqli_fetch_assoc($result)) {
				 echo "$post['title']";
				 echo "$post['detail']";
			}
			}

			
			 if ($result) {

                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
				
                // Failed to create user
                return USER_CREATE_FAILED;
            }
			 return $response;
    
	}*/
	
	
     function createUser($name, $email, $code, $phone, $password) {
        require_once 'PassHash.php';
        $response = array();

        // First check if user already existed in db
        if (!$this->isUserExists($email)) {
            // Generating password hash
            $password_hash = PassHash::hash($password);

			 // insert query
            $stmt = $this->conn->prepare("INSERT INTO users(name, email, code, phone, password_hash) values(?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiis", $name, $email, $code, $phone, $password);

            $result = $stmt->execute();

            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return USER_ALREADY_EXISTED;
        }

        return $response;
    }

	
     function checkLogin($email,$password) {
        // fetching user by email
		echo "check";
        $stmt = $this->conn->prepare("SELECT password_hash FROM users WHERE email = ? AND password_hash=? ");

        $stmt->bind_param("ss", $email,$password);

        $stmt->execute();

        $stmt->bind_result($password_hash);

        $stmt->store_result();

       if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password
            $stmt->fetch();

            $stmt->close();

                // User password is correct
                return TRUE;

        } else {
            $stmt->close();

            // user not existed with the email
            return FALSE;
        }
    }
 function getTask($email,$password,$new_password) {
	
     // fetching user by email
        $stmt = $this->conn->prepare("SELECT password_hash FROM users WHERE email = ? AND password_hash=? ");

        $stmt->bind_param("ss", $email,$password);

        $stmt->execute();

        $stmt->bind_result($password_hash);

        $stmt->store_result();
       if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password
			
            $stmt->fetch();

            $stmt->close();
			  $db = new DbConnect();
        $this->conn = $db->connect();
        $stmt = $this->conn->prepare("UPDATE users SET password_hash=? WHERE email=?");
        $stmt->bind_param("ss",$new_password,$email);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
		
        $stmt->close();
		if($num_affected_rows>0){
			return true;
		}else{
			return false;
		}
				

        } else {
            $stmt->close();

            // user not existed with the email
            return FALSE;
        }
		}
		
		
 function updatePasswords($email,$new_password){
		
			echo $email.$new_password;
        $stmt = $this->conn->prepare("UPDATE password_hash=? WHERE email = ?");
        $stmt->bind_param("ss",$new_password, $email);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
		
        $stmt->close();
		if(num_affected_rows>0){
			return true;
		}else{
			return false;
		}
    }
    /**
     * Updating task
     */
    
    
     function isUserExists($email) {
        $stmt = $this->conn->prepare("SELECT password_hash from users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

     function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT name, email, code, phone FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($name, $email, $code, $phone);
            $stmt->fetch();
            $user = array();
            $user["name"] = $name;
            $user["email"] = $email;
			$user["code"] = $code;
			$user["phone"] = $phone;
            
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }
	 
}

	
?>