<?php if(!defined("BASEPATH")) exit("No direct script access allowed");
safe_include("controllers/admin/admin_controller.php");
class Home extends Admin_controller {
    function __construct()
    {
        parent::__construct();
         
    }

    public function index()
    {
      
        if(is_logged_in()) {
            $this->service_message->set_flash_message("login_success");
            $this->layout->add_stylesheets(array('css/main','css/theme','css/MoneAdmin','plugins/Font-Awesome/css/font-awesome','css/layout2','plugins/flot/examples/examples','plugins/timeline/timeline'));
            $this->layout->view("admin/home");
        }
        else
        {
            
            $this->layout->view("login/login");
        }
    }
}
?>