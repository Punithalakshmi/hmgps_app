<?php
require APPPATH.'/libraries/REST_Controller.php';

class Service extends REST_Controller
{
       protected $profile_url = 'http://www.heresmygps.com/assets/uploads/profile/resize';
       
        function register_app_get() {
            
            //check for required values
            $app_id = $this->get('app_id');
    		if(empty($app_id))
    		{
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            $this->load->config("rest");
            
            $key_table = $this->config->item('rest_keys_table');
            
            $result =  $this->db->get_where($key_table,array('key' => $app_id))->num_rows();
                
            if(empty($result))
    		{
    		     $this->db->insert($key_table,array('key' => $app_id,'custom_key'=>1,'date_created' => strtotime(date('Y-m-d'))));
                 
                 if($this->db->insert_id()) {
                     return $this->response(array('status' =>'success','api_key' => $app_id), 200);
                 }
                 else
                 {
                     return $this->response(array('status' => "error",'msg' => 'Unknown Error Occurred!! Try Again...','error_code' => 2), 404);
                 }
    		}
            else
            {
                  return $this->response(array('status' =>'success','api_key' => $app_id), 200);  	
            }
            
        }
        
        function user_register_get() {
             
            $default_id     = $this->get('default');
            $phonenumber    = $this->get('phonenumber');
            $email          = $this->get('email');
            $profile_image  = $this->get('profile_image');
            $user_plan      = $this->get('user_plan');
            $password       = $this->get('password');
            $android_id     = $this->get('android_id');
            $device_id      = $this->get('device_id');
            $android_id     = (!empty($android_id))?$android_id:"";
            $device_id      = (!empty($device_id))?$device_id:"";
            $password       = md5($password);
            
            $this->load->model(array("user_model","plan_model","group_model"));
            
            //get plan id
            $plan = $this->plan_model->get_plan_details(array("plan_type"=>$user_plan));
            
            $plan_id = $plan['plan_id'];
            
            $source_image   = FCPATH."assets/uploads/profile/".$profile_image;
            $image_crop_url = FCPATH."assets/uploads/profile/resize";
            
            //check if phonenumber already exist or not for guest user
            $result = $this->user_model->check_unique(array("phonenumber" => $phonenumber, "plan_id" => 1));
            
            if(count($result)) {
                    
                    if($default_id){
                       //default ID check
                       $result_default = $this->user_model->check_unique(array("default_id" => $default_id));
                        if(!empty($result_default)) {
                            return $this->response(array('status' => "error",'msg' => 'Default ID already exist.','error_code' => 103), 404);
                        }
                      }
                   
                     if($email) {
                      //Email check
                      $result_email = $this->user_model->check_unique(array("email" => $email));
                      if(!empty($result_email)) {
                        return $this->response(array('status' => "error",'msg' => 'Email already exist.','error_code' => 112), 404);
                      }
                   }
                    
                    $ins_data = array();
                    $ins_data['default_id']     = $default_id;
                    $ins_data['password']       = $password;
                    $ins_data['phonenumber']    = $phonenumber;
                    $ins_data['email']          = $email;
                    $ins_data['profile_image']  = $profile_image;
                    $ins_data['plan_id']        = $plan_id;
                    $ins_data['date_updated']   = strtotime(date('Y-m-d H:i:s'));
                        
                    $this->user_model->update("user",$ins_data,array("id" => $result['id']));
                    
                    if(!empty($profile_image)) {
                    
                         $image_name = $result['id'].".jpg";
                         image_crop($source_image,$image_crop_url,$image_name);
                    }
                    
                    if(!empty($default_id)) {
                        $default_group = create_group($default_id,$result['id'],'default');
                     }
                        
                return $this->response(array('status' =>'success','request_type' => 'guest_login','user_id' => $result['id'], 'default_id' => $result['default_id'], 'phonenumber' => $result['phonenumber'], 'android_id' => $result['android_id']), 200);
               
            }
            else
            {
              
                if($email) {
                  //Email check
                  $result = $this->user_model->check_unique(array("email" => $email, 'plan_id !=' => 1));
                  if(!empty($result)) {
                    return $this->response(array('status' => "error",'msg' => 'Email already exist.','error_code' => 112), 404);
                  }
               }
               
               if($default_id){
                   //default ID check
                   $result = $this->user_model->check_unique(array("default_id" => $default_id,'plan_id !=' => 1));
                    if(!empty($result)) {
                        return $this->response(array('status' => "error",'msg' => 'Default ID already exist.','error_code' => 103), 404);
                    }
                }
               
                 if($phonenumber){
                    //phone number check
                   $result = $this->user_model->check_unique(array("phonenumber" => $phonenumber,'plan_id !=' => 1));
                   
                    if(!empty($result)) {
                        return $this->response(array('status' => "error",'msg' => 'Phonenumber already exist.','error_code' => 106), 404);
                        }
                    }
                    
                   if($android_id){
                    //Android Id check
                    $result = $this->user_model->check_unique(array("android_id" => $android_id));
                    if(!empty($result)) {
                        return $this->response(array('status' => "error",'msg' => 'Please Signup or Sign In. Your device already registerd as guest..','error_code' => 106), 404);
                        }
                    }
                    
                    if($device_id){
                    //Device Id check
                     $result = $this->user_model->check_unique(array("device_id" => $device_id));
                    if(!empty($result)) {
                        return $this->response(array('status' => "error",'msg' => 'Device Id already exist.','error_code' => 106), 404);
                        }
                    }
                    
                  
                    $ins_data = array();
                    $ins_data['default_id']     = $default_id;
                    $ins_data['display_name']   = $default_id;
                    $ins_data['password']       = $password;
                    $ins_data['phonenumber']    = $phonenumber;
                    $ins_data['email']          = $email;
                    $ins_data['profile_image']  = $profile_image;
                    $ins_data['plan_id']        = $plan_id;
                    $ins_data['date_created']   = strtotime(date('Y-m-d H:i:s'));
                    $ins_data['android_id']     = $android_id;
                    $ins_data['device_id']      = $device_id;
                
                    $user_id = $this->user_model->insert('user',$ins_data);
                        
                   
                    if(!empty($profile_image)) {
                         $image_name = $user_id.".jpg";
                        image_crop($source_image,$image_crop_url,$image_name);
                    }
                        
                    //create group by through default id, phonenumber
                    if(!empty($default_id)) {
                        $default_group = create_group($default_id,$user_id,'default');
                     }
                     if(!empty($phonenumber)) {
                      $phone_group    = create_group($phonenumber,$user_id,'phonenumber');
                     }
                     
                    if($user_id) {
                        return $this->response(array('status' =>'success','request_type' => 'create_account','user_id' => $user_id, 'default_id' => $default_id, 'phonenumber' => $phonenumber), 200);
                    }
                    else
                    {
                        return $this->response(array('status' => "error",'request_type' => 'create_account','msg' => 'Unknown Error Occurred!! Try Again...','error_code' => 2), 404);
                    }
                
                }
        }
           
        
        function login_get() {
            
            $val     = $this->get('val');
            $pass    = $this->get('password');
            //check for required values
    		if($val=='' || $pass == ''){
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            
            $pass = md5($pass);
            $result = $this->db->query("select * from user where default_id='".$val."' or phonenumber='".$val."'")->row_array();  
            
            if(!count($result)) {
                return $this->response(array('status' => "error",'msg' => 'Invalid Username.','error_code' => 4), 404);    
            }
            else
            {
                $this->load->model("plan_model");
                
                if(isset($result['password']) && ($result['password'] == $pass)) {
                     $default_id        = (!empty($result['default_id']))?$result['default_id']:"";
                     $phonenumber       = (!empty($result['phonenumber']))?$result['phonenumber']:"";
                     $profile_image     = (isset($result['profile_image']) && ($result['profile_image']!=''))?site_url()."assets/uploads/profile/resize/large_".$result['id'].".jpg":'http://heresmygps.com/assets/images/no_image.png';
                     $email             = (!empty($result['email']))?$result['email']:"";
                     $android_id        = (!empty($result['android_id']))?$result['android_id']:"";
                     $device_id         = (!empty($result['device_id']))?$result['device_id']:"";
                     $plan              = $this->plan_model->get_plan_details(array("plan_id" => $result['plan_id']));
                     $plan              = $plan['plan_type'];
                            
                    return $this->response(array('status' =>'success','request_type' => 'login','default_id' => $default_id,'display_name' => $result['display_name'],'phonenumber' => $phonenumber,'user_id' => $result['id'],'email' => $email,'profile_image' => $profile_image,'android_id' => $android_id,'device_id' => $device_id, 'plan' => $plan ), 200);
                }
                else
                {
                    return $this->response(array('status' => "error",'request_type' => 'login', 'msg' => 'Login faild.','error_code' => 108), 404);
                }
            }
        }
        
        
        function social_login_get(){
            
            //check for required values
            $email     = $this->get('email');
            $user_plan = $this->get('user_plan');
            
    		if(!$this->get('email') || !$this->get('profile_image')) {
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            
            $where = array("email" => $email);
            $result = $this->db_model->get_user_details($where);
           
            if(!empty($result)) {    
    			//return $this->response(array('status' =>'success',$this->_prepare_user_details($result)), 200);
                 $default_id        = (!empty($result['default_id']))?$result['default_id']:"";
                 $phonenumber       = (!empty($result['phonenumber']))?$result['phonenumber']:"";
                 $profile_image     = (isset($result['profile_image']) && ($result['profile_image']!=''))?$this->profile_url."large_".$result['id'].".jpg":'http://heresmygps.com/assets/images/no_image.png';
                 $email             = (!empty($result['email']))?$result['email']:"";
                 $android_id        = (!empty($result['android_id']))?$result['android_id']:"";
                 $device_id         = (!empty($result['device_id']))?$result['device_id']:"";
                 $user_id           = (!empty($result['id']))?$result['id']:"";
    
    			 return $this->response(array('status' =>'success','default_id' => $default_id,'phonenumber' => $phonenumber,'user_id' => $result['id'],'email' => $email,'profile_image' => $profile_image,'android_id' => $android_id,'device_id' => $device_id), 200);
            }
            else
            {
                $this->load->model(array("user_model","plan_model"));
            
                //get plan id
                $plan    = $this->plan_model->get_plan_details(array("plan_type"=>$user_plan));
                $plan_id = $plan['plan_id'];
                
                $default_id = "";
                
                $profile_image = $this->get('profile_image');
                
              if($this->get('email')){
                   
                   $result = $this->user_model->check_unique(array("email" => $email));
                    if(empty($result)) {
                       $email = $this->get('email');
                    }
                    else
                    {
                         return $this->response(array('status' => "error", 'request_type' => 'social_login','msg' => 'Email Already exist.','error_code' => 112), 404);
                    }
                }
                if($this->get('default')) {
                   //default ID check
                    $result = $this->user_model->check_unique(array("default_id" => str_to_lower($this->get('default'))));
                    if(empty($result)) {
                       $default_id = $this->get('default');
                    }
                    else
                    {
                         return $this->response(array('status' => "error", 'request_type' => 'social_login','msg' => 'Default Id Already exist.','error_code' => 112), 404);
                    }
                 }
                 
                 $ins_data = array('default_id' => $default_id,'display_name' => $default_id,'phonenumber' => $phonenumber,'email' => $email, 'profile_image' => $profile_image, 'plan_id' => $plan_id);
                 $ins_data['date_created'] = strtotime(date('Y-m-d H:i:s'));   
                 $user_id = $this->user_model->insert("user",$ins_data);
                 
                 $file    = FCPATH."assets/uploads/profile/social.jpg";
                 $current = file_get_contents($profile_image);
              
                 file_put_contents($file, $current, FILE_APPEND);
                 
                 $image_crop_url = FCPATH."assets/uploads/profile/resize";
                 $source_image   = FCPATH."assets/uploads/profile/social.jpg";
                 $image_name     = $user_id.".jpg";
                 image_crop($source_image,$image_crop_url,$image_name);
                 
                 //create group
                 create_group($default_id,$user_id,'default');
                  
                 if(!$user_id) {
                    return $this->response(array('status' => "error",'request_type' => 'social_login','msg' => 'Unknown Error Occurred!! Try Again...','error_code' => 2), 404);
                 }
                 $pimage = $this->profile_url."/large_$user_id.jpg";
                 return $this->response(array('status' =>'success','request_type' => 'social_login','default_id' => $default_id,'phonenumber' => $phonenumber,'user_id' => $user_id, 'profile_image' => $pimage, 'email' => $email), 200);
            }
            
        }
        
         //forgot password
        
        function forgot_password_get(){
            
            if(!$this->get('email') && !$this->get('appkey')) {
                return $this->response(array('status' => 'error', 'msg' => 'Required fields missing in your request', 'error_code' => 1), 404);
            }
            
            $email  = $this->get('email');
            $appkey = $this->get("appkey");
            
                $result = $this->db->query("select * from user where email='".$email."'")->row_array();
                if(count($result)) {
                    
                     $default      = md5($result['default_id']);
                     $user_id      = base64_encode($result['id']);
                     $current_time = strtotime(date("Y-m-d H:i:s"));
                     $fpwd_url = "http://heresmygps.com/user/changepassword?default=$default&id=$user_id&expire_time=$current_time";
                     $username = $result['default_id'];
                     $message  = "<p>Hi $username,</p><br /><br /><p>Please click below link to reset your password.</p><br /><br /><p><a href='".$fpwd_url."' title='Reset Your Password'>Click Here</a></p><br /><br /></br /><br />";
                     $message .= "<p>Thanks,<p>";
                     $message .= "<p><a href='http://heresmygps.com/'>Heresmygps.com</a></p>"; 
                      
                     $config['charset'] = 'iso-8859-1';
                     $config['wordwrap'] = TRUE;
                     $config['mailtype'] = 'html';
                     $this->load->library('email');
                     $this->email->set_newline("\r\n");
                     $this->email->initialize($config);
                     
                     $this->email->from('hmgps@gmail.com', 'HMGPS');
                     $this->email->to($email);
                     $this->email->subject('Forgot Password');
                     $this->email->message($message);
                     $this->email->send();
                    
                    return $this->response(array('status' =>'success','request_type' => 'forgot_password','email' => $email,'user_id' => $result['id']), 200);
                }
                else
                {
                    return $this->response(array('status' => "error",'request_type' => 'forgot_password','msg' => 'Invalid Mail ID.','error_code' => 11), 404);
                }
        }
        
       
        function _prepare_user_details($user_data = array(),$user_id = 0) {
            
            $result = array();
            $result['user']['profile'] = $result['user']['account'] = array();
            $user_profile_fields = array('default_id','id','phonenumber','email','profile_image','android_id','device_id');
            $user_account_fields = array('plan_name');
            
            if(!empty($user_data)) {
                
                foreach($user_profile_fields as $field) {
                    $result['user']['profile'][$field] = (isset($user_profile_fields[$field]))?$user_profile_fields[$field]:"";
                }
                
                foreach($user_account_fields as $field) {
                    $result['user']['account'][$field] = (isset($user_account_fields[$field]))?$user_account_fields[$field]:"";
                }
            }
            else if(empty($user_data) && $user_id ) {
                
                $this->_prepare_user_details( $this->db_model->get_user_details(array('id' => $user_id),implode(',',$user_profile_fields)) );
            }
            
            return $result;
        }
        
        
      
        function user_profile_save_get() {
            
               //check for required values
    		if(!$this->get('phonenumber')) {
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            
            
            
            $default_id  = $this->get('default');
            $phonenumber = $this->get('phonenumber');
            $user_id     = $this->get('user_id');
            $email       = $this->get('email');
            $password    = $this->get('password');
            $profile_image= $this->get('profile_image');
            
            $source_image   = FCPATH."assets/uploads/profile/".$profile_image;
            $image_crop_url = FCPATH."assets/uploads/profile/resize";
            
            $this->load->model(array("user_model","group_model"));
            
            $ins_data = array();
            
            if(!empty($email)) {
              //Email check
               if(!empty($user_id))
                $this->db->where('id != ',$user_id);
              $this->db->where('lower(email)',str_to_lower($email));
              $res_email = $this->db->get('user')->row_array();
            
              $ins_data['email']        = (!empty($email))?$email:$res_email['email'];
                
              if(count($res_email)) {
                return $this->response(array('status' => "error",'request_type' => 'user_profile','msg' => 'Email already exist.','error_code' => 112), 404);
              }
           }
           
           if(!empty($default_id)){
               //default ID check
               if($user_id)
                    $this->db->where('id != ',$user_id);
                    
               $this->db->where('lower(default_id)',str_to_lower($default_id));
               $res_default =  $this->db->get('user')->row_array();
               
                $ins_data['default_id']   = (!empty($default_id))?$default_id:$res_default['default_id'];
                
                if(count($res_default)) {
                    return $this->response(array('status' => "error",'request_type' => 'user_profile','msg' => 'Default ID already exist.','error_code' => 103), 404);
                }
            }
            
            if(!empty($phonenumber)){
                //phone number check
                 if($user_id)
                    $this->db->where('id != ',$user_id);
                
                $this->db->where('phonenumber',$phonenumber);    
                $res_phonenumber =  $this->db->get('user')->row_array();
                
                $ins_data['phonenumber']  = (!empty($phonenumber))?$phonenumber:$res_phonenumber['phonenumber'];
                
                if(count($res_phonenumber)) {
                    return $this->response(array('status' => "error",'request_type' => 'user_profile','msg' => 'Phonenumber already exist.','error_code' => 106), 404);
                }
            }
            
             $user_data = $this->user_model->check_unique(array("id" => $user_id));
            
             if(!empty($default_id) && !empty($phonenumber)) {
                //update user groups by through default id and phonenumber
                user_group_update($user_data,$default_id,$phonenumber);
             }
             
             
             $ins_data['date_updated'] = strtotime(date('Y-m-d H:i:s'));
             //print_r($ins_data);
             if(!empty($password)) {
                $ins_data['password'] = md5($password);
             }
             
             if(!empty($profile_image)) {
                 $image_name = $user_id.".jpg";
                 image_crop($source_image,$image_crop_url,$image_name);
             }
             
            if($user_id){
                  $this->user_model->update('user',$ins_data, array('id' => $user_id));
                  return $this->response(array('status' =>'success','request_type' => 'user_profile','user_id' => $user_id, 'default_id' => $default_id, 'phonenumber' => $phonenumber), 200);
            }
            else
            {
                return $this->response(array('status' => "error",'request_type' => 'user_profile','msg' => 'Unknown Error Occurred!! Try Again...','error_code' => 2), 404);
            }
        }
        
        
        
        function user_gcm_update_post() {
            
               //check for required values
    		if((!$this->post('user_id')) && (!$this->post('gcm_id') )) 
    		{
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            
            $user_id = $this->post('user_id');
            $gcm_id  = $this->post('gcm_id');
            
             
            if($user_id){
                 $this->load->model("user_model");
                 $ins_data                 = array();
                 $ins_data['gcm_id']       = $gcm_id;
                 $ins_data['date_updated'] = strtotime(date('Y-m-d H:i:s'));
                 
                 $this->user_model->update('user',$ins_data,array('id' => $user_id));
            }
            
            if($user_id) {
                return $this->response(array('status' =>'success','user_id' => $user_id), 200);
            }
            else
            {
                return $this->response(array('status' => "error",'msg' => 'Unknown Error Occurred!! Try Again...','error_code' => 2), 404);
            }
        }
        
        
    //user position update
    function user_position_save_get()
    {    
      //check for required values
		if(!$this->get('user_id') || !$this->get('lat') || !$this->get('lon')) {
			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
		}
     
        $user_id = $this->get('user_id');
        
        $result = $this->db->get_where('user_position',array('user_id' => $user_id))->num_rows();
       
        $this->load->model(array("user_position_model","user_groups_model"));
        
        $ins_data = array();
        $ins_data['user_id'] = $user_id;
        $ins_data['lat']     = $this->get('lat');
        $ins_data['lon']     = $this->get('lon');
        $ins_data['accuracy']= $this->get('accuracy');
        
        if($result > 0) {
            $ins_data['date_updated'] = strtotime(date('Y-m-d H:i:s'));
            $update = $this->user_position_model->update($ins_data,array('user_id' => $user_id));
            
            //update map last seen time
            $up_data = array();
            $up_data['last_seen_time'] = date("Y-m-d H:i:s");
            $this->user_groups_model->update($up_data,array("user_id" => $user_id,"status" => 1));
            
        }
        else
        {
            $ins_data['date_created'] = strtotime(date('Y-m-d H:i:s'));
            $this->user_position_model->insert($ins_data);
        }
             	
       $this->push_group_user_data_to_gcm("",$user_id);
       return $this->response(array('status' =>'success', 'request_type' => 'user_position_update'), 200);
    }
        
        
    //create group
     function group_save_get() 
     {
           
            //check for required values
    		if(!$this->get('name') || !$this->get('type')|| !$this->get('user_id') || !$this->get('join_key') || !$this->get('location_type'))
    		{
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            
            $this->load->model(array("group_model","user_groups_model"));
            
            $group_id = $this->get('group_id');
            $join_key = $this->get('join_key');
            $user_id  = $this->get('user_id');
            
            $where = array();
            $where['join_key'] = str_to_lower($join_key);
         
            if(!empty($group_id))
             $where['id'] = str_to_lower($group_id);
             
            $result = $this->group_model->check_unique($where);
            
            if(count($result)) {
                return $this->response(array('status' => "error",'msg' => 'Join key already exist.','error_code' => 109), 404);
            }
          
            $description = $this->get('description');
            $name        = $this->get('name');
            
            $ins_data = array();
            $ins_data['name']            = $name;
            $ins_data['type']            = $this->get('type');
            $ins_data['join_key']        = $join_key;
            $ins_data['description']     = (!empty($description))?$description:$name;
            $ins_data['location_type']   = $this->get('location_type');
            $ins_data['user_id']         = $this->get('user_id');   
            $ins_data['is_available']    = $this->get('is_available');
            
            if($ins_data['location_type'] == 'static'){
                $ins_data['lat'] = $this->get('lat');
                $ins_data['lon'] = $this->get('lon');
            }
            
            if($group_id) { 
                $ins_data['date_updated'] = strtotime(date('Y-m-d H:i:s'));
                $this->group_model->update($ins_data,array('id' => $group_id));
            }else
            {
                $ins_data['status']    = 1;
                $ins_data['date_created'] = strtotime(date('Y-m-d H:i:s'));
                $group_id =  $this->group_model->insert($ins_data);
                
                //join group
                $res = $this->user_groups_model->check_unique(array('user_id' => $user_id, 'group_id' => $group_id));
                $cnt    = count($res);
                
                if(empty($cnt)) {
                    $ins_data = array();
                    $ins_data['user_id']  = $user_id;
                    $ins_data['group_id'] = $group_id; 
                    $ins_data['status']   = 1; 
                    $ins_data['is_joined'] = 1; 
                    $add_group            = $this->user_groups_model->insert($ins_data);
                }
            }
            
              if($group_id) {
                	
                    return $this->response(array('status' =>'success','request_type' => 'group_creation','group_id' => $group_id,'join_key' => $join_key), 200);
              }
              else
              {
                    return $this->response(array('status' => "error",'request_type' => 'group_creation','msg' => 'Unknown Error Occurred!! Try Again...','error_code' => 2), 404);
              }   
              
        }
        
        function group_list_get()
        {
            
            $this->load->model(array("group_model","user_groups_model"));
            
            //check for required values
    		if(!$this->get('user_id')){
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            
            $user_groups = $this->group_model->lists($this->get('user_id'));
            
            foreach($user_groups as $ukey => $uvalue) {
                $count  = $this->user_groups_model->get_groups_member_count(array("group_id" => $uvalue['id']));
                $user_groups[$ukey]['members_count'] = $count;
            }
            
            
            return $this->response(array('status' =>'success','request_type' => 'group_list','list' => $user_groups), 200); 
       }
      
      
      //group update
      function group_update_get() 
      {
         if((!$this->get('group_id')) || (!$this->get('available')) || (!$this->get('type')) || (!$this->get('location_type'))) {
            return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
         }
         
         $available     = $this->get('available');
         $type          = $this->get('type');
         $location_type = $this->get('location_type');
         $group_id      = $this->get('group_id');
         
         $this->load->model("group_model");
         
         $ins_data = array();
         $ins_data['type']         = $type;
         $ins_data['is_available'] = $available;
         $ins_data['location_type']= $location_type;
         
         $this->group_model->update($ins_data,array("id" => $group_id));
         
         return $this->response(array("status" => 'success', 'request_type' => 'group_update'), 200);
      }
      
      
      //join group
      function join_group_get($user_id='',$group_id ='') 
      { 
        //check for required values
		if(!$this->get('join_key') || !$this->get('user_id')){
			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
		}
        
        $this->load->model("group_model");
        $this->load->model("user_groups_model");
        
        $join_key = $this->get('join_key');
        $result   = $this->group_model->check_unique(array("join_key" => $join_key));
        
        $user_id  = $this->get('user_id');
        
        if(empty($result)) {
             return $this->response(array('status' => "error",'msg' => 'Invalid Join key','error_code' => 110), 404);
        }
        
        $this->send_notification_group_owner($join_key,$user_id);
        
        $res = $this->user_groups_model->check_unique(array('user_id' => $user_id, 'group_id' => $result['id']));
        $cnt = count($res);
        
        if(empty($cnt)) {
            $ins_data = array();
            $ins_data['user_id']   = $user_id;
            $ins_data['group_id']  = $result['id']; 
            $ins_data['status']    = 1;
            $ins_data['is_joined'] = 1; 
            $add_group             = $this->user_groups_model->insert($ins_data);
        }
        else
        {
            $ins_data['is_joined'] = 1; 
            $up_group            = $this->user_groups_model->update($ins_data,array("user_id" => $user_id));
        }
        //echo $add_group; exit;
       // return $this->response(array('status' =>'success', 'join_key' => $join_key, 'group_name' => $result['name']), 200);
        
        $this->search_map_get($join_key,$user_id);
    }
    
    
    //group leave update
    function group_leave_get()
    {
        //check for required values
		if(!$this->get('group_id')|| !$this->get('user_id')){
			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
		}  
        
        $this->load->model(array("user_groups_model","group_model"));  
        $user_id   = $this->get("user_id");
        $join_key  = $this->get("group_id"); 
        
        $result = $this->group_model->check_unique(array("join_key" => $join_key));
        
        if(!count($result)) {
            return $this->response(array('status' => "error",'msg' => 'Invalid Group.','error_code' => 101), 404);
        }
            
        $ins_data  = array();
        $ins_data['status']  = 0;
        $ins_data['user_leave_time'] = date("Y-m-d H:i:s");
        $this->user_groups_model->update($ins_data,array("user_id" => $user_id, 'group_id' => $result['id']));
        
        if(($result['is_available'] !=1) && ($user_id == $result['user_id'])) {
            $this->group_model->delete(array("id" => $result['id']));
            $this->user_groups_model->delete(array("group_id" => $result['id']));
            
        }
        
          return $this->response(array('status' =>'success', 'request_type' => 'group_inactive','join_key' => $join_key, 'group_status' => 0, 'is_availabel' => $result['is_available']), 200);  
    }
    
    //set favourite map
    function favourite_group_get()
    {
        //check for required values
		if(!$this->get('group_id')|| !$this->get('user_id')){
			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
		}  
        
        $this->load->model(array("user_groups_model","group_model"));  
        $user_id   = $this->get("user_id");
        $join_key  = $this->get("group_id"); 
        
        $result = $this->group_model->check_unique(array("join_key" => $join_key));
        
        if(!count($result)) {
            return $this->response(array('status' => "error",'msg' => 'Invalid Group','error_code' => 101), 404);
        }
            
        $ins_data  = array();
        $ins_data['is_favourite'] = 1;
        $this->user_groups_model->update($ins_data,array("user_id" => $user_id, 'group_id' => $result['id']));
        
        return $this->response(array('status' =>'success', 'request_type' => 'set_group_favourite','join_key' => $join_key, 'favourite' => 1), 200);  
    }
    
    //get favourites
    function favourite_groups_get()
    {
         //check for required values
		if(!$this->get('user_id')){
			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
		}  
        
        $this->load->model(array("user_groups_model"));  
        $user_id   = $this->get("user_id");  
         
        $favourite_groups    = $this->user_groups_model->get_favourite_groups($user_id);
        
        foreach($favourite_groups as $fkey => $fvalue) {
            $count  = $this->user_groups_model->get_groups_member_count(array("group_id" => $fvalue['id']));
            $favourite_groups[$fkey]['members_count'] = $count;
        }
            
        return $this->response(array('status' =>'success','request_type' => 'favourite_list','list' => $favourite_groups), 200);
    }
    
    
    //get joined groups
    function joined_groups_get()
    {
       //check for required values
		if(!$this->get('user_id')){
			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
		}  
        
        $this->load->model(array("user_groups_model"));  
        $user_id   = $this->get("user_id");  
         
        $joined_groups    = $this->user_groups_model->get_joined_groups($user_id);
       
        foreach($joined_groups as $jkey => $jvalue) {
            $count  = $this->user_groups_model->get_groups_member_count(array("group_id" => $jvalue['id']));
            $joined_groups[$jkey]['members_count'] = $count;
        }
        return $this->response(array('status' =>'success','request_type' => 'joined_list','list' => $joined_groups), 200);
    }
   
   //delete groups
   function delete_groups_get()
   {
     //check for required values
		if(!$this->get('group_id')){
			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
		}  
        
        $this->load->model(array("user_groups_model","group_model"));  
        $group_id   = $this->get("group_id");
        
        $this->user_groups_model->delete(array("group_id" => $group_id));
        $this->group_model->delete(array("id" => $group_id));
        
       return $this->response(array('status' =>'success','request_type' => 'delete_map'), 200); 
   }
   
   //update user tracker on or off
   function update_user_tracker_status_get()
   {
       //check for required values
		if((!$this->get('tracker')) && (!$this->get('user_id'))){
			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
		} 
        
        $this->load->model(array("user_model","user_groups_model"));
        
        $user_id                = $this->get("user_id");
        $tracker                = $this->get("tracker");
        $ins_data               = array();
        $ins_data['is_tracked'] = $tracker;
        $this->user_model->update('user',$ins_data,array("id" => $user_id));
        
        $active_group = $this->user_groups_model->get_user_active_group($user_id);
        
        return $this->response(array('status' =>'success','request_type' => 'user_tracker_update', 'tracker' => $tracker, 'active_group' => $active_group['join_key'], 'group_type' => $active_group['type'], 'view' => $active_group['is_view']), 200);     
   } 
    
    //user current group active 
    function user_current_group_active_get() {
        if(!$this->get('group_id') || !$this->get('user_id')){
			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
		}  
        
         $this->load->model(array("user_groups_model","group_model"));  
         $user_id   = $this->get("user_id");
         $join_key  = $this->get("group_id"); 
         
         $result = $this->group_model->check_unique(array("join_key" => $join_key));
      
        $groups = $this->user_groups_model->get_user_groups($user_id);
       
        $ins_data  = array();
        if(count($groups)) {
            
            foreach($groups as $gkey => $gvalue) {
               
             //   $ins_data['status']  = ($gvalue['group_id']==$result['id'])?1:0;
                if($gvalue['group_id'] == $result['id']) {
                    $ins_data['user_active_time'] = date("Y-m-d H:i:s");
                    $ins_data['status'] = 1;
                    $group_id  = $gvalue['group_id'];
                }
                else
                {
                    $ins_data['user_leave_time'] = date("Y-m-d H:i:s");
                    $ins_data['status']  = 0;
                    $group_id  = $gvalue['group_id'];
                }
                $this->user_groups_model->update($ins_data,array("user_id" => $user_id, 'group_id' => $group_id));
            }
            
            return $this->response(array('status' =>'success', 'request_type' => 'current_group_active','join_key' => $join_key), 200);
        }   
  }
    
    //share map 
    function check_group_get()
    {
        //check for required values
		if(!$this->get('join_key')){
			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
		}
        $this->load->model("group_model");
        
        $join_key = str_to_lower($this->get('join_key'));
        $result   = $this->group_model->check_unique(array("join_key" => $join_key));
        
        if(count($result)) {
           return $this->response(array('status' => "success",'join_key' => $join_key), 404);
        }
        else
        {
            $ins_data = array();
            $ins_data['name']            = $join_key;
            $ins_data['type']            = 'private';
            $ins_data['join_key']        = $join_key;
            $ins_data['description']     = $join_key;
            $ins_data['location_type']   = 'mobile';
            $ins_data['user_id']         = $this->get('user_id');  
            $ins_data['date_created']    = strtotime(date('Y-m-d H:i:s'));
            $group_id =  $this->group_model->insert($ins_data); 
            return $this->response(array('status' =>'success','join_key' => $join_key, 'request_type' => 'group_creation','group_id' => $group_id), 200);
        }
            
    }  
     
    //random number generate for unique group id           
    function random_number_generation_get()
    {
        $rand = gen_random_string(6);
        
        $this->load->model("group_model");
        $result = $this->group_model->check_unique(array("join_key" => $rand));
        
        if(count($result)) {
            return $this->response(array('status' => "error",'msg' => 'Random Id already exist.','error_code' => 104), 404);
        }
        
        return $this->response(array('status' =>'success','request_type' => 'random_id_generate','random_id' => $rand), 200);     
    }
    
    //group hide or show
    function group_status_get()
    {
         if(!$this->get('group_id') && !$this->get('user_id') && !$this->get('view')){
			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
		}  
        
         $this->load->model(array("user_groups_model","group_model"));  
         $user_id   = $this->get("user_id");
         $join_key  = $this->get("group_id");
         $view      = $this->get("view"); 
         
         $result = $this->group_model->check_unique(array("join_key" => $join_key));
       
         $ins_data = array();
         $ins_data['is_view'] = ($result['type']=='private')?1:$view;
             
         $affected_rows = $this->user_groups_model->update($ins_data,array("user_id" => $user_id, 'group_id' => $result['id']));
        // echo $this->db->last_query();
         if(!empty($affected_rows)) {
            return $this->response(array('status' =>'success', 'request_type' => 'group_view_status','user_id' => $user_id, 'group_id' => $result['id'], 'view' => $ins_data['is_view']), 200);
         }
         else
         {
            return $this->response(array('status' =>'success', 'request_type' => 'group_view_status','msg' => "Already hided this group.", 'view' => $ins_data['is_view']), 202);
         }
    }
    
    function push_group_user_data_to_gcm($group_id = "",$user_id = "") { 
            
            if(empty($user_id) && empty($group_id)){
                return false;
            }
          
           if($user_id) { 
  
                $this->load->model(array("group_model","user_groups_model"));
                $user_groups = $this->group_model->get_group_users("user_groups",array("user_id" => $user_id));
                $filtered_user_ids = $filtered_group_ids = array();
                
                if(!empty($user_groups)){
                    foreach($user_groups as $row) {
                        $filtered_group_ids[] = $row['group_id'];
                    }
                }  
                
                if(!empty($filtered_group_ids)) {
                   $group_details =  $this->group_model->filter_groups($filtered_group_ids);
                   //echo $this->db->last_query();
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
            $user = array(); $i = 0;
            
            $this->load->library("GCM");
           
           
            if(!empty($group_details)){
                
                foreach($group_details as $group) {
                   
                   $user_details = $this->user_groups_model->get_user_gcm($group['id']);
                     
                    if(!empty($user_details)) {
                        
                        foreach($user_details as $ugcm){
                           
                             $gcm_id = $ugcm['gcm_id'];
                            
                            if(empty($gcm_id) || ($ugcm['is_tracked'] == 0)) {
                                continue;
                            }
                            $user_position = $this->user_position_model->get_group_active_user_position($group,$user_id);
                           
                            if(count($user_position)) {
                            
                            foreach($user_position as $pkey=>$pvalue) {    
                               //$user = $this->get_userdata($group,$pvalue,$i);
                               
                               //check group create user 
                                if($group['user_id'] == $pvalue['id'] && ($group['location_type'] == 'static')) {
                                    $lat = $group['lat'];
                                    $lon = $group['lon'];
                                } 
                                else
                                {
                                    $lat = $pvalue['lat'];
                                    $lon = $pvalue['lon'];
                                }
                                
                                //check user group status active or inactive
                                $user_position['lat']         = $lat;
                        
                                $user_info = $this->_prepare_user_details($user_position);
                                
                                if($user_info['user']['profile']) {
                                    $user[$i]['user']['profile']['id']              = $pvalue['id'];
                                    $user[$i]['user']['profile']['default_id']      = $pvalue['default_id'];
                                    $user[$i]['user']['profile']['display_name']    = $pvalue['display_name'];
                                    $user[$i]['user']['profile']['phonenumber']     = $pvalue['phonenumber'];
                                    $user[$i]['user']['profile']['email']           = $pvalue['email'];
                                    $user[$i]['user']['profile']['android_id']      = $pvalue['android_id'];
                                    $user[$i]['user']['profile']['device_id']       = $pvalue['device_id'];
                                    $user[$i]['user']['profile']['profile_image']   = $this->profile_url."/thumb_".$pvalue['id'].".jpg";
                                    $user[$i]['user']['profile']['flag']            = (($pvalue['status']==1) && ($pvalue['is_tracked']))?1:0;
                                    $user[$i]['user']['profile']['user_type']       = ($pvalue['id']==$group['user_id'])?'admin':'member';
                                }
                                if($user_info['user']['account']) {
                                    $user[$i]['user']['account']['plan_name'] = $pvalue['plan_name'];
                                } 
                               $user[$i]['user']['position']['lat'] = $lat;
                               $user[$i]['user']['position']['lon'] = $lon; 
                               $user[$i]['user']['position']['accuracy'] = $pvalue['accuracy'];
                               if($pvalue['status'] == 0) {
                                 //$user[$i]['user']['position']['updated_time'] = date("Y-m-d h:i:s", $pvalue['date_updated']);
                                 $user[$i]['user']['position']['updated_time'] = $pvalue['date_updated'];
                               }
                               $user[$i]['user']['group']['id']                = $pvalue['group_id'];
                               $user[$i]['user']['group']['join_key']          = $pvalue['group_join_key'];
                               $user[$i]['user']['group']['description']       = $pvalue['description'];
                               $user[$i]['user']['group']['visible']           = $pvalue['is_joined'];
                               $user[$i]['user']['group']['view']              = $pvalue['is_view'];
                               $user[$i]['user']['group']['last_seen_time']    = strtotime($pvalue['last_seen_time']);
                               //print_r($user);exit;
                               
                               $i++;                                   
                            } 
                            
                            $gcm_data = array();
                            $gcm_data['user_position_group'] = $user;
                            $this->gcm->send_notification(array($gcm_id),array("hmg" => $gcm_data));
                        
                          
                        }
                        else
                        {
                            return $this->response(array('status' => "error",'msg' => 'No one active in this group.','error_code' => 1), 404);
                        }
                      }  
                      return $this->response(array('status' =>'success','user_id' => $user_id), 200);
                    }
                    //else
//                    {
//                        //return $this->response(array('status' => "error",'msg' => 'No one active in this group.','error_code' => 1), 404);
//                    }
                }
            }
            else
            {
                return $this->response(array('status' => "error",'msg' => "Group doesn't exist.",'error_code' => 1), 404);
            }

        }
        
    function search_map_get($join_key = '', $user_id ='')
    {
        
        //check for required values
		if(!$this->get('join_key') && !$this->get('user_id')) {
			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
		} 
        
        $this->load->model(array("group_model","user_groups_model","user_position_model"));
        
        $join_key  = $this->get("join_key");
        $user_id   = $this->get("user_id");
        
       
        $result = $this->group_model->check_unique(array("join_key" => $join_key));
        //print_r($result);

        if(count($result)){
            if(!empty($user_id)) {
                $this->send_notification_group_owner($join_key,$user_id);
            }
            
            $res = $this->user_groups_model->check_unique(array('user_id' => $user_id, 'group_id' => $result['id']));
            $cnt    = count($res);
            
            if(empty($cnt)) {
                $ins_data = array();
                $ins_data['user_id']   = $user_id;
                $ins_data['group_id']  = $result['id']; 
                $ins_data['status']    = 1; 
                $ins_data['is_joined'] = 1; 
                $add_group             = $this->user_groups_model->insert($ins_data);
            }
        
            $this->load->library("GCM");
            $group_id = $result['id'];
            
             if(!empty($user_id)) {
                $ins_data = array();
                $ins_data['status'] = 1;
                $activate = $this->user_groups_model->update($ins_data,array("group_id" => $group_id, 'user_id' => $user_id));
             }
            //get active group user details
            $user_details = array();
            
            $user_details = $this->user_groups_model->get_user_gcm($group_id);
          
            $user_position = $user = array();
            
            if(count($user_details)){
                
                foreach($user_details as $ukey => $uvalue) {
                     $gcm_id   = $uvalue['gcm_id'];
                     
                      if(empty($gcm_id) || ($uvalue['is_tracked'] == 0)) {
                        continue;
                      }
                                                
                     $position = $this->user_position_model->get_position($result);
                    
                     $i = 0;
                     if(count($position)) {
                        
                         foreach($position as $pkey => $pvalue)
                         {
                             //$user = $this->get_userdata($result,$pvalue,$i);   
                             //check group create user 
                                if($result['user_id'] == $pvalue['id'] && ($result['location_type'] == 'static')) {
                                    $lat = $result['lat'];
                                    $lon = $result['lon'];
                                } 
                                else
                                {
                                    $lat = $pvalue['lat'];
                                    $lon = $pvalue['lon'];
                                }
                                
                                //check user group status active or inactive
                                $user_position['lat']         = $lat;
                        
                                $user_info = $this->_prepare_user_details($user_position);
                                
                                if($user_info['user']['profile']) {
                                    $user[$i]['user']['profile']['id']              = $pvalue['id'];
                                    $user[$i]['user']['profile']['default_id']      = $pvalue['default_id'];
                                    $user[$i]['user']['profile']['display_name']    = $pvalue['display_name'];
                                    $user[$i]['user']['profile']['phonenumber']     = $pvalue['phonenumber'];
                                    $user[$i]['user']['profile']['email']           = $pvalue['email'];
                                    $user[$i]['user']['profile']['android_id']      = $pvalue['android_id'];
                                    $user[$i]['user']['profile']['device_id']       = $pvalue['device_id'];
                                    $user[$i]['user']['profile']['profile_image']   = $this->profile_url."/thumb_".$pvalue['id'].".jpg";
                                    $user[$i]['user']['profile']['flag']            = (($pvalue['status']==1) && ($pvalue['is_tracked']))?1:0;
                                    $user[$i]['user']['profile']['user_type']       = ($pvalue['id']==$result['user_id'])?'admin':'member';
                                }
                                if($user_info['user']['account']) {
                                    $user[$i]['user']['account']['plan_name'] = $pvalue['plan_name'];
                                } 
                               $user[$i]['user']['position']['lat']          = $lat;
                               $user[$i]['user']['position']['lon']          = $lon;
                               $user[$i]['user']['position']['accuracy']     = $pvalue['accuracy'];
                               $user[$i]['user']['group']['id']              = $pvalue['group_id'];
                               $user[$i]['user']['group']['description']     = $pvalue['description'];
                               $user[$i]['user']['group']['join_key']        = $pvalue['group_join_key'];
                               $user[$i]['user']['group']['visible']         = $pvalue['is_joined'];
                               $user[$i]['user']['group']['view']            = $pvalue['is_view'];
                               $user[$i]['user']['group']['last_seen_time']  = strtotime($pvalue['last_seen_time']);
                               
                              // print_($user);
                            $i++;                               
                         }
                         
                         if(!empty($user_id)) {  
                            $gcm_data = array();
                            $gcm_data['user_position_group'] = $user;
                            $this->gcm->send_notification(array($gcm_id),array("hmg" => $gcm_data));  
                        }
                     } 
                }
                return $this->response(array('status' =>'success','join_key' => $join_key, 'members' => $user), 200);
            }
            else
            {
               return $this->response(array('status' => "error",'msg' => 'No one user active in this group.','error_code' => 1), 404);
            }
            
        }
        else
        {
            return $this->response(array('status' => "error",'msg' => "Group doesn't exist.",'error_code' => 1), 404);
        }
               
    }
   
   function send_notification_group_owner($join_key,$user_id)
   {
        $this->load->model(array("group_model","user_model"));
        
        $result = $this->group_model->get_group_owner_gcm($join_key);
        
        $user_data = $this->user_model->check_unique(array("id" => $user_id));
        
        $this->load->library("GCM");
        
        $gcm_id = $result['gcm_id'];
        $gcm_data = array();
        $user_data['default_id'] = (!empty($user_data['default_id']))?$user_data['default_id']:$user_data['phonenumber'];
        $gcm_data['join'] = array("msg" => $user_data['default_id'].' is going to join your group');
        
        $this->gcm->send_notification(array($gcm_id),array("hmg" => $gcm_data)); 
        
   }
   
   
   // Delete all Group members 
   function delete_group_members_get()
   {
    
        //check for required values
		if(!$this->get('join_key') && !$this->get('user_id')) {
			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
		}
        
        $this->load->library("GCM");
        
        $user_id  = $this->get('user_id');
        $join_key = $this->get('join_key');
        
        $this->load->model(array("group_model","user_groups_model","user_model"));
        
        $result = $this->group_model->check_unique(array("user_id" => $user_id, "join_key" => $join_key));
        
        
        if(count($result)) {
            
            $members = $this->user_groups_model->get_groups_member($result['id'], $result['user_id']);
            
            $ins_data = array();
            $ins_data['status'] = 1;
            
            foreach($members as $mkey => $mvalue) {
              
                if(!empty($mvalue['userId'])) {
                    
                    $this->user_groups_model->delete(array("group_id" => $result['id'], "user_id" => $mvalue['userId']));
                    
                    $status = $this->user_groups_model->check_unique(array("user_id" => $mvalue['userId'], 'status' => 1));
                  
                    if(!count($status)) {
                    
                        //make the user default map is active
                        $get_group_name = $this->user_model->get_defult_group($mvalue['userId']);
                        
                        $this->group_model->update($ins_data,array("id" => $get_group_name['id']));
                        
                        $this->user_groups_model->update($ins_data,array("group_id" => $get_group_name['id'],"user_id" => $mvalue['userId']));
                    }
                }
                
                $gcm_id   = $mvalue['gcm_id'];
                $gcm_data = array();
                
                $msg = "Your are disconnected from this group (".$join_key.")";
                $gcm_data['disconnect'] = array("msg" => $msg);
                
                $this->gcm->send_notification(array($gcm_id),array("hmg" => $gcm_data)); 
             
            }
             
            
            if($delete) {
                return $this->response(array("status" => 'success', 'group_id' => $result['id'], 'join_key' => $result['join_key']),200);
            }
        }
        else
        {
            return $this->response(array('status' => "error",'msg' => "Group doesn't exist.",'error_code' => 1), 404);
        }
   }
   
   function group_members_get()
   {
        //check for required values
		if(!$this->get('join_key')) {
			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
		}
        
        $this->load->model(array("group_model"));
        
        $join_key = $this->get("join_key");
        
        $group_members = $this->group_model->get_group_members($join_key);
       // echo $this->db->last_query();
       // print_r($group_members);
        $users = array();
        
        if(count($group_members)) {
            $i = 0;
            foreach($group_members as $gkey => $gvalue)
            {
                $users[$i]['user_id']         = $gvalue['id'];
                $users[$i]['default_id']      = $gvalue['default_id'];
                $users[$i]['display_name']    = $gvalue['display_name'];
                $users[$i]['phonenumber']     = $gvalue['phonenumber'];
                $users[$i]['profile_image']   = $this->profile_url."/thumb_".$gvalue['id'].".jpg";
                $users[$i]['track']           = $gvalue['is_tracked'];
                $users[$i]['join_key']        = $gvalue['join_key'];
                $users[$i]['description']     = $gvalue['description'];
                $users[$i]['group_id']        = $gvalue['gid'];
                $users[$i]['lat']             = $gvalue['lat'];
                $users[$i]['lon']             = $gvalue['lon'];
                $users[$i]['accuracy']        = $gvalue['accuracy'];
            
                $i++;
            }
            
            return $this->response(array("status" => 'success', 'members' => $users),200);
        }
        else
        {
            return $this->response(array('status' => "error",'msg' => "Member doesn't exist.",'error_code' => 102), 404);
        }    
   }
   
    //Delete one member of the group 
   function delete_member_get()
   {
       //check for required values
		if(!$this->get('group_id') && !$this->get('user_id')) {
			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
		}
        
        $this->load->model(array("user_groups_model","user_model","group_model"));
        
        $this->load->library("GCM");
        
        $group_id  = $this->get('group_id');
        $user_id   = $this->get('user_id');
        
        $this->user_groups_model->delete(array('group_id' => $group_id, 'user_id' => $user_id));
        
        
        //check if member active group exist or not
        $status = $this->user_groups_model->check_unique(array("user_id" => $user_id, 'status' => 1));
        
        $ins_data = array();
        $ins_data['status'] = 1;
            
            
        if(!count($status)) {
           //make the user default map is active
            $get_group_name = $this->user_model->get_defult_group($user_id);
            
            $this->group_model->update($ins_data,array("id" => $get_group_name['id']));
            
            $this->user_groups_model->update($ins_data,array("group_id" => $get_group_name['id'],"user_id" => $user_id));
         }
         
         //send notification to delete member
         $user_data = $this->user_model->check_unique(array("id" => $user_id));
            
        if(count($user_data)) {
                $gcm_id   = $user_data['gcm_id'];
                $gcm_data = array();
                $group_name = $this->group_model->check_unique(array("id" => $group_id));
                $msg = "Your are disconnected from this group (".$group_name['join_key'].")";
                $gcm_data['disconnect'] = array("msg" => $msg);
                
                $this->gcm->send_notification(array($gcm_id),array("hmg" => $gcm_data));
        }
        
        return $this->response(array("status" => 'success'),200);    
   } 
   
   //Get Particular user information like position,profile,account and group
   
   function get_user_information_get()
   {
    
         //check for required values
		if(!$this->get('join_key') && !$this->get('user_id')) {
			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
		}
        
        $this->load->model("user_groups_model");
        
        $join_key = $this->get("join_key");
        $user_id  = $this->get("user_id");
        
        $user_info = $this->user_groups_model->get_user_info($join_key,$user_id);
        
        if(count($user_info)) {
           return $this->response(array("status" => 'success','user' => $user_info),200);     
        }
        else
        {
            return $this->response(array('status' => "error",'msg' => 'Invalid Details','error_code' => 101), 404);
        }
   }
   
   //Update user display name
   function update_display_name_get()
   {
         //check for required values
		if(!$this->get('user_id')) {
			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
		}
        
         $this->load->model("user_model");
        
         $user_id      = $this->get("user_id");
         $display_name = $this->get("display_name"); 
         
         $ins_data = array();
         $ins_data['display_name'] = $display_name;
         
         $this->user_model->update("user",$ins_data,array("id" => $user_id));
         
        return $this->response(array("status" => 'success','user_id' => $user_id),200);   
   }
   
   //username send to user email
   function username_recovery_get()
   {
    
         //check for required values
		if(!$this->get('email')) {
			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
		}
        
        $this->load->model("user_model");
        
        $email = $this->get("email");
        
        $result = $this->user_model->check_unique(array("email" => $email));
        
         if(count($result)) {
            
             $username = $result['default_id'];
             $message  = "<p>Hi $username,</p><br /><br /><br />";
             $message .= "Please see below your username. <br /><br />";
             $message .= "<b>Username</b> :".$username;
             $message .= "<br /><br /><br /><br /><br />";
             $message .= "<p>Thanks,<p>";
             $message .= "<p><a href='http://heresmygps.com/'>Heresmygps.com</a></p>"; 
              
             $config['charset'] = 'iso-8859-1';
             $config['wordwrap'] = TRUE;
             $config['mailtype'] = 'html';
             $this->load->library('email');
             $this->email->set_newline("\r\n");
             $this->email->initialize($config);
             
             $this->email->from('hmgps@gmail.com', 'HMGPS');
             $this->email->to($email);
             $this->email->subject('Username Recovery');
             $this->email->message($message);
             $this->email->send();
            
            return $this->response(array('status' =>'success','request_type' => 'username_recovery','email' => $email,'user_id' => $result['id']), 200);        
        }
        else
        {
            return $this->response(array('status' => "error",'request_type' => 'username_recovery','msg' => 'Invalid Mail ID.','error_code' => 11), 404);
        }
   }
   
}
?>
