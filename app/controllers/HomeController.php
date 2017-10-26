<?php


//Author:    sathish.k
//E-mail:    ksathish0014@gmail.com
//Team  :    MyChat 

class HomeController extends BaseController {
	/*
	|--------------------------------------------------------------------------
	| Default Group Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|
	*/

	public $responseContents = null;
	public $responseStatusCode = null;

	
	// register user
	public function registerUser(){
	    try{
			$requestData = Input::all();
			if(!empty($requestData) && !empty($requestData["firstName"]) && !empty($requestData["lastName"]) && !empty($requestData["phoneNo"])&& !empty($requestData["gcmRegId"]))
			{
			   	DB::beginTransaction();
			   	$checkUser = DB::table('users')->where("phone_no","=",$requestData["phoneNo"])->get();
			   	if(count($checkUser)<=0){
			   		$sid=$this->getSid();
			   		$otpId=uniqid();
			   		$otp=substr($otpId, 0, 6);
				    DB::table('users')->insertGetId(array("first_name"=>$requestData["firstName"],"last_name"=>$requestData["lastName"],"phone_no"=>$requestData["phoneNo"],"password"=>$this->encrypt($requestData["password"]),"otp"=>$otp,"account_status"=>"inactive","email"=>$requestData["email"],"gcm_id"=>$requestData["gcmRegId"],"created_on"=>$this->NOW(),"sid"=>$sid));
				    DB::commit();
				    $userInfo = DB::table('users')->where("sid","=",$sid)->get();
				    if(count($userInfo)>0)
				    {
						$this->httpResponse["content"] =(int)$userInfo[0]->id;
						$this->httpResponse["statusCode"] = 200;
				    }
				    else
				    {
					$this->httpResponse["content"] =  array("errorMessage"=>"User not created,Please contact admin!");
					$this->httpResponse["statusCode"] = 417;
				    }
			   	}else{
			   		$this->httpResponse["content"] =  array("errorMessage"=>"User already exists");
					$this->httpResponse["statusCode"] = 409;
			   	}
			    
			}
			else
			{
			    $this->httpResponse["content"] =  array("errorMessage"=>"Required fieled empty ");
				$this->httpResponse["statusCode"] = 406;
			}
	    }
	    
	    catch(Exception $e)
	    {
			DB::rollback();
			$this->httpResponse["content"] = array("errorMessage"=>"Internal error", "developerMessage"=>$e->getMessage());
			$this->httpResponse["statusCode"] = 500;
	    }
	    return Response::make(json_encode($this->httpResponse["content"]), $this->httpResponse["statusCode"]);
	}
	
	

	// register user
	public function signInUser(){
	    try{
			$requestData = Input::all();
			if(!empty($requestData["email"])  && !empty($requestData["password"]))
			{
			   	DB::beginTransaction();
			    $userInfo = DB::table('users')->where("email","=",$requestData["email"])->where("password","=",$this->encrypt($requestData["password"]))
			    ->select("id as userId","sid as userSid","first_name as firstName","last_name as lastName","gcm_id as gcmRegId","email as email" )->get();
			    if(count($userInfo)>0)
			    {
					$this->httpResponse["content"] =$userInfo[0];
					$this->httpResponse["statusCode"] = 200;
			    }
			    else
			    {
				$this->httpResponse["content"] =  array("errorMessage"=>"User not created,Please contact admin!");
				$this->httpResponse["statusCode"] = 417;
			    }
			}
			else
			{
			    $this->httpResponse["content"] =  array("errorMessage"=>"Required fieled empty ");
				$this->httpResponse["statusCode"] = 406;
			}
	    }
	    
	    catch(Exception $e)
	    {
			DB::rollback();
			$this->httpResponse["content"] = array("errorMessage"=>"Internal error", "developerMessage"=>$e->getMessage());
			$this->httpResponse["statusCode"] = 500;
	    }
	    return Response::make(json_encode($this->httpResponse["content"]), $this->httpResponse["statusCode"]);
	}
	
	


//get all users	

	public function getAllUser(){
		try{
			$userInfo = DB::table('users as u')
						->select('u.first_name as firstName','u.last_name as lastName','u.profile_image_path as image','u.sid as userSid','u.gcm_id as gcmRegId','u.phone_no as phoneNo')->where("u.account_status","active")->get();
			if(count($userInfo)<=0){
				$this->httpResponse["content"] = array("errorMessage"=>"No user found", "developerMessage"=>"NO_DATA_FOUND");
				$this->httpResponse["statusCode"] = 417;
			}else{
				$this->httpResponse["content"] = array("successMessage"=>$userInfo, "developerMessage"=>"ALL_USER_LIST");
				$this->httpResponse["statusCode"] = 200;
			}
		}
	    
	    catch(Exception $e)
	    {
			//DB::rollback();
			$this->httpResponse["content"] = array("errorMessage"=>"Internal error", "developerMessage"=>$e->getMessage());
			$this->httpResponse["statusCode"] = 500;
	    }
	    return Response::make(json_encode($this->httpResponse["content"]), $this->httpResponse["statusCode"]);

	}


