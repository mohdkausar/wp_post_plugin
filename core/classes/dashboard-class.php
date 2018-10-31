<?php
/**
 *Any Helper functions go in this class
 */

class dashboard {
	
	public function response_success_error($suc_err,$data_stuffs,$message1 = NULL,$callback = NULL){
		$response 	= new stdClass();
		switch ($suc_err) {
			case "success":
				$response->data 	= $data_stuffs;
				$response->message	= $message1;
				$response->result	= 1;
				$response->callback	= $callback;
				return $response;
				break;
			case "error":
				$response->data 	= '';
				$response->message	= $data_stuffs;
				$response->result	= 0;
				$response->callback	= $callback;
				return $response;
				break;
			case "callback_data":
				$response->data 	= $data_stuffs;
				$response->message	= '';
				$response->result	= 0;
				$response->callback	= $callback;
				return $response;
				break;
		}
	}
	/**
	 * Check user input before inserting a new setting or updating existing setting
	 */
	public function validate($request,$type = 'insert'){
		
		$errors = [];
		//columns that are required 
		$columns = ['min_enabled','rin_enabled','call_us_enabled','call_us_setting','language','color_primary','color_secondary','show_reviews','upgraded_email',
		'note_gate','note_movein','note_reservation','note_terms','note_payment','note_late_payment','note_insurance','features','insurance','hours','multimedia',
		'hidden_fields','map','google','facebook','id_verification','request_fields','unavailable_dates','faq','welcome_min_email_notes','unit_left_limit','test_mode',
		'call_us_limit','hide_price','push_rates','show_gatecode','allow_discounts_reservation','gate_codes','yelp','reserve_days_out','promo_note','allow_esign',
		'pre_pay','allow_discounts','required_image_dl','welcome_rin_emaill_notes','promo_required','remove_buttons','force_autopay','discounts_full_price','show_calendar',
		'insurance_text','ins_title','ins_desc','promo_title','promo_desc','days_billing','allow_ach','default_order','editable_fields','show_tenant_login',
		'move_title','reserve_title'];
		//columns of int type
		$int_columns = ['min_enabled','rin_enabled','call_us_enabled','call_us_setting', 'show_reviews','upgraded_email','hidden_fields','id_verification','unit_left_limit',
		'insurance','test_mode','call_us_limit','hide_price','push_rates',
		'pre_pay','allow_discounts','show_gatecode','allow_discounts_reservation','required_image_dl','reserve_days_out','promo_required','allow_esign','remove_buttons',
		'force_autopay','discounts_full_price','show_calendar','days_billing','allow_ach','default_order','show_tenant_login'];
		//columns of string type
		$string_columns = ['language','color_primary','color_secondary', 'note_gate','note_movein','note_reservation','note_terms','note_payment','note_late_payment',
							'note_insurance','features','hours','multimedia','map','google','facebook','request_fields','unavailable_dates','faq','welcome_min_email_notes',
							'welcome_rin_emaill_notes','gate_codes','yelp','promo_note','insurance_text','ins_title','ins_desc','promo_title','promo_desc','editable_fields',
							'move_title','reserve_title'];

		//in case of update or delete
		if( in_array($type,['update','delete','get'] ) ) {
			//checking if property_id exists in input 
			if( !isset($request['property_id']) ) {
				$errors[] = 'Parameter property_id is required';
			}
			//$property_id= str_replace('\'""\/<>!@#$%^&*()-_=+,./','', $request['property_id']);
			//check if property_id is empty
			elseif( empty(trim( $request['property_id'] ) ) ) {
				$errors[] = 'Parameter property_id should not be empty';
			}
			//return in case of get data or delete case
			if('delete' === $type || 'get' === $type){
				return $errors;
			}
		}
		//checking if required fields exists, not empty and are of correct type
		foreach($columns as $value){
			
			//checing if required field exists
			if( !isset( $request[$value] ) ) {
				$errors[] = 'Parameter '.$value.' is required';
			}
			//checing if requred field is empty
			elseif( isset( $request[$value] ) &&  !strlen( trim($request[$value]) )  ) {
				$errors[] = 'Parameter '.$value.' should not be empty';
			}
			//checking if columns are of integer type
			if(isset( $request[$value] ) && in_array($value,$int_columns) &&  !is_numeric( $request[$value] )  ) {
				$errors[] = 'Parameter '.$value.' should be an integer';
			}
			//checking if columns are of string type
			if(isset( $request[$value] ) && in_array($value,$int_columns) &&  !is_string( $request[$value] )  ) {
				$errors[] = 'Parameter '.$value.' should be a string';
			}
		}
		return $errors;
	}
	
