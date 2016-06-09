<?php
safe_include("models/app_model.php");
class User_Model  extends App_model {
    
    
    function __construct()
    {
        parent::__construct();
        $this->_table = 'user';
    }
    
    function listing()
    {
      //$this->_fields = "*,id as id";
      
       $this->_fields = "u.*,g.join_key,(select code from promo p where p.id=u.last_assigned_promo) as assigned_promo,IF(u.gcm_id!='','1','0') as gcm,up.id as position,up.lat as lat,up.lon as lon,up.date_updated as last_updated";
       $this->db->from('user u');
       $this->db->join('groups g',"g.join_key=u.default_id");
       $this->db->join('user_position up',"up.user_id=u.id");
       $this->db->group_by("g.join_key");
       
        foreach ($this->criteria as $key => $value) 
        {
            if( !is_array($value) && strcmp($value, '') === 0 )
                continue;

            switch ($key)
            {
                case 'default_id':
                    $this->db->like("u.default_id", $value);
                break;
                case 'phonenumber':
                    $this->db->like("u.phonenumber", $value);
                break;
                case 'email':
                    $this->db->like("u.email", $value);
                break;
                case 'display_name':
                    $this->db->like("u.display_name", $value);
                break;
               
            }
        }  
        
        return parent::listing();
    }
    
   	// insert a record based on the parameters
	function insert($table, $ins_arr)
	{		
		 $this->db->insert($table, $ins_arr);
         return $this->db->insert_id();
	}
	
    function update($table,$ins_data,$where)
    {
        $this->db->where($where);
        $this->db->update($table,$ins_data);
    }

    function delete($table,$where)
    {
        $this->db->where($where);
        $this->db->delete($table);
    }
    
    public function check_unique($where)
    {
        $this->db->select("*");
        $this->db->from($this->_table);
        $this->db->where($where);
        $result = $this->db->get();
        return $result->row_array();
    }
    public function get_by_loginid($login_id)
    {
        $this->db->select("*");
        $this->db->from($this->_table);
        $this->db->where('user_name', $login_id);
        $result = $this->db->get();
        return $result->row_array();
    }
    public function get_users($id = 0)
    {
        $this->db->select("*");
        $this->db->from($this->_table);
        if($id != 0) {
            $this->db->where('id', $id);
        }
        $result = $this->db->get();
        return $result;
    }
    
    function guest_time_check($user_id)
    {
        $this->db->select("ROUND(((UNIX_TIMESTAMP(now()) - user.date_created)/3600)) as diff,user.date_created");
        $this->db->from("user");
        $this->db->join("user_plan","user_plan.plan_id=user.plan_id");
        $this->db->where('user.id', $user_id);
        $this->db->where('user_plan.plan_type', "guest");
        return $result = $this->db->get()->row_array();     
    }
    
    function get_defult_group($user_id)
    {
        $this->db->select("user.default_id,groups.id,groups.join_key");
        $this->db->where("user.id",$user_id);
        $this->db->join("groups","groups.join_key=user.default_id");
        
        return $this->db->get("user")->row_array();
    }

    function insert_notification($ins_data)
    {
        $this->delete_old_records();

        $this->db->insert('user_notifications', $ins_data);
        return $this->db->insert_id();
    }

    function get_user_notifications($user_id)
    {
        $this->db->select("*");
        $this->db->where("user_id",$user_id); 
        $this->db->order_by("is_viewed",'asc');       
        $res = $this->db->get("user_notifications")->result_array();

        $result=array();
        if(count($res)){
            foreach($res as $val){
                $result[] = array('msg_id'=>$val['id'],'message'=>json_decode($val['message'],TRUE),'is_viewed'=>$val['is_viewed'],'date_created'=>strtotime($val['date_created']));
            }
        }
        return $result;
    }
    function delete_old_records()
    {
        $date = date('Y-m-d H:i:s', strtotime('-7 days'));
        $this->db->where('date_created <=',$date);
        $this->db->delete('user_notifications');
    }

    function check_user_static_group($user_id,$group_id,$map_name)
    {
        $this->db->select("id");
        $this->db->where("user_id",$user_id); 
        $this->db->where("group_id",$group_id); 
        $this->db->where("map_name",$map_name); 
        $res = $this->db->get("user_static_maps")->num_rows();

        return $res;

    }

    function get_static_maps($user_id,$group_id)
    {
        $this->db->select("user_id,group_id,map_name,lat,lon,notes,status,created_time,clue_image");
        $this->db->where("user_id",$user_id); 
        $this->db->where("group_id",$group_id); 
        $this->db->where("status",1); 
        $res = $this->db->get("user_static_maps")->result_array();

        $result = array();

        if(count($res)>0){
            $result = $res;
            
            foreach($res as $key => $val){

                if(!empty($val['clue_image']))
                    $result[$key]['clue_image'] = site_url()."assets/uploads/profile/resize/large_static_".$val['group_id']."_".$val['user_id'].".jpg";
            }
        }   
        return $result;
    }
    
   function get_all_users()
   {
        $this->db->select("*");
        $this->db->from($this->_table);
        return $this->db->get()->result_array();
   }
}
?>