	// register user
	public function sendNotification(){
	    try{
			$requestData = Input::all();
			if(!empty($requestData) && !empty($requestData["phoneNo"]) && !empty($requestData["msg"]))
			{
				if($requestData["msg"]['type']){
					if($requestData["msg"]['content']){
					    $userInfo = DB::table('users')->where("phone_no","=",$requestData["phoneNo"])->groupby('phone_no')->distinct('phone_no')->get();
					    if(count($userInfo)>0)
					    {
					    	
						    // Replace with the real server API key from Google APIs
						    $apiKey = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

						    // Replace with the real client registration IDs
						    $registrationIDs = $userInfo[0]->gcm_id;
						    $registrationIDs;
						    // Message to be sent
						    $message =$requestData["msg"];
						//echo $message;
						    // Set POST variables
						    $url = 'https://android.googleapis.com/gcm/send';

						    $fields = array(
						        'registration_ids' =>array($registrationIDs),
						        'data' => array("message"=>$message)
						    );

						    //return $fields;
						    $headers = array(
						        'Authorization: key=' . $apiKey,
						        'Content-Type: application/json'
						    );

						    // Open connection
						    $ch = curl_init();

						    // Set the URL, number of POST vars, POST data
						    curl_setopt( $ch, CURLOPT_URL, $url);
						    curl_setopt( $ch, CURLOPT_POST, true);
						    curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
						    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
						    //curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $fields));

						    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						    // curl_setopt($ch, CURLOPT_POST, true);
						    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $fields));

						    // Execute post
						     $result = curl_exec($ch);

						    // Close connection
						    curl_close($ch);
						    $result=json_decode($result);
						    //return json_encode($result);
						    if($result->success==1){
						    	$this->httpResponse["content"] =array("messageID "=>$result->results[0]->message_id);
							$this->httpResponse["statusCode"] = 200;
						    }else{
						    	$this->httpResponse["content"] =array("errorMessage"=> $result->results[0]->error);
							$this->httpResponse["statusCode"] = 417;
						    }
							
					    }
					    else{
					    	$this->httpResponse["content"] =array("errorMessage"=> "User Not Found");
						$this->httpResponse["statusCode"] = 417;
					    }
				    }
				    else
				    {
					$this->httpResponse["content"] =  array("errorMessage"=>"Message should not be empty!");
					$this->httpResponse["statusCode"] = 417;
				    }
				}
			    else
			    {
				$this->httpResponse["content"] =  array("errorMessage"=>"Message type should not be empty!");
				$this->httpResponse["statusCode"] = 417;
			    }
			}
			else
			{
			    $this->httpResponse["content"] =  array("errorMessage"=>"Required fieled empty ");
				$this->httpResponse["statusCode"] = 406;
			}
	    }
	    
	    catch(Exception $e)
	    {
			DB::rollback();
			$this->httpResponse["content"] = array("errorMessage"=>"Internal error", "developerMessage"=>$e->getMessage());
			$this->httpResponse["statusCode"] = 500;
	    }
	    return Response::make(json_encode($this->httpResponse["content"]), $this->httpResponse["statusCode"]);
	}
	

	//Update Reg Id

	public function updateRegId(){
		try{
			$r = $_SERVER['REQUEST_URI']; 
			$r = explode('/', $r);
			$r = array_filter($r);
			$r = array_merge($r, array()); 
			$r = preg_replace('/\?.*/', '', $r);
			$phoneNo = $r[3];
			$regId= $r[4];

			if($phoneNo || $regId){
			 	$getUser=DB::table('users')->where("phone_no",$phoneNo)->get();
				if(count($getUser)>0){
					DB::table('users')->where("phone_no",$phoneNo)->update(array("gcm_id"=> $regId));
				 	$this->httpResponse["content"] = array("successMessage"=>"Register Id updated", "developerMessage"=>"success");
					$this->httpResponse["statusCode"] = 200;
				}else{
					$this->httpResponse["content"] = array("errorMessage"=>"Mobile number not registered", "developerMessage"=>"NOT_FOUND");
					$this->httpResponse["statusCode"] = 417;
				}
				 	
			}else{
				$this->httpResponse["content"] = array("errorMessage"=>"Phone number or register id empty", "developerMessage"=>"FIELD_EMPTY");
				$this->httpResponse["statusCode"] = 417;
			}
			

		}
	    catch(Exception $e)
	    {
			DB::rollback();
			$this->httpResponse["content"] = array("errorMessage"=>"Internal error", "developerMessage"=>$e->getMessage());
			$this->httpResponse["statusCode"] = 500;
	    }
	    return Response::make(json_encode($this->httpResponse["content"]), $this->httpResponse["statusCode"]);
	
	}