	/**
	 * Check user input dates in request
	 */
	public function validateDates($request){
		$errors = [];
		$format = 'Y-m-d';
		//check start date
		if( !isset($request['start_date'])) {
			$errors[] = 'Parameter start_date is required';
		}
		//check end date
		if( !isset($request['end_date'])) {
			$errors[] = 'Parameter end_date is required';
		}
		//check if start date is empty
		if( !count($errors) &&  isset($request['start_date']) && empty(trim($request['start_date']) ) ) {
			$errors[] = 'Parameter start_date should not be empty';
		}
		//check if end date is empty
		if( !count($errors) &&  isset($request['end_date']) && empty(trim($request['start_date']) ) ) {
			$errors[] = 'Parameter end_date should not be empty';
		}
		//check start date format
		$temp_date = explode('-', $request['start_date']);
		if(!checkdate($temp_date[1], $temp_date[2], $temp_date[0] ) ){
			$errors[] = 'Parameter start_date should be in yyyy-mm-dd format';
		}
		//check if date is less than 2005-01-01
		else if($temp_date[0]  < 2005){
			$errors[] = 'Parameter start_date should not precede 2005-01-01';
		}
		//check end date format
		$temp_date = explode('-', $request['end_date']);
		if(!checkdate($temp_date[1], $temp_date[2], $temp_date[0] ) ){
			$errors[] = 'Parameter end_date should be in yyyy-mm-dd format';
		}
		//check if date is less than 2005-01-01
		else if($temp_date[0]  < 2005){
			$errors[] = 'Parameter end_date should not precede 2005-01-01';
		}
		return $errors;
	}
	/**
	 * format result of "traffic_type" and "view_by_device" APIs before sending it to user
	 */
	public function format_response($result,$type = 'percentage'){
		
		$response = [];
		$counter = 0;
		$total = 0;
		if(!empty($result) && count($result) > 0) {
			foreach($result as $key=>$value){
				$response[$counter]['type'] = (isset($value['type']['dimension'])) ? reset($value['type']['dimension']) : "";
				$response[$counter]['value'] = (isset($value['value']['metrices'])) ? reset($value['value']['metrices']) : 0;
				$total = $total + $response[$counter]['value'];
				$counter++;
			}
			if('percentage' == $type){

				//add percentage for each value
				foreach($response as $key=>$value){
					$response[$key]['percentage'] = isset($value['value']) ? number_format((float)(($value['value']*100)/$total), 2, '.', '') : "";
				}
			}
		}
		return $response;
	}
	/**
	 * format result of "page_views" APIs before sending it to user
	 */
	public function format_views_response($result){

		$response = [];
		$counter = 0;
		if(!empty($result) && count($result) > 0) {
			foreach($result as $key=>$value){
				$response[$counter]['path'] = (isset($value['type']['dimension'])) ? reset($value['type']['dimension']) : "";
				$response[$counter]['page_title'] = (isset($value['type']['dimension'][1])) ? $value['type']['dimension'][1] : "";

				$response[$counter]['total_views'] = (isset($value['value']['metrices'])) ? reset($value['value']['metrices']) : 0;
				$response[$counter]['unique_views'] = (isset($value['value']['metrices'][1])) ? $value['value']['metrices'][1] : 0;
				$response[$counter]['time'] = (isset($value['value']['metrices'][2])) ? gmdate("H:i:s", $value['value']['metrices'][2]): 0;
				$counter++;
			}
		}
		return $response;
	}
	
