<?php if(!defined("BASEPATH")) exit("No direct script access allowed");

class Home extends CI_Controller {
    function __construct()
    {
        parent::__construct();
         
    }

    public function index()
    {
        
         $this->load->view("_partials/header");
        $this->load->view("home");
        $this->load->view("_partials/footer");
    }
}
?>