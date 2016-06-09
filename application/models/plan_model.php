<?php
safe_include("models/app_model.php");
class Plan_Model extends App_model {
    
    
    function __construct() 
    {
        parent::__construct();
        $this->_table = 'plans';
    }
   
   
   function listing()
   {
      $this->_fields = "*,id as id";
       
        foreach ($this->criteria as $key => $value) 
        {
            if( !is_array($value) && strcmp($value, '') === 0 )
                continue;

            switch ($key)
            {
                case 'planname':
                    $this->db->like($key, $value);
                break;
            }
        }
        
        return parent::listing();
    }
    
   function insert($ins_data)
   {
     $this->db->insert($this->_table,$ins_data);
     return $this->db->insert_id();
   } 
    
   function update($update_data,$where)
   {
     $this->db->where($where);
     $this->db->update($this->_table,$update_data);
   }
   
   
  function get_pro_plans()
  {
    $this->db->select("p.*,t.*,p.id as plan_id,t.id as plan_type_id");
    $this->db->from("plans p");
    $this->db->join("plan_types t","t.plan_id=p.id");
    $this->db->where(array("t.type =" => "app"));
    return $this->db->get()->result_array();
  }
  
  function get_all_plans()
  {
    $this->db->select("*");
    $this->db->from($this->_table);
    return $this->db->get()->result_array();
  }
    
   function get_plans()
   {
      $this->db->select('*');
      $this->db->from('user_plan');
      return $this->db->get()->result_array();
   } 
    
   function get_plan_details($where)
   {
	  $this->db->select('*');
      $this->db->from('user_plan');
      $this->db->where($where);
      $query = $this->db->get()->row_array();
      return $query;
   }
   
   function check_unique($where)
   {
     $this->db->select("*");
     $this->db->from($this->_table);
     $this->db->where($where);
     return $this->db->get()->row_array();
   }
   
   function get_access()
   {
     $this->db->select("*");
     $this->db->from("plan_access");
     return $this->db->get()->result_array();
   }
   
   function plan_access_insert($ins_data)
   {
     $this->db->insert("plan_access",$ins_data);
     return $this->db->insert_id();
   }
}
?>