		/**
	 * format result of "site_visitors" APIs before sending it to user
	 */
	public function format_site_visitors_response($result,$timeframe){

		$response = [];
		$counter = 0;
		if(!empty($result) && count($result) > 0) {
			foreach($result as $key=>$value){
				$response[$counter]['timeframe'] = (isset($value['type']['dimension'])) ? reset($value['type']['dimension']) : "";
				//set timeframe format
				$year = substr($response[$counter]['timeframe'], 0, 4);
				if( "date" == $timeframe){
					$month = substr($response[$counter]['timeframe'], 4, 2);
					$day = substr($response[$counter]['timeframe'], 6, 8);
					$response[$counter]['timeframe'] = $year."/".$month."/".$day;
				}
				else if("yearWeek" == $timeframe){
					$week = substr($response[$counter]['timeframe'], 4, 2);
					$response[$counter]['timeframe'] = "Week #".$week."(".$year.")";

				}else{
					$month = substr($response[$counter]['timeframe'], 4, 2);
					$response[$counter]['timeframe'] = $month."/".$year;
				}
				$response[$counter]['visitors'] = (isset($value['value']['metrices'][0])) ? reset($value['value']['metrices']) : 0;
				$response[$counter]['new_visitors'] = (isset($value['value']['metrices'][1])) ? $value['value']['metrices'][1] : 0;
				$counter++;
			}
		}
		return $response;
	}
	
	
	/**
	 * format result of "visitors" APIs before sending it to user
	 */
	public function format_visitors_response($result){

		$response = [];
		$counter = 0;
		if(!empty($result) && count($result) > 0) {
			foreach($result as $key=>$value){
				$response[$counter]['city'] = (isset($value['type']['dimension'])) ? reset($value['type']['dimension']) : "";
				$response[$counter]['state'] = (isset($value['type']['dimension'][1])) ? $value['type']['dimension'][1] : "";

				$response[$counter]['visitors'] = (isset($value['value']['metrices'])) ? reset($value['value']['metrices']) : 0;
				$response[$counter]['time'] = (isset($value['value']['metrices'][1])) ? gmdate("H:i:s", $value['value']['metrices'][1]): 0;
				$counter++;
			}
		}
		return $response;
	}

	/**
	 * format result of "sources" APIs before sending it to user
	 */
	public function format_sources_response($result){

		$response = [];
		$counter = 0;
		if(!empty($result) && count($result) > 0) {
			foreach($result as $key=>$value){
				$response[$counter]['source'] = (isset($value['type']['dimension'])) ? reset($value['type']['dimension']) : "";
			
				$response[$counter]['sessions'] = (isset($value['value']['metrices'])) ? reset($value['value']['metrices']) : 0;
				$response[$counter]['unique'] = (isset($value['value']['metrices'][1]) ) ? $value['value']['metrices'][1]: 0;
				$counter++;
			}
		}
		return $response;
	}

	/**
	 * format result of "sessions" APIs before sending it to user
	 */
	public function format_sessions_response($result){

		$response = [];
		$counter = 0;
		if(!empty($result) && count($result) > 0) {
			foreach($result as $key=>$value){
				$response[$counter]['week'] = (isset($value['type']['dimension'])) ? reset($value['type']['dimension']) : "";
				$response[$counter]['sessions'] = (isset($value['value']['metrices'])) ? reset($value['value']['metrices']) : 0;
				$year = substr($response[$counter]['week'], 0, 4);
				$week = substr($response[$counter]['week'], 4, 6);
				$response[$counter]['week'] = "Week #".$week."(".$year.")";
				$counter++;
			}
		}
		return $response;
	}

	 public function validatePassword($password_db,$password,$salt){
          //return $this->password === hash('sha512',hash('sha512',$password).$this->salt, false);
          if ($password_db == hash('sha512',hash('sha512',$password).$salt, false)) {
              return true;
          }
          return false;
      }
	

}