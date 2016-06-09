<?php
require APPPATH.'/libraries/REST_Controller.php';

class Service extends REST_Controller
{

        function modify_location_get()
    	{
    		//check for required values
    		if((!$this->get('lat'))|| (!$this->get('lon')) || (!$this->get('type'))|| (!$this->get('user_key')))
    		{
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            
            $ins_data = array();
            $ins_data['lat'] = $this->get('lat');
            $ins_data['lon'] = $this->get('lon');
    		
            $user_id    = $this->get('user_id');
            $group_type = $this->get('type');
            
            $user_key   = $this->get('user_key');
            $user_key   = strtolower($user_key);
      
            $this->db->where('user_key',$user_key);
            $result = $this->db->get('location')->row_array(); 
            
            if(count($result)) {
              //  return $this->response(array('status' => "error",'msg' => 'User key already exists.','error_code' => 12), 404);
                $this->db->update('location',array('lat' => $ins_data['lat'], 'lon' => $ins_data['lon']),array('user_key' => $user_key));
                $insert_id = $result['id'];
            }
            else
            {
              $ins_data['user_key'] = $user_key;  
     	      $this->db_model->insert('location',$ins_data);
              $insert_id = $this->db->insert_id();
            } 
            
    		
            if($insert_id)
    		{
    		   $response = array();
               
               $unique_location_link_and_key = unique_location_link_and_key_generate($this->get('user_key'));
               
               if(!empty($user_id) && !empty($group_type)) { 
                    $group = $this->create_group($unique_location_link_and_key['key'],$user_id,$group_type);
                    if(!empty($group)){
                        $unique_location_link_and_key['group_id'] = $group['group_id'];
                    } 
               }
                
   			   return $this->response($unique_location_link_and_key, 200);
                
    		}
            else
            {
                return $this->response(array('status' => "error",'msg' => 'Unknown Error Occurred!! Try Again...','error_code' => 2), 404);
                	
            }
    		
    	}
        function create_group($join_key = '', $user_id = '', $group_type) 
        {  
            $res = array();
            $join_key = str_to_lower($join_key); 
            $this->db->where('lower(join_key)',$join_key);
            $result = $this->db->get('groups')->num_rows();
            
            if($result > 0) {
               // $this->modify_location_get();
                return false;
            }
            else
            {
                $this->db->insert('groups',array('name' => $join_key,'type'=> $group_type,'join_key' => $join_key,'user_id' => $user_id,'date_created' => strtotime(date('Y-m-d h:i:s'))));
                $group_id =  $this->db->insert_id();
                if($group_id) {
                	$res['group_id'] = $group_id;
                    return $res;
              }
            }
            
            
        }
        function location_from_key_get()
    	{
    		//check for required values
    		if((!$this->get('key')))
    		{
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            
            $key =  $this->get('key');
            
            $key = hexdec($key);
        
            $result = $this->db->get_where('location',array('user_key' => $key))->row_array();
                
            if(!empty($result))
    		{
    		     $lon = $result['lon'];
                 $lat = $result['lat'];
                 $key = $result['user_key'];
                 
    			return $this->response(array('status' =>'success','lat' => $lat,'lon' => $lon, 'key' => $key), 200);
    		}
            else
            {
                return $this->response(array('status' => "error",'msg' => 'Invalid key.','error_code' => 4), 404);
                	
            }
    		
    	}
        
        function register_app_get() {
            
            //check for required values
            $app_id = $this->get('app_id');
    		if( empty($app_id) )
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
               // return $this->response(array('status' => "success",'msg' => 'APP ID Already Exist.','error_code' => 3), 404);
                	
            }
            
        }
        
        function user_profile_save_get() {
            
            //check for required values
    		if((!$this->get('default')) && (!$this->get('custom')) && (!$this->get('random')) && (!$this->get('phonenumber') && (!$this->get('gcm_id') ) && (!$this->get('email')))) 
    		{
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            
            $default_id = $this->get('default');
            $custom_id = $this->get('custom');
            $random_id = $this->get('random');
            $phonenumber = $this->get('phonenumber');
            $user_id = $this->get('user_id');
            //$gcm_id = $this->get('gcm_id');
            $email  = $this->get('email');
            
           
           if($email) {
              //Email check
              if($user_id)
                    $this->db->where('id != ',$user_id);
              $this->db->where('lower(email)',str_to_lower($email));
              $result = $this->db->get('user')->num_rows();
              
              if(!empty($result)) {
                return $this->response(array('status' => "error",'msg' => 'Email already exist.','error_code' => 112), 404);
              }
           }
           
           if($default_id){
               //default ID check
               if($user_id)
                    $this->db->where('id != ',$user_id);
                    
               $this->db->where('lower(default_id)',str_to_lower($default_id));
               $result =  $this->db->get('user')->num_rows();
                
                if(!empty($result)) {
                    return $this->response(array('status' => "error",'msg' => 'Default ID already exist.','error_code' => 103), 404);
                }
            }
            
            if($custom_id) {
                //Custom ID Check
                
                  if(!empty($user_id))
                    $user_idd = "and id!='$user_id'";
                    
                 $result = $this->db->query("select * from user where (default_id='".$custom_id."' or custom_id='".$custom_id."' or random_id='".$custom_id."' or phonenumber='".$custom_id."') $user_idd ")->num_rows();
                
                if(!empty($result)) {
                    return $this->response(array('status' => "error",'msg' => 'Custom ID already exist.','error_code' => 104), 404);
                }
            }
            
            if($random_id) {
                //random ID check
               
                if($user_id)
                    $this->db->where('id != ',$user_id);
                    
                $this->db->where('lower(random_id)',str_to_lower($random_id));    
                $result =  $this->db->get('user')->num_rows();
             
                if(!empty($result)) {
                    return $this->response(array('status' => "error",'msg' => 'Random ID already exist.','error_code' => 105), 404);
                }
            }
            
            if($phonenumber){
                //phone number check
                 if($user_id)
                    $this->db->where('id != ',$user_id);
                
                $this->db->where('phonenumber',$phonenumber);    
                $result =  $this->db->get('user')->num_rows();
                
                if(!empty($result)) {
                    return $this->response(array('status' => "error",'msg' => 'Phonenumber already exist.','error_code' => 106), 404);
                }
            }
             
            if($user_id){
                 $this->db->update('user',array('default_id' => $default_id,'custom_id'=>$custom_id,'random_id' => $random_id,'phonenumber' => $phonenumber,'date_updated' => strtotime(date('Y-m-d h:i:s'))),array('id' => $user_id));
           
            }
            else
            {
                $this->db->insert('user',array('default_id' => $default_id,'custom_id'=>$custom_id,'random_id' => $random_id,'phonenumber' => $phonenumber,'gcm_id' => $gcm_id,'date_created' => strtotime(date('Y-m-d h:i:s')),'email' => $email));
                $user_id = $this->db->insert_id();
            }    
            
            if($user_id) {
                return $this->response(array('status' =>'success','user_id' => $user_id, 'default_id' => $default_id, 'custom_id'=>$custom_id, 'random_id' => $random_id, 'phonenumber' => $phonenumber), 200);
            }
            else
            {
                return $this->response(array('status' => "error",'msg' => 'Unknown Error Occurred! Try Again...','error_code' => 2), 404);
            }
        }
        
        function user_profile_get()
    	{
    		//check for required values
    		if((!$this->get('user_id')))
    		{
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            
            $user_id =  $this->get('user_id');
            
        
            $result = $this->db->get_where('user',array('id' => $user_id))->row_array();
                
            if(!empty($result))
    		{
    		     $default_id = $result['default_id'];
                 $random_id = $result['random_id'];
                 $custom_id = $result['custom_id'];
                 $phonenumber = $result['phonenumber'];
    
    			return $this->response(array('status' =>'success','default_id' => $default_id,'custom_id'=>$custom_id,'random_id' => $random_id,'phonenumber' => $phonenumber), 200);
    		}
            else
            {
                return $this->response(array('status' => "error",'msg' => 'Invalid User ID.','error_code' => 11), 404);
                	
            }
    		
    	}
        
        function login_get() {
            
            $val    = $this->get('val');
            
            //check for required values
    		if($val=='')
    		{
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            
            $result = $this->db->query("select * from user where default_id='".$val."' or custom_id='".$val."' or random_id='".$val."' or phonenumber='".$val."'")->row_array();  
           
            if(isset($result['id'])) {
                 $default_id        = (!empty($result['default_id']))?$result['default_id']:"";
                 $random_id         = (!empty($result['random_id']))?$result['random_id']:"";
                 $custom_id         = (!empty($result['custom_id']))?$result['custom_id']:"";
                 $phonenumber       = (!empty($result['phonenumber']))?$result['phonenumber']:"";
                 $profile_image     = (isset($result['profile_image']) && ($result['profile_image']!=''))?$result['profile_image']:'http://heresmygps.com/assets/images/no_image.png';
                 $email             = (!empty($result['email']))?$result['email']:"";
                 $android_id        = (!empty($result['android_id']))?$result['android_id']:"";
                 $device_id         = (!empty($result['device_id']))?$result['device_id']:"";
    
                return $this->response(array('status' =>'success','default_id' => $default_id,'custom_id'=>$custom_id,'random_id' => $random_id,'phonenumber' => $phonenumber,'user_id' => $result['id'],'email' => $email,'profile_image' => $profile_image,'android_id' => $android_id,'device_id' => $device_id), 200);
            }
            else
            {
                return $this->response(array('status' => "error",'msg' => 'Login faild.','error_code' => 108), 404);
            }
        }
        
        
        function social_login_get(){
            
            //check for required values
            $email   = $this->get('email');
            $user_id = $this->get('user_id');
            
    		if(!$this->get('email') || !$this->get('user_id'))
    		{
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            if(!empty($user_id)) {
                $where = array("id" => $user_id);
            }
            $where = array("email" => $email);
            $result = $this->db->get_where('user',$where)->row_array();
            
            if(!empty($result))  {
                
                 $default_id        = (!empty($result['default_id']))?$result['default_id']:"";
                 $random_id         = (!empty($result['random_id']))?$result['random_id']:"";
                 $custom_id         = (!empty($result['custom_id']))?$result['custom_id']:"";
                 $phonenumber       = (!empty($result['phonenumber']))?$result['phonenumber']:"";
                 $profile_image     = (isset($result['profile_image']) && ($result['profile_image']!=''))?$result['profile_image']:'http://heresmygps.com/assets/images/no_image.png';
                 $email             = (!empty($result['email']))?$result['email']:"";
                 $android_id        = (!empty($result['android_id']))?$result['android_id']:"";
                 $device_id         = (!empty($result['device_id']))?$result['device_id']:"";
                 $user_id           = (!empty($result['id']))?$result['id']:"";
               
    
    			return $this->response(array('status' =>'success','default_id' => $default_id,'custom_id'=>$custom_id,'random_id' => $random_id,'phonenumber' => $phonenumber,'user_id' => $result['id'],'email' => $email,'profile_image' => $profile_image,'android_id' => $android_id,'device_id' => $device_id), 200);
            }else
            {
                
                $default_id = $random_id = $custom_id = $phonenumber = $profile_image = $android_id = $device_id = "";
                
              if($this->get('email')){
                   //default ID check
                   $this->db->where('lower(email)',str_to_lower($this->get('email')));
                   $result =  $this->db->get('user')->num_rows();
                    
                    if(empty($result)) {
                       $email = $this->get('email');
                    }
                    else
                    {
                         return $this->response(array('status' => "error",'msg' => 'Invaid key.','error_code' => 107), 404);
                    }
                }
               if($this->get('default')){
                   //default ID check
                   $this->db->where('lower(default_id)',str_to_lower($this->get('default')));
                   $result =  $this->db->get('user')->num_rows();
                    
                    if(empty($result)) {
                       $default_id = $this->get('default');
                    }
                }
                
                if($this->get('custom')) {
                    //Custom ID Check
                    $custom = $this->get('custom');
                    $result = $this->db->query("select * from user where default_id='".$custom."' or custom_id='".$custom."' or random_id='".$custom."' or phonenumber='".$custom."'")->num_rows();
                    
                    if(empty($result)) {
                        $custom_id = $this->get('custom');
                    }
                }
                
                if($this->get('random')) {
                    //random ID check
                    $this->db->where('lower(random_id)',str_to_lower($this->get('random')));    
                    $result =  $this->db->get('user')->num_rows();
                    
                    if(empty($result)) {
                        $random_id = $this->get('random');
                    }
                }
                
                if($this->get('phonenumber')){
                    //phone number check
                    $this->db->where('phonenumber',$this->get('phonenumber'));    
                    $result =  $this->db->get('user')->num_rows();
                    
                    if(empty($result)) {
                        $phonenumber = $this->get('phonenumber');
                    }
                }
               
               if($this->get('profile_image')) {
                    // profile image check
                    $this->db->where('profile_image',$this->get('profile_image'));
                    $result = $this->db->get('user')->num_rows();
                    if(empty($result)) {
                        $profile_image = $this->get('profile_image');
                     }
               }
               
                $user_type = $this->get('user_type');
               
                $insert_data = array('default_id' => $default_id,'custom_id'=>$custom_id,'random_id' => $random_id,'phonenumber' => $phonenumber,'email' => $email,'user_type' => $user_type,'date_created' => strtotime(date('Y-m-d h:i:s')), 'profile_image' => $profile_image);
              
                 $this->db->insert('user',$insert_data);
                 
                 $user_id =  $this->db->insert_id();
                 
                 if(!$user_id) {
                    return $this->response(array('status' => "error",'msg' => 'Unknown Error Occurred!! Try Again...','error_code' => 2), 404);
                 }
            }
            
            return $this->response(array('status' =>'success','default_id' => $default_id,'custom_id'=>$custom_id,'random_id' => $random_id,'phonenumber' => $phonenumber,'user_id' => $user_id, 'profile_image' => $profile_image, 'email' => $email), 200);
            
        }
        
        function group_save_get() {
           
            //check for required values
    		if(!$this->get('name') || !$this->get('type')|| !$this->get('user_id')|| !$this->get('join_key')  )
    		{
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            
            $group_id = $this->get('group_id');
            
            $join_key = $this->get('join_key');
            
            $this->db->where('lower(join_key)',str_to_lower($join_key));
         
            if($group_id)
             $this->db->where('id !=',$this->get('group_id'));
             
            $result = $this->db->get('groups')->num_rows();


            if($result > 0) {
                return $this->response(array('status' => "error",'msg' => 'Join key already exist.','error_code' => 109), 404);
            }
          

            if($group_id) { 
                $this->db->update('groups',array('name' => $this->get('name'),'type'=>$this->get('type'),'join_key' => $this->get('join_key'),'user_id' => $this->get('user_id'),'date_updated' => strtotime(date('Y-m-d h:i:s'))),array('id' => $group_id));
            }else
            {
                $this->db->insert('groups',array('name' => $this->get('name'),'type'=>$this->get('type'),'join_key' => $this->get('join_key'),'user_id' => $this->get('user_id'),'date_created' => strtotime(date('Y-m-d h:i:s'))));
                $group_id =  $this->db->insert_id();
             
            }
            
              if($group_id) {
                	
                    return $this->response(array('status' =>'success','group_id' => $group_id), 200);
              }
              else
              {
                    return $this->response(array('status' => "error",'msg' => 'Unknown Error Occurred!! Try Again...','error_code' => 2), 404);
              }
            
            
        }
        
        function group_list_get(){
            
            //check for required values
    		if(!$this->get('user_id'))
    		{
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            
            $result = $this->db->get_where('groups',array('user_id' => $this->get('user_id')))->result_array();
            
            return $this->response(array('status' =>'success','list' => $result), 200);
            
        }
        
        function user_position_save_get() {
            
          //check for required values
    		if(!$this->get('user_id') || !$this->get('lat') || !$this->get('lon'))
    		{
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            
            $user_id = $this->get('user_id');
            
            $result = $this->db->get_where('user_position',array('user_id' => $user_id))->num_rows();  
            
        
            if($result > 0) {
                $this->db->update('user_position',array('lat' => $this->get('lat'),'lon'=>$this->get('lon'),'date_updated' => strtotime(date('Y-m-d h:i:s'))),array('user_id' => $user_id)); 
            }
            else
            {
                $this->db->insert('user_position',array('user_id' => $this->get('user_id'),'lat' => $this->get('lat'),'lon'=>$this->get('lon'),'date_created' => strtotime(date('Y-m-d h:i:s'))));
            }
            
                	
           $this->push_group_user_data_to_gcm("",$user_id);
           $this->push_tracked_user_data_to_gcm($user_id);
           return $this->response(array('status' =>'success'), 200);
        }
        
        function join_group_get() {
             
           
            //check for required values
    		if(!$this->get('join_key')|| !$this->get('user_id'))
    		{
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            
            $join_key = str_to_lower($this->get('join_key'));
            $user_id = $this->get('user_id');
            
            $wir = array('lower(join_key)' => $join_key);
            $this->db->where($wir,false);
            $result = $this->db->get('groups')->row_array();
          
            
            if(empty($result)) {
                 return $this->response(array('status' => "error",'msg' => 'Invalid Join key','error_code' => 110), 404);
            }
           
            $user_groups_res = $this->db->get_where('user_groups',array('user_id' => $user_id, 'group_id' => $result['id']))->num_rows();
            
            if(empty($user_groups_res)) {
                $this->db->insert('user_groups',array('user_id' => $user_id,'group_id' => $result['id']));
            }
            
            $this->push_group_user_data_to_gcm($result['id'],$user_id); 
            
            return $this->response(array('status' =>'success', 'join_key' => $join_key, 'group_name' => $result['name']), 200);
             
        }
        
        function push_group_user_data_to_gcm($group_id = "",$user_id = "") { 
            
            if(empty($user_id) && empty($group_id)){
                return false;
            }
            
           // if($group_id) { 
//                $group_details = $this->db->get_where('groups',array('id' => $group_id))->result_array();
//            }
//	       else 
           if($user_id) { 
                
           //     if($group_id) {
//                    $this->db->where('group_id' , $group_id);
//                }
//                else
//                {
                     $this->db->where('user_id', $user_id);
               // }
                $user_groups = $this->db->get('user_groups')->result_array();
                
                $filtered_user_ids = $filtered_group_ids = array();
                
                if(!empty($user_groups)){
                    foreach($user_groups as $row) {
                        //$filtered_user_ids[] = $row['user_id'];
                        $filtered_group_ids[] = $row['group_id'];
                    }
                }
                
                
                if(!empty($filtered_group_ids)) {
                    $this->db->where_in('id',$filtered_group_ids);
                    $group_details = $this->db->get('groups')->result_array();
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

            $this->load->library("GCM");
            if(!empty($group_details)){
                
                foreach($group_details as $group) {
                    
                    $this->db->select("user.gcm_id,user.id");
                    if($group['type'] == 'single' || empty($group_id))
                        $this->db->where('user_groups.user_id !=',$user_id);
                     
                    $this->db->where('user_groups.group_id',$group['id']);  
                    $this->db->join('user','user.id= user_groups.user_id and user.status=1');
                    $user_details = $this->db->get('user_groups')->result_array();

                    if(!empty($user_details)) {
                        foreach($user_details as $user){
                            
                            $gcm_id = $user['gcm_id'];
                            
                            if(empty($gcm_id)) {
                                continue;
                            }
                             //print_r($group['join_key']);exit;
                            $this->db->select('user.default_id,user.custom_id,user.random_id,"'.$group['join_key'].'" as group_join_key,user.phonenumber,user_position.lat,user_position.lon',false);
                            $this->db->where('user_groups.group_id',$group['id']);
                            $this->db->where('user_groups.user_id !=',$user_id);
                            $this->db->join('user_position','user_groups.user_id=user_position.user_id');
                            $this->db->join('user','user.id=user_position.user_id and user.status=1');

                            //if($group['type'] == 'single')
                            // $this->db->where('user_position.user_id',$group['user_id']);  
                                 
                            $user_position = $this->db->get('user_groups')->result_array();
                        //    echo "<pre>";
///echo $this->db->last_query();
	//	print_r($user_position);die;
                            $data = array();
                            $data['user_position_group'] = $user_position;
                      //echo $gcm_id;
                    //  print_r($data);
                           $this->gcm->send_notification(array($gcm_id),array("hmg" => $data));
                        }
                    }
                }
            }

        }
        
        function user_profile_save_post() {
            
               //check for required values
    		if((!$this->post('default')) && (!$this->post('custom')) && (!$this->post('random')) && (!$this->post('phonenumber') && (!$this->post('gcm_id') )) && (!$this->post('email'))) 
    		{
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            
            $default_id = $this->post('default');
            $custom_id = $this->post('custom');
            $random_id = $this->post('random');
            $phonenumber = $this->post('phonenumber');
            $user_id = $this->post('user_id');
            $gcm_id = $this->post('gcm_id');
            $email  = $this->post('email');
            
            if($email) {
              //Email check
              if($user_id)
                    $this->db->where('id != ',$user_id);
              $this->db->where('lower(email)',str_to_lower($email));
              $result = $this->db->get('user')->num_rows();
              
              if(!empty($result)) {
                return $this->response(array('status' => "error",'msg' => 'Email already exist.','error_code' => 112), 404);
              }
           }
           
           if($default_id){
               //default ID check
               if($user_id)
                    $this->db->where('id != ',$user_id);
                    
               $this->db->where('lower(default_id)',str_to_lower($default_id));
               $result =  $this->db->get('user')->num_rows();
                
                if(!empty($result)) {
                    return $this->response(array('status' => "error",'msg' => 'Default ID already exist.','error_code' => 103), 404);
                }
            }
            
            if($custom_id) {
                //Custom ID Check
                
                 if($user_id)
                     $user_id = "id!='$user_id'";
                    
                 $result = $this->db->query("select * from user where (default_id='".$custom_id."' or custom_id='".$custom_id."' or random_id='".$custom_id."' or phonenumber='".$custom_id."') and $user_id ")->num_rows();
                    
                
                if(!empty($result)) {
                    return $this->response(array('status' => "error",'msg' => 'Custom ID already exist.','error_code' => 104), 404);
                }
            }
            
            if($random_id) {
                //random ID check
               
                if($user_id)
                    $this->db->where('id != ',$user_id);
                    
                $this->db->where('lower(random_id)',str_to_lower($random_id));    
                $result =  $this->db->get('user')->num_rows();
                
                if(!empty($result)) {
                    return $this->response(array('status' => "error",'msg' => 'Random ID already exist.','error_code' => 105), 404);
                }
            }
            
            if($phonenumber){
                //phone number check
                 if($user_id)
                    $this->db->where('id != ',$user_id);
                
                $this->db->where('phonenumber',$phonenumber);    
                $result =  $this->db->get('user')->num_rows();
                
                if(!empty($result)) {
                    return $this->response(array('status' => "error",'msg' => 'Phonenumber already exist.','error_code' => 106), 404);
                }
            }
             
            if($user_id){
                 $this->db->update('user',array('gcm_id' => $gcm_id,'date_updated' => strtotime(date('Y-m-d h:i:s'))),array('id' => $user_id));
            }
            else
            {
                
                $this->db->insert('user',array('default_id' => $default_id,'custom_id'=>$custom_id,'random_id' => $random_id,'phonenumber' => $phonenumber,'gcm_id' => $gcm_id,'date_created' => strtotime(date('Y-m-d h:i:s')),'email' => $email));
                $user_id = $this->db->insert_id();
            }    
            
            
            if($user_id) {
                return $this->response(array('status' =>'success','user_id' => $user_id, 'default_id' => $default_id, 'custom_id'=>$custom_id, 'random_id' => $random_id, 'phonenumber' => $phonenumber), 200);
            }
            else
            {
                return $this->response(array('status' => "error",'msg' => 'Unknown Error Occurred!! Try Again...','error_code' => 2), 404);
            }
        }
        
        function track_user_get(){
            
            
             //check for required values
    		if(!$this->get('val')|| !$this->get('user_id') ||  !$this->get('end_time') ) 
    		{
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            
            $tracker_user_details = $this->db->get_where("user",array('id' => $this->get('user_id')))->row_array();
            
            if(empty($tracker_user_details) )
            {
                 return $this->response(array('status' => "error",'msg' => 'Invalid User ID.','error_code' => 11), 404);
            }
            
            $tracked_user_details = $this->db->query("select * from user where lower(default_id) ='".str_to_lower($this->get('val'))."' OR lower(random_id) ='".str_to_lower($this->get('val'))."' OR lower(custom_id) ='".str_to_lower($this->get('val'))."' OR phonenumber ='".$this->get('val')."'")->row_array();
           
       
            if(empty($tracked_user_details)) {
              return $this->response(array('status' => "error",'msg' => 'Invalid User details.','error_code' => 111), 404);
            } 
            
            
            //check tracked user time
            
            /*$tracked_time = $this->db->query("select * from single_user_track where tracked_user_id='".$tracked_user_details['id']."'")->row_array();
            $current_time = strtotime(date("Y-m-d H:i:s"));
            $minutes      = round(abs($current_time - $tracked_time['end_time'])/60,2); 
            
            if(count($tracked_time) && ($minutes > 0)) {
               return $this->response(array('status' => "error",'msg' => "Please wait for $minutes minutes or Join user group to track this user.",'error_code' => 4), 404); 
            }
            else
            {
                 $this->db->query("delete from single_user_track where id='".$tracked_time['id']."'"); 
            }
            */
            //$track_rec = $this->db->get_where("single_user_track",array("user_id" => $this->get('user_id')))->row_array();
            
            
            $uid         = $this->get('user_id');
            
            $track_rec   = $this->db->query("select * from single_user_track where user_id='".$uid."' or tracked_user_id='".$uid."'")->row_array();
           // echo $this->db->last_query();
          
            $this->load->library("GCM");
             
            if(!empty($track_rec)) {
                
                if(($tracked_user_details['id'] == $track_rec['tracked_user_id']) || ($tracked_user_details['id'] == $track_rec['user_id'] )) {
                    $update_data   = array("end_time" => $this->get('end_time'),"approved" => 1,'date_updated' => strtotime(date('Y-m-d h:i:s')));
                }
                else
                {
                    $update_data   = array("end_time" => $this->get('end_time'),"tracked_user_id" => $tracked_user_details['id'],"approved" => 1,'date_updated' => strtotime(date('Y-m-d h:i:s')));
                } 
                //$udate_data = array("end_time" => $this->get('end_time'),"approved" => 1,'date_updated' => strtotime(date('Y-m-d h:i:s')));
                $this->db->update("single_user_track",$update_data,array("id" => $track_rec['id']));
                $insert_id = $track_rec['id'];
            }
            else
            {
                $data = array("user_id" =>$this->get('user_id'),"tracked_user_id" => $tracked_user_details['id'], "end_time" => $this->get('end_time'),"approved" => 1,'date_created' => strtotime(date('Y-m-d h:i:s')));
                $this->db->insert("single_user_track",$data);
                $insert_id = $this->db->insert_id();
            }
            
            if($insert_id) {
                 $gcm_data = array();
                $msg = "Phone number {$tracker_user_details['phonenumber']} wanna track your location.";
                $gcm_data['single_track_notification'] = array("msg" => $msg,"single_track_id" => $insert_id);
               
               //echo $tracked_user_details['gcm_id']; exit;
               $gcm_id = $tracked_user_details['gcm_id'];
                $this->gcm->send_notification(array("$gcm_id"),array("hmg" => $gcm_data));
               
                $this->push_tracked_user_data_to_gcm($tracked_user_details['id']);
                return $this->response(array('status' =>'success','single_track_id' => $insert_id), 200);
            }
            else
            {
                return $this->response(array('status' => "error",'msg' => 'Unknown Error Occurred!! Try Again...','error_code' => 2), 404);
            }

        }
        
        function single_track_delete_get(){
            
            //check for required values
    		if(!$this->get('single_track_id')) 
    		{
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            
            $this->db->delete('single_user_track',array('id' =>$this->get('single_track_id')));
            
             return $this->response(array('status' =>'success'), 200);
            
            
        }
        
        function track_approve_get() {
            
            //check for required values
    		if(!$this->get('single_track_id') || !$this->get('approve_status')) 
    		{
    			return $this->response(array('status' => 'error','msg' => 'Required fields missing in your request','error_code' => 1), 404);
    		}
            
            $this->db->update("single_user_track",array("approved" => $this->get('approve_status')),array("id" => $this->get('single_track_id')));
            
            return $this->response(array('status' =>'success'), 200);
           // $this->push_tracked_user_data_to_gcm();
        }
        
        function push_tracked_user_data_to_gcm($user_id = "") {
           
            $this->load->library("GCM");
            $this->db->where('approved',1);
            if(!empty($user_id))
                $this->db->where('tracked_user_id',$user_id);
                
            $track_records = $this->db->get('single_user_track')->result_array();
          
            if(!empty($track_records)) {
                
                foreach($track_records as $rec) {
                   
                    $this->db->select("gcm_id");
                    $tracker_user_details = $this->db->get_where("user",array("id" =>$rec['user_id'] ))->row_array();
                    

                    $this->db->select("gcm_id");
                    $tracked_user_details = $this->db->get_where("user",array("id" =>$rec['tracked_user_id'] ))->row_array();
                    
                    
                    $this->db->select('user.default_id,user.custom_id,user.random_id,user.id,user.phonenumber,user_position.lat,user_position.lon');
                    $this->db->where('user.id',$rec['tracked_user_id']);
                    $this->db->join('user_position','user.id=user_position.user_id');
                    
                     $user_position = $this->db->get('user')->result_array();
                     
                     //tracked user position
                     
                      $gcm_data = array();
                     if(!empty($user_position)) {
                     
                        $gcm_data['user_position'] = $user_position;
                     }
                 
                    $this->db->select('user.default_id,user.custom_id,user.random_id,user.id,user.phonenumber,user_position.lat,user_position.lon');
                    $this->db->where('user.id',$rec['user_id']);
                    $this->db->join('user_position','user.id=user_position.user_id');
                   // if($user_id){
//                         $this->db->where('user.id',$user_id);
//                    }
                  
                     $user_position = $this->db->get('user')->result_array();
                    
                     //tracker user position
                     
                      $gcm1_data = array();
                     if(!empty($user_position)) {
                     
                        $gcm1_data['user_position'] = $user_position;
                     }
                    
                     if(isset($tracker_user_details['gcm_id']) && !empty($tracker_user_details['gcm_id']) && isset($gcm_data) && !empty($gcm_data)){
                        
                        $this->gcm->send_notification(array($tracker_user_details['gcm_id']),array("hmg" => $gcm_data));
                     }
                     
                     if(isset($tracked_user_details['gcm_id']) && !empty($tracked_user_details['gcm_id']) && isset($gcm1_data) && !empty($gcm1_data)){
                            
                        $this->gcm->send_notification(array($tracked_user_details['gcm_id']),array("hmg" => $gcm1_data));
                     }
                }
            }
        }
        
        
        //update user status visible or invisible
        function user_activation_get() {
        
            if(!$this->get('user_id') && !$this->get('status')) {
                return $this->response(array('status' => 'error', 'msg' => 'Required fields missing in your request', 'error_code' => 1), 404);
            }
          $user_id = $this->get('user_id');
          
          $result = $this->db->get_where('user',array('id' => $user_id))->num_rows(); 
          
          if($result > 0){
            $this->db->update('user',array('status' => $this->get('status'),'date_updated' => strtotime(date('Y-m-d h:i:s'))),array('id' => $user_id));
          } 
          
          return $this->response(array('status' =>'success'), 200);  
       }
       
       
    function guest_user_create_get()
     {
        if(!$this->get('android_id') && !$this->get('device_id') && !$this->get('gcm_id') && !$this->get('default') && !$this->get('user_type')) {
                return $this->response(array('status' => 'error', 'msg' => 'Required fields missing in your request', 'error_code' => 1), 404);
        }
        
        $android_id = $this->get('android_id');
        $device_id  = $this->get('device_id');
        $gcm_id     = $this->get('gcm_id');
        $default    = $this->get('default');
        $user_type  = $this->get('user_type');
        
        $result     = $this->db->query("select * from user where android_id='".$android_id."' and device_id='".$device_id."'")->row_array();
        
        if(isset($result['id'])) {
             $this->db->query("delete from user where id='".$result['id']."'");            
        }
       
         $this->db->insert('user',array('default_id' => $default,'phonenumber' => 0,'random_id' => 0,'android_id' => $android_id,'device_id'=>$device_id,'gcm_id'=> $gcm_id,'custom_id' => 0,'user_type' => $user_type,'date_created' => strtotime(date('Y-m-d h:i:s'))));
         
         $user_id =  $this->db->insert_id(); 
        
        if($user_id) {
                return $this->response(array('status' =>'success','user_id' => $user_id), 200);
        }
        else
        {
            return $this->response(array('status' => "error",'msg' => 'Unknown Error Occurred!! Try Again...','error_code' => 2), 404);
        }
        
     }
     
     function guest_user_update_get()
     {
        if(!$this->get('user_id') && !$this->get('default')) {
                return $this->response(array('status' => 'error', 'msg' => 'Required fields missing in your request', 'error_code' => 1), 404);
        }
        
        $user_id = $this->get("user_id");
        $custom  = $this->get("default");
        
        if($custom) {
                //Custom ID Check
              if(!empty($user_id))
                $user_idd = "and id!='$user_id'";
                
             $result = $this->db->query("select * from user where (default_id='".$custom."' or custom_id='".$custom."' or random_id='".$custom."' or phonenumber='".$custom."') $user_idd ")->num_rows();
            
            if(!empty($result)) {
                return $this->response(array('status' => "error",'msg' => 'Custom ID already exist.','error_code' => 104), 404);
            }
         }
        
        if($user_id){
            $this->db->update("user",array("default_id" => $custom, "date_updated" => strtotime(date("Y-m-d H:i:m"))),array("id" => $user_id));
            
            return $this->response(array('status' =>'success'), 200);
        }
        else
        {
            return $this->response(array('status' => "error",'msg' => 'Invalid User ID.','error_code' => 11), 404);
        }
        
        
     }
     function delete_track_user()
     {
        $result = $this->db->query("select * from single_user_track order by id desc")->result_array();
        
        $current_time = date("H", strtotime(date("Y-m-d H:i:s")));
        
        foreach($result as $res) {
            
            $start_time = date("H", $res['endtime']);
            $difference = $current_time - $start_time;
            
            if($difference > 1) {
                $this->db->query("delete from single_user_track where id='".$res['id']."'"); 
            }
        }
     }
    
}
    
  
?>