//file upload

public function uploadImage(){
	try{
		if($_SERVER['REQUEST_METHOD'] == "POST")
		{
			if(!empty($_FILES)){
				$bucketName='wisencrazy';
			$tempPath = $_FILES[ 'file' ][ 'tmp_name' ];
			$imageKey= strtotime(date('Y-m-d H:i:s')).$_FILES[ 'file' ][ 'name' ];
			$s3 = AWS::get('s3');
			$s3->putObject(array(
				'Bucket'     => $bucketName,
				'Key'        =>$imageKey ,
				'SourceFile' => $tempPath,
				'ACL'    => 'public-read'
			));

			/*$request=$s3->getObject(array(
						'Bucket' => $bucketName, 
						'Key' => $imageKey
				));*/

			$this->httpResponse["content"] = array("successMessage"=>"https://s3-ap-southeast-1.amazonaws.com/wisencrazy/".$imageKey, "developerMessage"=>"FILE_UPLOADED");
			$this->httpResponse["statusCode"] = 200;
			}else{
				$this->httpResponse["content"] = array("errorMessage"=>"Invalid file uploaded", "developerMessage"=>"FILE_INVALID");
				$this->httpResponse["statusCode"] = 417;
			}
			
		}else{
			$this->httpResponse["content"] = array("errorMessage"=>"Method not allowed", "developerMessage"=>"METHOD_TYPE_INVALID");
			$this->httpResponse["statusCode"] = 406;
		}
		
	}
		catch(Exception $e)
	{
		DB::rollback();
		$this->httpResponse["content"] = array("errorMessage"=>"Internal error", "developerMessage"=>$e->getMessage());
		$this->httpResponse["statusCode"] = 500;
	}
	return Response::make(json_encode($this->httpResponse["content"]), $this->httpResponse["statusCode"]);
}

public function uploadImageForAndroid(){
    try{
		if(!empty($_REQUEST['image']))
		{
			// if(!empty($_POST['filename'])){
			// Decode Image
			$binary=base64_decode($_REQUEST['image']);
			$fileName=$_REQUEST['filename'];
			header('Content-Type:image/jpeg; charset=utf-8');
			$bucketName='wisencrazy';
			//$tempPath = $_FILES[ 'file' ][ 'tmp_name' ];
			$imageKey= strtotime(date('Y-m-d H:i:s'));//.$_POST['filename'];
			$finalName=$imageKey.$fileName;
			$s3 = AWS::get('s3');
			$result= $s3->putObject(array(
				'Bucket'     => $bucketName,
				'Key'        =>$finalName,
				'Body' => $binary,
				'ContentType'=>'image/'.pathinfo($fileName, PATHINFO_EXTENSION),
				'ACL'    => 'public-read'
			));

			$this->httpResponse["content"] = array("successMessage"=>$result["ObjectURL"], "developerMessage"=>"FILE_UPLOADED");
			$this->httpResponse["statusCode"] = 200;
		
		}else{
				$this->httpResponse["content"] = array("errorMessage"=>"Invalid file uploaded", "developerMessage"=>"INVALID_FILE");
				$this->httpResponse["statusCode"] = 406;
		}
	}
	catch(Exception $e)
	{
		DB::rollback();
		$this->httpResponse["content"] = array("errorMessage"=>"Internal error", "developerMessage"=>$e->getMessage());
		$this->httpResponse["statusCode"] = 500;
	}
	return Response::make(json_encode($this->httpResponse["content"]), $this->httpResponse["statusCode"]);

}



//profile image update
	public function updateProfileImagePath(){
        try{
            $requestData = Input::all();
            if(!empty($requestData) && !empty($requestData["phoneNo"]) && !empty($requestData["image"]))
            {
		DB::beginTransaction();
                $userInfo = DB::table('users')->where("phone_no","=",$requestData["phoneNo"])->get();
                if(count($userInfo)>0){
                	DB::table('users')->where("phone_no","=",$requestData["phoneNo"])->update(array("profile_image_path"=>$requestData["image"]));
                	DB::commit();
			$this->httpResponse["content"] = array("successMessage"=>"Profile image has been updated", "developerMessage"=>"PATH_UPDATED");
					$this->httpResponse["statusCode"] = 200;
                }else{
                	$this->httpResponse["content"] = array("errorMessage"=>"User Not registered", "developerMessage"=>"NO_RECORD_FOUND");
					$this->httpResponse["statusCode"] = 417;
                }
            }       
        }
        catch(Exception $e)
        {
                DB::rollback();
                $this->httpResponse["content"] = array("errorMessage"=>"Internal error", "developerMessage"=>$e->getMessage());
                $this->httpResponse["statusCode"] = 500;
        }
    	return Response::make(json_encode($this->httpResponse["content"]), $this->httpResponse["statusCode"]);

    }
}
