<?php

require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

function authenticate(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
}
	

$app->post('/signup', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('name', 'email', 'code', 'phone', 'password'));

            $response = array();

            // reading post params
            $name = $app->request->post('name');
            $email = $app->request->post('email');
			$code = $app->request->post('code');
			$phone = $app->request->post('phone');
            $password = $app->request->post('password');

            // validating email address
            validateEmail($email);

            $db = new DbHandler();
			
            $res = $db->createUser($name, $email, $code, $phone, $password);
            if ($res == USER_CREATED_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "You are successfully registered";
            } else if ($res == USER_CREATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Oops! An error occurred while registereing";
            } else if ($res == USER_ALREADY_EXISTED) {
                $response["error"] = true;
                $response["message"] = "Sorry, this email already existed";
            }
            // echo json response
            echoRespnse(201, $response);
        });
		
		
$app->post('/comment', function() use ($app) {
			verifyRequiredParams(array('user_id','comment'));

            $response = array();

            // reading post params
            $user_id = $app->request->post('user_id');
			$comment = $app->request->post('comment');
			
			$db = new DbHandler();
			
			$res = $db->commentImg($user_id, $comment);
            if ($res == USER_CREATED_SUCCESSFULLY) {  
				echo "The time is " . date("h:i:sa");
				echo "commented on " . date("d/m/y");
                $response["error"] = false;
                $response["message"] = "commented successfully";
            } else if ($res == USER_CREATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Oops! An error occurred while commenting";
            } 
            // echo json response
            echoRespnse(201, $response);
        });
		

$app->post("/uploaded", function() use($app) {
	
		verifyRequiredParams(array('user_id'));

            $response = array();

            // reading post params
            $user_id = $app->request->post('user_id');
		
		$app->response()->header("Content-Type", "application/json");
		$req = $app->request()->post();
	$db = new DbHandler();
 
    if ((!isset($_FILES['file']))) {
        $result = array('success' => '0',
            'code' => '500',
            'message'=> 'Error: No files uploaded');
        echoRespnse(500,json_encode($result));
        return;
		
    }else {
		 $userId = $req["user_id"];
        $tmp_name = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];

        if (!file_exists("../images/$userId")) {
            mkdir("../images/$userId", 0777, true);
        }

        $fullFileName =  strtotime("now").'_'.$name;
		
        $saved = move_uploaded_file($tmp_name, "../images/$userId/$fullFileName");
		$fullpath= "/images/$userId/$fullFileName";
		 $imageUrl = "http://$_SERVER[HTTP_HOST]/images/$userId/$fullpath";
		 echo "$imageUrl";
		 
      $res = $db->upload_img($user_id, $fullpath);
            if ($res == USER_CREATED_SUCCESSFULLY) {
				echo "uploaded on " . date("d/m/y") . "<br>";
                $response["error"] = false;
                $response["message"] = "Image Uploaded successfully";
            } else if ($res == USER_CREATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Oops! An error occurred while Updating";
            } 
            // echo json response
            echoRespnse(201, $response);
	}
        });
		
$app->get('/tasks', 'authenticate', function() {
	
            // $page=1;
			 if(isset($_GET["page"])){
			 // if ($page==1){
				$page = intval($_GET["page"]);
			}
			else {
				$page = 1;
			}
	
            $response = array();
            $db = new DbHandler();
			

            // fetching all user tasks
            $result = $db->getAllUserTasks($page);

            $response["error"] = false;
            $response["tasks"] = array();
			
            // looping through result and preparing tasks array
            while ($task = $result->fetch_assoc()) {
				
                $tmp = array();
                $tmp["id"] = $task["id"];
                $tmp["title"] = $task["title"];
                $tmp["detail"] = $task["detail"];
                array_push($response["tasks"], $tmp);
            }

            echoRespnse(200, $response);
			
	/*else($page>1)
{
	
$result = mysqli_query($conn,"select Count(*) As Total from post");
$rows = mysqli_num_rows($result);
if($rows)
{
	
$rs = mysqli_fetch_assoc($result);
$total = $rs["Total"];
}
$totalPages = ceil($total / $perpage);

if($page <=1 ){
echo "Prev";
}

else
{
$j = $page - 1;
echo "$j.Prev";
}

for($i=1; $i <= $totalPages; $i++)
{
if($i<>$page)
{
echo "$i";
}

else
{
echo "$i";
}

}

if($page == $totalPages )
{
echo "Next";
}

else
{
$j = $page + 1;
echo "$j.Next";
}

}*/
        });
		
		
