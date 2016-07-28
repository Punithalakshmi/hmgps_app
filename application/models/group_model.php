<?php

class Group_Model extends CI_Model {
    
    
    function __construct()
    {
        parent::__construct();
        $this->_table = 'groups';
    }
   	
	function insert($ins_arr)
	{		
		 $this->db->insert($this->_table, $ins_arr);
         return $this->db->insert_id(); 
	}
	
    function update($ins_data,$where)
    {
        $this->db->where($where);
        $this->db->update($this->_table,$ins_data);
        return $this->db->affected_rows();
    }
     function delete($where)
    {
        $this->db->where($where);
        $this->db->delete($this->_table);

    }
    function check_unique($where)
    {
        $this->db->select();
        $this->db->from($this->_table);
        $this->db->where($where);
        $result = $this->db->get()->row_array();
        return $result;
    }
    
    //get all channels
    function get_own_channel($user_id)
    {
        $this->db->select("g.*,g.id as gid,g.user_id as admin_id,IF(g.user_id=".$user_id.",'own','notown') as grouptype,ug.last_seen_time",false);
        $this->db->from("groups g");
        $this->db->join("user_groups ug", "ug.group_id=g.id");
       // $this->db->where("g.user_id",$user_id);
        $this->db->where("ug.user_id",$user_id);
        $this->db->order_by("g.id","asc");
        return $this->db->get()->result_array();
    }
    
    //get all members particular channel 
    function get_channel_members($group_id,$blocked)
    {
        $this->db->select('user.id as uid,user.default_id,user.display_name,user_groups.group_id,user_groups.is_joined,user.phonenumber,user.android_id,user.device_id,user.email,user.profile_image,user_position.lat,user_position.lon,user_groups.status,user_plan.plan_name,user_groups.last_seen_time,user.is_tracked,user_groups.is_view,user_position.date_updated,user_position.accuracy,user_position.altitude,user_position.speed,user_position.bearing,user_groups.is_visible,user_groups.blocked,user_groups.is_favourite,user_groups.user_id as uid',false);
        $this->db->from("user_groups");
        $this->db->where("user_groups.group_id",$group_id);
        $this->db->where('user_groups.blocked',$blocked);
        $this->db->join('user_position','user_groups.user_id=user_position.user_id');
        $this->db->join('user','user.id=user_position.user_id');
        $this->db->join('user_plan','user_plan.plan_id=user.plan_id');
        return $this->db->get()->result_array();
    }
    
    function get_owner_status($join_key)
    {
        $this->db->select("user.is_tracked as track, user.id,user_groups.status as active");
        $this->db->where("groups.join_key",$join_key);
        $this->db->join("user_groups", "user_groups.group_id=groups.id and user_groups.user_id=groups.user_id");
        $this->db->join("user","user.id=user_groups.user_id");
        $result = $this->db->get("groups")->row_array();
        return $result;
    }
    
    function lists($user_id)
    {
        $this->db->select('groups.id,groups.description,groups.join_key,groups.type,groups.name,user.default_id,user_groups.is_favourite,user_groups.last_seen_time',false);
        $this->db->where("groups.user_id",$user_id);
        $this->db->join('user','user.id=groups.user_id');
        $this->db->join('user_groups','user_groups.group_id=groups.id');
        return $this->db->get('groups')->result_array();
    }
    
    function get_group_users($table,$where)
    {
        $this->db->select("*");
        $this->db->from($table);
        $this->db->where($where);
        $result = $this->db->get()->result_array();
        return $result;
    }
    
    function get_user_position($key)
    {
        $this->db->select('user.id,user_position.lat,user_position.lon',false);
        $this->db->where('groups.join_key',$key);
        $this->db->where('groups.location_type','mobile');
        $this->db->join('user_position','groups.user_id=user_position.user_id');
        $this->db->join('user','user.id=user_position.user_id');
        return $this->db->get('groups')->row_array();
       
    }
    
    function filter_groups($filtered_group_ids)
    {
        //$this->db->where("status",1);
        $this->db->where_in('id',$filtered_group_ids);
        return $this->db->get('groups')->result_array();
    }
    
    function get_group_owner_gcm($join_key)
    {
        $this->db->select("user.gcm_id,user.id,groups.*,groups.id as group_id");
        $this->db->where("groups.join_key",$join_key);
        $this->db->join("user","user.id=groups.user_id");
        return $this->db->get('groups')->row_array();
    }
    
    function get_group_members($group)
    {
        $this->db->select("user.*,groups.join_key,user_groups.last_seen_time,groups.id as gid,groups.description,user_position.lat,user_position.lon,user_position.accuracy,user_groups.blocked,IF(user_groups.status=1,'active','inactive') as status,IF(groups.user_id=user.id,'admin','member') as user_type,IF(user_groups.is_visible=1,1,0) as invisible",false);
        $this->db->where("groups.join_key",$group['join_key']);
        $this->db->join("groups","groups.id=user_groups.group_id");
        $this->db->join("user","user.id=user_groups.user_id");
        $this->db->join('user_position','user_position.user_id=user.id');
        $this->db->order_by('user_groups.is_visible','asc');
        return $this->db->get("user_groups")->result_array();
        
        
    }
    function get_single_user_gcm($user_id)
    {
        $this->db->select("gcm_id,id");
        $this->db->where("id",$user_id);
        return $this->db->get('user')->row_array(); 
    }
    
}
?>
