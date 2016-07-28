<?php 


function is_logged_in()
{
    $CI = get_instance();
    
    $user_data = get_user_data();
    
    if( is_array($user_data) && $user_data )
        return TRUE;

    return FALSE;

}

function get_user_data()
{
    $CI = get_instance();
    
    
    if($CI->session->userdata('user_data'))
    {
        return $CI->session->userdata('user_data');
    }
    else
    {
        return FALSE;
    }
}

function get_user_role( $user_id = 0 )
{
    $CI= & get_instance();
    
    if(!$user_id) 
    {
        $user_data = get_user_data();
        return $user_data['role'];
    }   
    
    $CI->load->model('user_model');
    $row = $CI->user_model->get_where(array('id' => $user_id))->row_array;

    if( !$row )
        return FALSE;

    return $row['role'];
}

  function unique_location_link_and_key_generate($key,$status = "success"){
        $CI = &get_instance();
        $result = array();
        $result['link'] = site_url().'location/'.$key;
        $result['key'] = $key;
        $result['status'] = $status;
        
        return $result;
        
    }
    
function str_to_lower($str) {
    $ret_str ="";
    
    $ret_str = strtolower($str);
    
    return $ret_str;
}



function get_user_plan( $user_id = 0 )
{
    $CI= & get_instance();
    
    if(!$user_id) 
    {
        $user_data = get_user_data();
        return $user_data['role'];
    }   
    
    $CI->load->model('user_model');
    $row = $CI->user_model->get_where(array('id' => $user_id))->row_array;

    if( !$row )
        return FALSE;

    return $row['role'];
}


function get_plans()
{
    $CI = & get_instance();
    $CI->load->model('plan_model');
    $records = $CI->plan_model->get_plans();

    $roles = array();
    foreach ($records as $id => $val) 
    {
        $roles[$id] = $val;
    }

    return $roles;
}

function image_crop($source_image,$image_crop_url,$name)
{
   // echo $source_image."-".$image_crop_url;
    
    $CI = & get_instance();
   //echo $name;
   $configSize1 = $configSize2 = array();

   $CI->load->library('image_lib');

    /* First size */
    
  //  $configSize1['image_library']   = 'gd2';
    $configSize1['source_image']    = $source_image;
    $configSize1['create_thumb']    = FALSE;
    $configSize1['maintain_ratio']  = TRUE;
    $configSize1['width']           = 400;
    $configSize1['height']          = 400;
    $configSize1['quality']        = "100%";
    $configSize1['new_image']       = $image_crop_url."/large_$name";
    $CI->image_lib->initialize($configSize1);
    $CI->image_lib->resize();
    $CI->image_lib->clear();
    
    /* First size */
   // $configSize2['image_library']   = 'gd2';
    $configSize2['source_image']    = $source_image;
    $configSize2['create_thumb']    = FALSE;
    $configSize2['maintain_ratio']  = TRUE;
    $configSize2['width']           = 200;
    $configSize2['height']          = 200;
    $configSize2['quality']        = "100%";
    $configSize2['new_image']       = $image_crop_url."/thumb_$name";
    $CI->image_lib->initialize($configSize2);
    $CI->image_lib->resize();
    
    if ( ! $CI->image_lib->resize())
    {
    echo $CI->image_lib->display_errors(); exit;
    }
    $CI->image_lib->clear();
   
}

function create_group($join_key = '', $user_id = '',$type = '') 
{  
    
    $CI = & get_instance();
    
    $CI->load->model(array("group_model","user_groups_model"));
    
    $result = $CI->group_model->check_unique(array("join_key" => $join_key));
         
    $ins_data = array();
    $ins_data['name']            = $join_key;
    $ins_data['type']            = 'private';
    $ins_data['join_key']        = $join_key;
    $ins_data['description']     = $join_key;
    $ins_data['location_type']   = 'mobile';
    $ins_data['user_id']         = $user_id; 
    $ins_data['is_available']    = 1;

    if(!count($result))
    {
        $get_user_active_groups = $CI->group_model->check_unique(array("status" => 1, 'user_id' => $user_id));
        
        if(count($get_user_active_groups)) {
            $ins_data['status']  = 0;
        }
        else
        {
            $ins_data['status']       = ($type=='default')?1:0;
        }
        
        $ins_data['date_created'] = strtotime(date('Y-m-d h:i:s'));
       
        $group_id =  $CI->group_model->insert($ins_data);
        
        //join group
        $res    = $CI->user_groups_model->check_unique(array('user_id' => $user_id, 'group_id' => $group_id));
        $cnt    = count($res);
        
        $user_active_groups = $CI->user_groups_model->check_unique(array("status" => 1, 'user_id' => $user_id));
        
        
        
        if(empty($cnt)) {
            $ins_data = array();
            $ins_data['user_id']   = $user_id;
            $ins_data['group_id']  = $group_id; 
            if(count($user_active_groups)) {
                $ins_data['status']  = 0;
            }
            else
            {
                $ins_data['status']    = ($type=='default')?1:0;
            } 
            $ins_data['is_joined'] = 1; 
           $ins_data['last_seen_time'] = date("Y-m-d H:i:s"); 
           
            $add_group             = $CI->user_groups_model->insert($ins_data);
        }
    }
}