$app->get("/download/:user_id" , function($user_id) use($app) {
	verifyRequiredParams(array('user_id'));

            $response = array();

            // reading post params
            $user_id = $app->request->post('user_id');
		
		$app->response()->header("Content-Type", "application/json");
		$req = $app->request()->post();
	    
		$db = new DbHandler();
		
        $res = $db->download_img($user_id);
            if ($res == USER_CREATED_SUCCESSFULLY) {
				echo "downloaded on " . date("d/m/y") . "<br>";
                $response["error"] = false;
                $response["message"] = "Image download successfully";
            } else if ($res == USER_CREATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Oops! An error occurred while downloading";
            } 
            // echo json response
            echoRespnse(201, $response);
		
		
});


     
$app->post('/login', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('email','password'));

            // reading post params
            $email = $app->request()->post('email');
            $password = $app->request()->post('password');
            $response = array();

            $db = new DbHandler();
			
            // check for correct email and password
            if ($db->checkLogin($email, $password)) {
                // get the user by email
                $user = $db->getUserByEmail($email);

                if ($user != NULL) {
                    $response["error"] = false;
                    $response['name'] = $user['name'];
                    $response['email'] = $user['email'];
                 
                } else {
                                                                                                                                                                                                                     // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            } else {
                // user credentials are wrong
                $response['error'] = true;
                $response['message'] = 'Login failed. Incorrect credentials';
            }

            echoRespnse(200, $response);
        });
		
		

	
	
$app->post('/like', function() use ($app)  {
	verifyRequiredParams(array('user_id'));
	 $response = array();

            // reading post params
            $user_id = $app->request->post('user_id');
        
    $user = $app->request()->post();
    $result = array();
	
    if(isset($user["user_id"])) {
            $data = array(
                "user_id" => $user["user_id"]);

			$db = new DbHandler();
			 $res = $db->user_Like($user_id);
            if ($res == USER_CREATED_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "image liked";
            } else if ($res == USER_CREATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Oops! An error occurred while liking";
            } 
            // echo json response
            echoRespnse(201, $response);
		}
	
        });
           

	$app->post('/dislike', function() use ($app)  {
	verifyRequiredParams(array('user_id'));
	 $response = array();

            // reading post params
            $user_id = $app->request->post('user_id');
            
    $user = $app->request()->post();
    $result = array();
	
    if(isset($user["user_id"])) {
        
            $data = array(
                "user_id" => $user["user_id"]);
               
			$db = new DbHandler();
			
			  $res = $db->user_dislike($user_id);
            if ($res == USER_CREATED_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "Image Disliked";
            } else if ($res == USER_CREATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Oops! An error occurred while Disliking";
            } 
            // echo json response
            echoRespnse(201, $response);
		}
	
        });
		
		
	$app->delete('/delete/:id',  function($user_id) use($app) {
		
	 $response = array();

            // reading post params
            $user_id = $app->request->post('user_id');
          //  $result = array();
			$db = new DbHandler();
            $response = array();
			
			  $res = $db->deleteComment($user_id);
            if ($res == USER_CREATED_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "Comment deleted successfully";
            } else if ($res == USER_CREATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Oops! An error occurred while Disliking";
            } 
            // echo json response
            echoRespnse(201, $response);
		
		
        });


// Listing single task of particual user
 
$app->put('/change',  function() use ($app) {
	verifyRequiredParams(array('email','password','new_password'));
           
            $response = array();
			

            $email = $app->request->put('email');
			$new_password = $app->request->put('new_password');
            $password = $app->request->put('password');
            $db = new DbHandler();

            // fetch task
            $result = $db->getTask($email,$password,$new_password);

             if ($result == TRUE) {
                $response["error"] = false;
                $response["message"] = "Updated Succesfully ";
            }else{
                $response["error"] = true;
                $response["message"] = "Error Occured";
            }
            // echo json response
            echoRespnse(201, $response);
        });

		/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }
    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Validating email address
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        echoRespnse(400, $response);
        $app->stop();
  }
}
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}


$app->run();
?>