<?php


//Author:    sathish.k
//E-mail:    ksathish0014@gmail.com
//Team  :    MyChat 

class GroupController extends BaseController {
	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public $responseContents = null;
	public $responseStatusCode = null;

	
	// register group
	public function createGroup(){
	    try{
			$requestData = Input::all();
			if(!empty($requestData) && !empty($requestData["groupName"]) &&!empty($requestData["groupMembers"]))
			{
			   	DB::beginTransaction();
			   	$checkUser = DB::table('users')->where("sid","=",$requestData["createdBy"])->get();
			   	if(count($checkUser)>0){
			   		$sid=$this->getSid();
				    DB::table('group_details')->insertGetId(array("group_name"=>$requestData["groupName"],"image_path"=>$requestData["image"],"group_status"=>$requestData["status"],"status"=>"active","created_by"=>$checkUser[0]->id,"created_on"=>$this->NOW(),"sid"=>$sid));
				    DB::commit();
				    $groupInfo = DB::table('group_details')->where("sid","=",$sid)->get();
				    DB::table('group_members')->insertGetId(array("user_id"=>$checkUser[0]->id,"group_id"=>$groupInfo[0]->id,"added_by"=>$checkUser[0]->id,"status"=>"unmute","role"=>"admin","created_on"=>$this->NOW(),"sid"=>$this->getSid()));
				    if(count($groupInfo)>0)
				    {
				    	$addedStatus=$this->addGroupMembers($groupInfo[0]->id,$requestData["groupMembers"],$checkUser[0]->id);
				    	if($addedStatus==true)
				    	{
				    		$this->httpResponse["content"] =array("successMessage"=>"Group created");
							$this->httpResponse["statusCode"] = 200;
				    	}else{
				    		$this->httpResponse["content"] =array("errorMessage"=>"Members not added");
							$this->httpResponse["statusCode"] = 417;
				    	}
						
				    }
				    else
				    {
					$this->httpResponse["content"] =  array("errorMessage"=>"group not created,Please contact admin!");
					$this->httpResponse["statusCode"] = 417;
				    }
			   	}else{
			   		$this->httpResponse["content"] =  array("errorMessage"=>"Required field empty");
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
	
	protected function addGroupMembers($groupId,$groupMembers,$addedBy){
		if(!empty($groupId)&& !empty($groupMembers) && !empty($addedBy)){
			$sentUser = DB::table('users')->where("id","=",$addedBy)->get();
			for($i=0;$i<count($groupMembers);$i++){
				$checkUser = DB::table('users')->where("sid","=",$groupMembers[$i])->get();
				if(!empty($checkUser)){
					DB::table('group_members')->insertGetId(array("user_id"=>$checkUser[0]->id,"group_id"=>$groupId,"added_by"=>$addedBy,"role"=>"user","status"=>"unmute","created_on"=>$this->NOW(),"sid"=>$this->getSid()));
				    // Replace with the real server API key from Google APIs
				    $apiKey = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";
				    // Replace with the real client registration IDs
				    $registrationIDs = $checkUser[0]->gcm_id;
				    $registrationIDs;
				    // Message to be sent
				    $message =  array( "type"=> "group_notification", 
							        "content"=> "Hi you have added in this group",
							        "addedBy"=>$sentUser[0]->first_name);
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
				}
			}
			return true;
		}else{
			return false;
		}
	}



	// add group members 

	public function addGroupMembersinExixtingGroup(){
		try{
			$requestData = Input::all();
			//return DB::table('group_details')->get();
		    if(!empty($requestData["groupSid"])&&!empty($requestData["groupMembers"]) && !empty($requestData["createdBy"])){
		    	$checkUser = DB::table('users')->where("sid","=",$requestData["createdBy"])->get();
		    	$checkGroup = DB::table('group_details')->where("sid","=",$requestData["groupSid"])->get();
		    	if(!empty($checkUser)&& !empty($checkGroup)){
		    		$addUserResponse=$this->addGroupMembers($checkGroup[0]->id,$requestData["groupMembers"],$checkUser[0]->id);
		    		if($addUserResponse==true){
		    			$this->httpResponse["content"] =array("successMessage "=>"Successfully added");
						$this->httpResponse["statusCode"] = 200;
		    		}else{
		    			$this->httpResponse["content"] =array("errorMessage "=>"Member not added in group");
						$this->httpResponse["statusCode"] = 417;
		    		}
		    	}else{
		    		$this->httpResponse["content"] =array("errorMessage"=> "Admin or group not found");
					$this->httpResponse["statusCode"] = 417;
		    	}
		    	
		    }else{
		    	$this->httpResponse["content"] =array("errorMessage"=> "Required fields empty");
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


	//get Group info
	public function getGroupInfo(){
		try{
			if(!empty($_REQUEST['groupSid'])){
				$groupData=DB::table("group_details")->where("sid",$_REQUEST['groupSid'])->get();
				if(!empty($groupData)){
					$groupUsers=DB::table("group_members as gm")->join('users AS u','u.id','=','gm.user_id')
							->select("u.first_name as firstName","u.last_name as lastName","u.profile_image_path as image","u.sid as userSid","u.gcm_id as gcmId","u.phone_no as phone","gm.role as role")
							->where("gm.group_id",$groupData[0]->id)
							->get();
					$groupInfo=array("groupId"=>$groupData[0]->id,"groupName"=>$groupData[0]->group_name,"groupSid"=>$groupData[0]->sid,"createdOn"=>$groupData[0]->created_on,"status"=>$groupData[0]->status,"groupStatus"=>$groupData[0]->group_status,"image"=>$groupData[0]->image_path,"groupMembers"=>$groupUsers);
					$this->httpResponse["content"] =$groupInfo;
					$this->httpResponse["statusCode"] = 200;
				}else{
					$this->httpResponse["content"] =array("errorMessage"=> "Group not found");
					$this->httpResponse["statusCode"] = 417;
				}
				
		  	}else{
		    	$this->httpResponse["content"] =array("errorMessage"=> "Required field empty");
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

	protected function removeGroupMembers($groupId,$groupMembers,$removedBy){
		if(!empty($groupId)&& !empty($groupMembers) && !empty($removedBy)){
			$sentUser = DB::table('users')->where("id","=",$removedBy)->get();
			$group = DB::table('group_details as gd')->join("group_members as gm","gm.group_id","=","gd.id")->join("users as u","u.id","=","gm.user_id")->where("gd.id","=",$groupId)->get();
			$removeUser=DB::table('users')->where("sid","=",$groupMembers)->get();
			for($i=0;$i<count($group);$i++){
					
				    // Replace with the real server API key from Google APIs
				    $apiKey = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";
				    // Replace with the real client registration IDs
				    $registrationIDs = $group[$i]->gcm_id;
				    // Message to be sent
				    $message =  array( "type"=> "group_notification", 
							        "content"=> $removeUser[0]->first_name." this group",
							        "removedBy"=>$sentUser[0]->first_name);
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
				
			}
			 DB::table('group_members')->where("user_id","=",$removeUser[0]->id)->delete();

			return true;
		}else{
			return false;
		}
	}



// remove member from group

	public function removeMember(){
		try{
			$requestData=Input::all();
			 if(!empty($requestData["groupSid"])&&!empty($requestData["groupMember"]) && !empty($requestData["removedBy"])){
		    	$checkUser = DB::table('users')->where("sid","=",$requestData["removedBy"])->get();
		    	 $checkGroup = DB::table('group_details')->where("sid","=",$requestData["groupSid"])->get();
		    	if(!empty($checkUser)&& !empty($checkGroup)){
		    		$removeUserResponse=$this->removeGroupMembers($checkGroup[0]->id,$requestData["groupMember"],$checkUser[0]->id);
		    		if($removeUserResponse==true){
		    			$this->httpResponse["content"] =array("successMessage "=>"Successfully removed");
						$this->httpResponse["statusCode"] = 200;
		    		}else{
		    			$this->httpResponse["content"] =array("errorMessage "=>"Member not added in group");
						$this->httpResponse["statusCode"] = 417;
		    		}
		    	}else{
		    		$this->httpResponse["content"] =array("errorMessage"=> "Admin or group not found");
					$this->httpResponse["statusCode"] = 417;
		    	}
		    	
		    }else{
		    	$this->httpResponse["content"] =array("errorMessage"=> "Required fields empty");
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
	
	//send group message
	public function sendGroupNotification(){
	    try{
			$requestData = Input::all();
			if(!empty($requestData) && !empty($requestData["groupSid"]) && !empty($requestData["msg"]))
			{
				$groupInfo = DB::table('group_details')->where("sid","=",$requestData["groupSid"])->get();
				if(!empty($groupInfo)){
					$userInfo = DB::table('group_members as gm')->join("users as u","u.id","=","gm.user_id")->where("gm.group_id","=",$groupInfo[0]->id)->where("u.sid","!=",$requestData["sentBy"])->where("u.account_status","=","active")->select("u.gcm_id as gcmId")->get();
					if(!empty($userInfo)){
						$gcmIds=array();
						for($i=0;$i<count($userInfo);$i++){
							array_push($gcmIds, $userInfo[$i]->gcmId);
						}
						if($requestData["msg"]['type']){
							if($requestData["msg"]['content']){
								if(count($userInfo)>0)
								{
									// Replace with the real server API key from Google APIs
									$apiKey = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX-CsezU";

									// Replace with the real client registration IDs
									$registrationIDs = $gcmIds;
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
									return json_encode($result);
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
					}else{
						$this->httpResponse["content"] =  array("errorMessage"=>"Group Members Not found");
						$this->httpResponse["statusCode"] = 417;
					}
				}else{
					$this->httpResponse["content"] =  array("errorMessage"=>"Group Not found");
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

public function getUserGroup(){
        try{
			if(!empty($_REQUEST['sid']))
            {
                $groupData=DB::table("users as u")->join("group_members as gm","gm.user_id","=","u.id")
			                ->join("group_details as gd","gd.id","=","gm.group_id")->where("u.sid","=",$_REQUEST['sid'])
			                ->select("gd.group_name as name","gd.image_path as image","gd.status as status","gd.sid as sid")->get();
                if(count($groupData)){
                	$this->httpResponse["content"] = array("successMessage"=>$groupData);
                	$this->httpResponse["statusCode"] = 200;
                } else{
                	$this->httpResponse["content"] = array("errorMessage"=>"No group found");
                	$this->httpResponse["statusCode"] = 417;
                }
            }else{
                $this->httpResponse["content"] = array("errorMessage"=>"SID should not be empty");
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



}