function gen_random_string($length=16)
{
    $CI = & get_instance();
    $CI->load->model("group_model");

    $chars ="ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
    $final_rand='';
    for($i=0;$i<$length; $i++)
    {
        $final_rand .= $chars[ rand(0,strlen($chars)-1)];
 
    }

    $res = $CI->group_model->check_unique(array("join_key" => $final_rand));
    if(count($res))
        return gen_random_string(6);

    return $final_rand;
}

function user_group_update($user_data,$default_id,$phonenumber)
{
    
      $CI = & get_instance();
      $CI->load->model("group_model");
        
      $group_data1 =array();
      $group_data1['join_key'] = $default_id;
      $group_data1['name']     = $default_id;
     
      $result1 = $CI->group_model->check_unique(array("join_key" => $user_data['default_id'],"user_id" => $user_data['id']));
      
      //if default id group not exists means create new group
      if(!count($result1) && empty($result1) && (!empty($default_id))) {
        create_group($default_id,$user_data['id'],'default');
      } 
      
      $CI->group_model->update($group_data1,array('id' => $result1['id']));
      
      $group_data2 = array();
      $group_data2['join_key'] = $phonenumber;
      $group_data2['name']     = $phonenumber;
      
     // echo $phonenumber;
      $result2 = $CI->group_model->check_unique(array("join_key" => $user_data['phonenumber'],"user_id" => $user_data['id']));
       
        if(!count($result2) && empty($result2) && (!empty($phonenumber))) {
           create_group($phonenumber,$user_data['id'],'phonenumber');
        }  
           
      $CI->group_model->update($group_data2,array('id' => $result2['id']));
}


function displayData($data = null, $type = 'string', $row = array(), $wrap_tag_open = '', $wrap_tag_close = '')
{
     $CI = & get_instance();
     
    if(is_null($data) || is_array($data) || (strcmp($data, '') === 0 && !count($row)) )
        return $data;
    
    switch ($type)
    {
        case 'string':
            break;
        case 'humanize':
            $CI->load->helper("inflector");
            $data = humanize($data);
            break;
        case 'date':
            str2USDate($data);
            break;
        case 'datetime':
            $data = str2USDate($data);
            break;
        case 'planname':
            $CI->load->model("plan_model");
            $plan = $CI->plan_model->check_unique(array('id' => $data));
            $data = $plan['planname'];
            break;  
        case 'username':
            $data = '<a href="'.base_url().'admin/user/add/'.$row['id'].'">'.$data.'</a>';    
            break;    
       case 'participant_lists':
            $data = '<button class="btn btn-info participant btn-action" data-toggle="modal" data-target="#participant_lists" onclick="get_participants_lists('.$row['id'].')"> 
                      <img src="'.base_url().'assets/admin/images/participants.png" class="img-responsive m-menu" alt="participants"> 
                    </button>';
            break;        
       case 'user_type':
            $plan = $CI->db->query("select plan_type from user_plan where plan_id='".$row['plan_id']."'")->row_array();
            $data = ucfirst($plan['plan_type']);
            break; 
       case 'current_location':
            $data = '<a href="http://maps.google.com/maps?z=12&t=m&q=loc:'.$row['lat'].'+'.$row['lon'].'">View Location</a>';
            break; 
       case 'participant_count':
            $CI->load->model("promo_model");
            $promo = $CI->promo_model->check_unique(array('id' => $row['last_assigned_promo']));
            $data = $promo['participant_count'];
            break;
       case 'last_updated':
            $data = date("Y-m-d H:i:s", $row['last_updated']);
            break;
       case 'userstatus':
            if($data==1){
                $color = '#7CFC00';
            }
            else if($data == 2)
            {
                $color = '#ff0000 ';
            }
            else if($data == 3)
            {
                $color = '#fff500';
            }
            $data =  '<span style="background-color:'.$color.'; border-radius: 20px;display: inline-block;height: 20px;margin-left: 10px;
    margin-top: 10px;width: 20px;"></span>';
            break;
                
    }
    
    return $wrap_tag_open.$data.$wrap_tag_close;
}

