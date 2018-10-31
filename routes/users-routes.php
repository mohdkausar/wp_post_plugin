<?php
/*
Reservation API Routes functions
*/
 
class users_routes extends dashboard {
 
  /**
   * Register the routes for the objects of the controller.
   */
	public function register_routes() {
		add_action( 'rest_api_init', function () {

		  register_rest_route( 'dashboard/users/v1', '/login', array(
		    'methods' => 'POST',
		    'callback' => array($this,'login'),
		  ) );

		  register_rest_route( 'dashboard/users/v1', '/get/properties', array(
		    'methods' => 'POST',
		    'callback' => array($this,'get_properties'),
		  ) );

		   register_rest_route( 'dashboard/test/v1', 'test', array(
		    'methods' => 'POST',
		    'callback' => array($this,'test'),
		  ) );
		} );
	}

	public function test() {
		global $wpdb, $table_prefix;
		$query = "select * from wp_users LIMIT 1";
		$res = $wpdb->get_results($query);
		echo json_encode($res);
		// print_r($res);
	}

    /**
	 * login validation
	 */
	public function get_properties( $request )
	{
	     $dashboard  = new dashboard();
	     $property   = new dashboard_properties_table();
	     $users      = new dashboard_users_table();
	     if($request['user_id']){
		    $prop       = $users->get_properties_by_user($request['user_id']);
		    $facilities = explode(",", $prop[0]->facilities);
		    if(is_array($facilities)){
        	    foreach($facilities as $facility){
        	         $properties['properties'][] = $property->get_property_by_id($facility);
        	    }
        	    $formatted_return = $dashboard->response_success_error('success',$properties);
		        return new WP_REST_Response( $formatted_return, 200 );
		    }else{
		    $formatted_return = $dashboard->response_success_error('error','There are not properties associate to the user');
		    return new WP_REST_Response( $formatted_return, 200 );    
		    }
	     }else{
	        $formatted_return = $dashboard->response_success_error('error','Please provide User ID');
		    return new WP_REST_Response( $formatted_return, 200 );
	     }
	     
	     
	}
	/**
	 * login validation
	 */
	public function login( $request )
	{
	    $dashboard  = new dashboard();
		$users      = new dashboard_users_table();
	    if(!isset($request['email']) || !isset($request['password'])){
	        $formatted_return = $dashboard->response_success_error('error','Please provide Email-address and Password.');
		    return new WP_REST_Response( $formatted_return, 200 );
	    }else{
			//sanitize email
			$email = filter_var($request['email'], FILTER_SANITIZE_EMAIL);
		    $returned   = $users->get_users($email);
		    if(empty($returned)){
		        $formatted_return = $dashboard->response_success_error('error','User is not register in the system.');
		        return new WP_REST_Response( $formatted_return, 200 );
		    }else{
		        $validation = $dashboard->validatePassword($returned[0]->password,$request['password'],$returned[0]->salt);
		        if($validation){
					//update token in DB
					$token = base64_encode( ( time()+ (86400 * 30) ).'$$'.$email); // 1 day
					$update   = $users->update_token($token, $returned[0]->id);
					//update token value
					$returned[0]->token = $token ;
					// unset password
					unset($returned[0]->password);
					//send response
		            $formatted_return = $dashboard->response_success_error('success',$returned,'Login successful');
		            $formatted_return = $dashboard->response_success_error('success',$returned);
		            return new WP_REST_Response( $formatted_return, 200 );
		        }else{
		            $formatted_return = $dashboard->response_success_error('error','Incorrect Email-address or Password.');
		            return new WP_REST_Response( $formatted_return, 200 );  
		        }
		    }
	    }
	}

}