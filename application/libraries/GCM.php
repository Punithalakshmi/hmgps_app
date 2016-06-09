<?php defined('BASEPATH') OR exit('No direct script access allowed');

class GCM {

    //put your code here
    // constructor
    function __construct() {
        //define("GOOGLE_API_KEY", "AIzaSyC1559Cx9HzQlm2LAvOPSu1O03k_7ZQvWc");
          define("GOOGLE_API_KEY", "AIzaSyCfPXSh29zjle1sQUeRUQmiUZgk6Lm7WLU");
    }

    /**
     * Sending Push Notification
     */
    public function send_notification($registatoin_ids, $message) {
       
       
        // include config
        //include_once './config.php';

        // Set POST variables
        $url = 'https://android.googleapis.com/gcm/send';
      //  echo "<pre>";
      //  print_r($registatoin_ids);
        $fields = array(
            'registration_ids' => $registatoin_ids,
            'data' => $message,
        );
       //print_r($fields);
    // echo json_encode($fields);
        $headers = array(
            'Authorization: key=' . GOOGLE_API_KEY,
            'Content-Type: application/json'
        );
        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields,JSON_UNESCAPED_SLASHES));
        //JSON_UNESCAPED_SLASHES

        // Execute post
        $result = curl_exec($ch);
        
       // echo $result;
      //  echo "<br />";
     
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }

        // Close connection
        curl_close($ch);
     
     
    }

}

?>