//common function search group and map channel ids
function get_group_data($position,$result)
{
    
     $CI = & get_instance();
     
     $CI->load->model("user_model");
     
    $user_position = $user = array();
    
    foreach($position as $pkey => $pvalue)
     {
            $usertype = ($pvalue['id']==$result['user_id'])?'admin':'member'; 
            
            if(!empty($pvalue['last_seen_time'])) {

               $lastseen = date('d-m-Y H:i',strtotime($pvalue['last_seen_time']));
               $currtime = date('d-m-Y H:i',strtotime('-1 hour'));
            
                $lastup = 1;
                
                if(strtotime($currtime) >= strtotime($lastseen)) {  
                    
                    if($usertype == 'member') {
                       continue;
                    }
                    
                  $lastup = 0;
                
                }   
            }
            
            $lat = $pvalue['lat'];
            $lon = $pvalue['lon'];
            
            if($result['user_id'] == $pvalue['id']){
                $user['lastseenup'] = $pvalue['date_updated'];
                $user['admin_id']   = $pvalue['id'];
            }  
            
            //check user group status active or inactive
            $user_position['lat']         = $lat;
    
            $user_info = $CI->_prepare_user_details($user_position);
            
            if($user_info['user']['profile']) {
                $user[$i]['user']['profile']['id']              = $pvalue['id'];
                $user[$i]['user']['profile']['default_id']      = $pvalue['default_id'];
                $user[$i]['user']['profile']['display_name']    = $pvalue['display_name'];
                $user[$i]['user']['profile']['phonenumber']     = $pvalue['phonenumber'];
                $user[$i]['user']['profile']['email']           = $pvalue['email'];
                $user[$i]['user']['profile']['android_id']      = $pvalue['android_id'];
                $user[$i]['user']['profile']['device_id']       = $pvalue['device_id'];
                $user[$i]['user']['profile']['profile_image']   = site_url()."assets/uploads/profile/resize/thumb_".$pvalue['id'].".jpg";
                $user[$i]['user']['profile']['flag']            = $pvalue['is_tracked'];
                $user[$i]['user']['profile']['user_type']       = $usertype;
            }
            if($user_info['user']['account']) {
                $user[$i]['user']['account']['plan_name'] = $pvalue['plan_name'];
            } 
           $user[$i]['user']['position']['lat']          = $lat;
           $user[$i]['user']['position']['lon']          = $lon;
           $user[$i]['user']['position']['altitude']     = $pvalue['altitude'];
           $user[$i]['user']['position']['speed']        = $pvalue['speed']; 
           $user[$i]['user']['position']['bearing']      = $pvalue['bearing']; 
           $user[$i]['user']['position']['accuracy']     = $pvalue['accuracy'];
           $user[$i]['user']['group']['id']              = $pvalue['group_id'];
           $user[$i]['user']['group']['description']     = $pvalue['description'];
           $user[$i]['user']['group']['join_key']        = $pvalue['group_join_key'];
           $user[$i]['user']['group']['status']          = $pvalue['status'];
           $user[$i]['user']['group']['visible']         = $pvalue['is_joined'];
           $user[$i]['user']['group']['view']            = $pvalue['is_view'];

           $user[$i]['user']['group']['invisible']       = $pvalue['is_visible'];
           if($lat==0)
              $user[$i]['user']['group']['invisible']       = 1;

           $user[$i]['user']['group']['type']            = $result['type'];
           $user[$i]['user']['group']['last_seen_time']  = strtotime($pvalue['last_seen_time']);
           $user[$i]['user']['group']['is_online']       = $lastup;
           $user[$i]['user']['group']['share_link']      = site_url()."search/".$pvalue['group_join_key'];
           
           
           $user[$i]['user']['static_map']['user_id'] = $pvalue['id'];

           $static_maps = $CI->user_model->get_static_maps($pvalue['id'],$pvalue['group_id']);

           $user[$i]['user']['static_map']['maps']   = $static_maps;
          // print_($user);

             

        $i++;                               
     }
     
     return $user;

}

function _prepare_user_details($user_data = array(),$user_id = 0) 
{
    $CI = & get_instance();
               
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
        
        $CI->_prepare_user_details( $CI->db_model->get_user_details(array('id' => $user_id),implode(',',$user_profile_fields)) );
    }
    
    return $result;
}