<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MatchedRequests extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index() {
		Library_Auth_Common::check_allowed_request();

		if (!LoginAuth::is_user_have_admin_access()) {
			print BackEnd_Login_View::build_no_access_html();
			exit(0);
		} else {
			print BackEnd_MatchedRequests_View::build_html();
		}

		/*
		if (LoginAuth::is_user_have_access(true)) {
			print BackEnd_GHRequests_View::build_html();
		} else {
			print BackEnd_Login_View::build_no_access_html();
		}
		//print BackEnd_Segments_View::build_html();
		//$this->load->view('welcome_message');
		*/
	}

	// temporary
	function get_bitcoin_price_temp() {
		print $bitcoin_price = Bitcoin::get_bitcoin_price();
	}

	public function get_image_receipt($id) {
		Library_Auth_Common::check_allowed_request();
		if ($id > 0) {
			$data = DB_MatchedRequests::get($id);
			if (count($data) > 0) {
				$user_id = LoginAuth::get_session_user_id();

				$is_access = false;
				if ($user_id == $data['phrequest_user_id'] || $user_id == $data['ghrequest_user_id']) {
					$is_access = true;
				}

				if (LoginAuth::is_user_have_admin_access()) {
					$is_access = true;
				}

				if ($is_access) {
					//$destination = $_SERVER['DOCUMENT_ROOT']."/../../image_receipts/".$id.".png";
					$uploads_dir = Registry::get("uploads_dir")."/image_receipts/";
					if (!is_dir($uploads_dir)) {
						@mkdir($uploads_dir);
					}
					$destination = Registry::get("uploads_dir")."/image_receipts/".$id.".png";
					if (is_file($destination)) {
						//print "in here\n";
						header("Content-type: image/png");
						readfile("$destination");
						exit;
					} 
				}
			}
		}
		$destination = $_SERVER['DOCUMENT_ROOT']."/images/no-image-box.png";
		header("Content-type: image/png");
		readfile("$destination");
		exit;
	}

	function match_request_for_all_users() {
		 MatchedRequest::match_all_gh_requests();
	}

	// confirms a request
	public function confirm_request($matchedrequest_id) {
		Library_Auth_Common::check_allowed_request();

		$obj_request_data = Common::load_request_data();
		//print "in here\n";
		//DailyMaintenance::update_daily_growths();
		//print_r($obj_request_data);
		//print_r($_POST);
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->is_already_confirmed = false;

		$user_id = LoginAuth::get_session_user_id();
		// verify that we are the ph
		$db_mr_model = new DB_MatchedRequests();
		// or if we are a manager
		if ($matchedrequest_id > 0 && $matchedrequest_id != "") {
			$temp_data = $db_mr_model->get($matchedrequest_id);
			if (count($temp_data) > 0) {
				// or if we are a manager
				if ($user_id == $temp_data['ghrequest_user_id'] || LoginAuth::is_user_have_admin_access()) {
					$result = $db_mr_model->confirm_request($matchedrequest_id);
					if ($result->is_success) {
						$obj_result->is_success = true;
					} else {
						if ($db_mr_model->is_confirmed($matchedrequest_id)) {
							$obj_result->is_already_confirmed = true;
						}
					}
					
					//$obj_result->is_success = true;
					//$is_verified = true;
					//$is_submit_image = true;
				}
			}
		}
		print json_encode($obj_result);
	}

	// cancel a request
	public function cancel_request_by_gh($matchedrequest_id) {
		Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();
		//print_r($obj_request_data);
		//print_r($_POST);
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		$user_id = LoginAuth::get_session_user_id();
		// verify that we are the ph
		$db_mr_model = new DB_MatchedRequests();
		// or if we are a manager
		if ($matchedrequest_id > 0 && $matchedrequest_id != "") {
			$temp_data = $db_mr_model->get($matchedrequest_id);
			if (count($temp_data) > 0) {
				if ($user_id == $temp_data['ghrequest_user_id']) {
					$result = $db_mr_model->cancel_no_receipt($matchedrequest_id, $user_id);
					if ($result->is_success) {
						$obj_result->is_success = true;
					}
				}
			}
		}
		print json_encode($obj_result);
	}

	// admin_cancel
	public function clear_image_receipt($matchedrequest_id) {
		Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();
		//print_r($obj_request_data);
		//print_r($_POST);
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		$user_id = LoginAuth::get_session_user_id();
		// verify that we are the ph
		$db_mr_model = new DB_MatchedRequests();
		// or if we are a manager
		if ($matchedrequest_id > 0 && $matchedrequest_id != "") {
			$temp_data = $db_mr_model->get($matchedrequest_id);
			if (count($temp_data) > 0) {
				if (LoginAuth::is_user_have_admin_access()) {
					$arr_criteria = Array();
					$arr_criteria['id'] = $matchedrequest_id;
					$data = Array();
					$data['image_receipt_gm_date_time'] = null;
					$data['is_have_image_receipt'] = 'N';
					$number_affected = $db_mr_model->update($data,$arr_criteria);
					//print $db_mr_model->get_last_query();
					if ($number_affected == 1) {
						$obj_result->is_success = true;
					}
				}
			}
		}
		print json_encode($obj_result);
	}

	// admin_cancel
	public function admin_cancel($matchedrequest_id) {
		Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();
		//print_r($obj_request_data);
		//print_r($_POST);
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		$user_id = LoginAuth::get_session_user_id();
		// verify that we are the ph
		$db_mr_model = new DB_MatchedRequests();
		// or if we are a manager
		if ($matchedrequest_id > 0 && $matchedrequest_id != "") {
			$temp_data = $db_mr_model->get($matchedrequest_id);
			if (count($temp_data) > 0) {
				if (LoginAuth::is_user_have_admin_access()) {
					$result = $db_mr_model->cancel_request($matchedrequest_id, $user_id, DB_MatchedRequests::$ADMIN_CANCELLED_REASON_TYPE_ID);
					if ($result->is_success) {
						$obj_result->is_success = true;
					}
				}
			}
		}
		print json_encode($obj_result);
	}

	public function accept_and_set_image_receipt() {
		Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();
		$matchedrequest_id = 0;

		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->matchedrequest_id)) {
				$matchedrequest_id = $obj_request_data->matchedrequest_id;
			}
		}
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		$user_id = LoginAuth::get_session_user_id();

		$is_verified = false;
		$is_submit_image = false;
		
		// verify that we are the ph
		$db_mr_model = new DB_MatchedRequests();
		$temp_data = $db_mr_model->get($matchedrequest_id);
		if (count($temp_data) > 0) {
			if ($user_id == $temp_data['phrequest_user_id']) {
				if ($temp_data['is_have_image_receipt'] == "N") {
					$is_verified = true;
				}
			}
		}

		if ($is_verified) {
			// upload
			$result = MatchedRequest::set_image_receipt_wrapper($matchedrequest_id);
			if ($result->is_success) {
				$obj_result->is_success = true;
			}
		}

		//$this->image_lib->resize();
		unlink($_FILES['file']['tmp_name']);

		print json_encode($obj_result);
	}
	
	/* upload image receipt */
	// could just pass in get with id
	public function image_receipt_upload() {
		Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();
		$matchedrequest_id = 0;
		//print_r($obj_request_data);
		//print_r($_POST);
		// need to check if this will work on remote server
		if (isset($_POST['matchedrequest_id'])) {
			$matchedrequest_id = $_POST['matchedrequest_id'];
		}
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->matchedrequest_id)) {
				$matchedrequest_id = $obj_request_data->data->matchedrequest_id;
			}
		}
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		$user_id = LoginAuth::get_session_user_id();

		$is_verified = false;
		$is_submit_image = false;
		
		// verify that we are the ph
		$db_mr_model = new DB_MatchedRequests();
		$temp_data = $db_mr_model->get($matchedrequest_id);
		if (count($temp_data) > 0) {
			if ($user_id == $temp_data['phrequest_user_id']) {
				if ($temp_data['is_have_image_receipt'] == "N") {
					$is_verified = true;
					$is_submit_image = true;
				}
			}
		}

		//$is_verified = false;


		if ($is_submit_image) {
			$filename = $_FILES['file']['tmp_name'];
			$tmp_file = $filename;
			//$destination = $_SERVER['DOCUMENT_ROOT']."/../../image_receipts/". $filename;
			$filename = $_FILES['file']['name'];

			$uploads_dir = Registry::get("uploads_dir")."/";
			if (!is_dir($uploads_dir)) {
				@mkdir($uploads_dir);
			}

			$destination = Registry::get("uploads_dir")."/image_receipts/".$id.".png";

			$uploads_dir = Registry::get("uploads_dir")."/image_receipts/";
			if (!is_dir($uploads_dir)) {
				@mkdir($uploads_dir);
			}
			$destination = Registry::get("uploads_dir")."/image_receipts/".$matchedrequest_id.".png";
			//print "destination is $destination\n";

			//move_uploaded_file($tmp_file, $destination);
			/*
			$this->load->library('image_lib');
			$settings['maintain_ratio'] = TRUE;
			$settings['image_library'] = 'gd2';
			$settings['create_thumb'] = TRUE;
			$settings['quality'] = '100%';
			$settings['width'] = 400;
			$settings['height'] = 600;
			$settings['new_image'] = $destination;
			$settings['source_image'] = $tmp_file;
			$this->load->library('image_lib',$settings); 
			if ( !$this->image_lib->resize()){
				// if got fail.
				$error = $this->image_lib->display_errors();	
				//print_r($error);
				//print "in here error\n";
			} else {
				//print "no errror\n";
			}
			*/
			imagepng(imagecreatefromstring(file_get_contents($tmp_file)), $destination);
		}

		if ($is_verified) {
			/*
			// upload
			$result = MatchedRequest::set_image_receipt_upload($matchedrequest_id);
			if ($result->is_success) {
				$obj_result->is_success = true;
			}
			*/
			$obj_result->is_success = true;
		}


		//$this->image_lib->resize();
		unlink($_FILES['file']['tmp_name']);

		print json_encode($obj_result);
	}

	// get active matched requests for user
	public function get_data($user_id = 0) {
		Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();
		$status_id = DB_MatchedRequests::$ACTIVE_STATUS_ID;
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->user_id)) {
			}
		}
		if (is_object($obj_request_data) && isset($obj_request_data->status_id)) {
			if (isset($obj_request_data->status_id)) {
				$status_id = $obj_request_data->status_id;
			}
		}

		if (!LoginAuth::is_user_have_admin_access()) {
			print BackEnd_Login_View::build_no_access_html();
			exit(0);
		}


		//$user_id = LoginAuth::get_session_user_id();
		//print "user id is $user_id<br>";

		$data = Array();
		
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		//print "in here<br>\n";
		$arr_data = MatchedRequest::get_arr_matched_requests_for_user($user_id, $status_id);
		$obj_result->is_success = true;
		$obj_result->arr_data = $arr_data;
		$obj_result->user_id = $user_id;
		print json_encode($obj_result);
	}

	// get active matched requests for user
	public function get_active_matched_requests_for_user() {
		Library_Auth_Common::check_allowed_request();
		$user_id = 0;
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->user_id)) {
			}
		}

		$user_id = LoginAuth::get_session_user_id();

		$data = Array();
		
		$obj_result = new Stdclass();
		$obj_result->is_success = false;


		$arr_data = MatchedRequest::get_arr_active_matched_requests_for_user($user_id);
		$obj_result->is_success = true;
		$obj_result->arr_data = $arr_data;
		$obj_result->user_id = $user_id;
		print json_encode($obj_result);
	}

	// get completed or cancelled
	public function get_completed_cancelled_for_user() {
		Library_Auth_Common::check_allowed_request();
		$user_id = 0;
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->user_id)) {
			}
		}

		$user_id = LoginAuth::get_session_user_id();

		$data = Array();
		
		$obj_result = new Stdclass();
		$obj_result->is_success = false;


		$arr_data = MatchedRequest::get_arr_completed_cancelled_for_user($user_id);
		$obj_result->is_success = true;
		$obj_result->arr_data = $arr_data;
		$obj_result->user_id = $user_id;
		print json_encode($obj_result);
	}


}
?>
