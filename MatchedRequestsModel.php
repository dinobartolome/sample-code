<?

class DB_MatchedRequests extends CI_Model {

	//static $DELETED_STATUS_ID = 10;

	static $TABLE_NAME = "matched_requests";

	static $ACTIVE_STATUS_ID = 1;
	static $DELETED_STATUS_ID = 2;
	static $CANCELLED_STATUS_ID = 3;
	static $COMPLETED_STATUS_ID = 4;

	// dont use rejected just cancelled
	static $REJECTED_STATUS_ID = 5;

	static $CANCELLED_COMPLETED_STATUS_ID = 30;


	static $NO_RECEIPT_CANCELLED_REASON_TYPE_ID = 5;
	static $NO_CONFIRM_CANCELLED_REASON_TYPE_ID = 6;
	static $GENERAL_CANCELLED_REASON_TYPE_ID = 10;
	static $ADMIN_CANCELLED_REASON_TYPE_ID = 20;
 
	// maybe we have rejected reason


	public function __construct() {
           //$this->load->database();
		//print "in here\n";
	}

	function run_query() {
		$this->load->database();
		$data['company_name'] = "%B%";
		$sql = "SELECT id,name FROM customers where name like ".$this->db->escape($data['company_name'])." LIMIT 1";
		print "sql is $sql\n";
		$query = $this->db->query($sql);
		$row = $query->row_array();
		$row_id = $row['id'];
		print_r($row);
		exit(0);
	}

	function get($id) {
		$this->load->database();
		$field_names = self::get_field_names();
		$sql = "SELECT $field_names FROM ".self::$TABLE_NAME." where id = ".$this->db->escape($id)." limit 1";
		//print "sql is $sql\n";
		$query = $this->db->query($sql);
		$row = $query->row_array();
		return $row;
	}

	function arr_get_field_names() {
		$arr[] = "id";
		$arr[] = "ghrequest_id";
		$arr[] = "phrequest_id";
		$arr[] = "amount";
		$arr[] = "gm_created";
		$arr[] = "gm_modified";
		$arr[] = "status_id";
		$arr[] = "is_have_image_receipt";
		$arr[] = "is_confirmed";
		$arr[] = "image_receipt_gm_date_time";
		$arr[] = "ghrequest_user_id";
		$arr[] = "phrequest_user_id";
		$arr[] = "bitcoin_amount";
		$arr[] = "rejected_by";
		$arr[] = "completed_gm_date_time";
		// if 0 cancelled by system
		$arr[] = "cancelled_by_user_id";
		$arr[] = "cancelled_reason_type_id";
		$arr[] = "expiration_gm_date_time";
		$arr[] = "extended_expiration_gm_date_time";

		return $arr;
	}

	function get_field_names() {
		$arr = self::arr_get_field_names();
		$value = "";
		for($n=0;$n<count($arr);$n++) {
			$value .= self::$TABLE_NAME.".".$arr[$n].", ";
		}
		$value = rtrim($value, ", ");
		return $value;
	}

	function search($arr_criteria) {
		$this->load->database();
		$sql_where = self::get_sql_where($arr_criteria);

		if ($sql_where == "") {
		} else {
			$sql_where .= " and ";
		}

		$sql_where .= " ".self::$TABLE_NAME.".status_id <> ".self::$DELETED_STATUS_ID."";


		if (isset($arr_criteria['order_by'])) {
			$order_by_direction = "asc";
			if (isset($arr_criteria['order_by_direction'])) {
				$order_by_direction = $arr_criteria['order_by_direction'];
			}
                        $order_by = "order by ".self::$TABLE_NAME.".".$arr_criteria['order_by']." $order_by_direction";
		} else {
                        $order_by = "order by ".self::$TABLE_NAME.".id asc";
                }

		$field_names = self::get_field_names();
		$from_tables = self::$TABLE_NAME;

		//print_r($arr_criteria);

		if (isset($arr_criteria['join_ph_user_tbl']) && $arr_criteria['join_ph_user_tbl'] >= 0) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$ph_table_name = "ph_user_tbl";
			$sql_where .= " ".self::$TABLE_NAME.".phrequest_user_id = ".$ph_table_name.".id";
			$field_names = DB_Users::get_field_names($ph_table_name).", " . $field_names;
			$from_tables .= ", ".DB_Users::$TABLE_NAME." as $ph_table_name";
		}

		if (isset($arr_criteria['join_gh_user_tbl']) && $arr_criteria['join_gh_user_tbl'] >= 0) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$gh_table_name = "gh_user_tbl";
			$sql_where .= " ".self::$TABLE_NAME.".ghrequest_user_id = ".$gh_table_name.".id";
			$field_names = DB_Users::get_field_names($gh_table_name).", " . $field_names;
			$from_tables .= ", ".DB_Users::$TABLE_NAME." as $gh_table_name";
		}

		if ($sql_where != "") {
			$sql_where = " where $sql_where";
		}

		$sql = "select $field_names FROM $from_tables $sql_where $order_by";
		//print "sql is $sql\n";

		$query = $this->db->query($sql);
		$row = $query->row_array();
		//$row_id = $row['total'];
		//print_r($row);
		$arr_data = Array();
		foreach ($query->result_array() as $row) {
			$arr_data[] = $row;
		}

		//print_r($arr_data);

		return $arr_data;
		//exit(0);
		
	}


	function get_sql_where($arr_criteria) {
		$this->load->database();
		$sql_where = "";
		if (isset($arr_criteria['status_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".status_id", $arr_criteria['status_id']);
		}

		if (isset($arr_criteria['id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".id", $arr_criteria['id']);
		}

		if (isset($arr_criteria['amount'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".amount", $arr_criteria['amount']);
		}

		if (isset($arr_criteria['phrequest_user_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".phrequest_user_id", $arr_criteria['phrequest_user_id']);
		}

		if (isset($arr_criteria['ghrequest_user_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".ghrequest_user_id", $arr_criteria['ghrequest_user_id']);
		}

		if (isset($arr_criteria['user_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= "(".self::$TABLE_NAME.".ghrequest_user_id = ".$this->db->escape($arr_criteria['user_id']);
			$sql_where .= " or ";
			$sql_where .= "".self::$TABLE_NAME.".phrequest_user_id = ".$this->db->escape($arr_criteria['user_id']).")";
		}

		if (isset($arr_criteria['search'])) {
                        if ($sql_where == "") {
                        } else {
                                $sql_where .= " and ";
                        }
                        $sql_where .= "".self::$TABLE_NAME.".name like ".$this->db->escape("%".$arr_criteria['search']."%")."";
                }

		return $sql_where;
	}

	function get_drop_down_options($id) {
		$arr_options = $this->get_arr_values();
		//print_r($arr_options);
		$s = Library_Html_Util::build_drop_down_options($id, $arr_options);
		return $s;

	}

	function get_arr_values() {
		//print "blah\n";
		//print_r($this);
		//print_r($this->db);
		//exit(0);
		$sql = "SELECT id,name FROM ".self::$TABLE_NAME." order by name";
		//print "sql is $sql\n";
		$query = $this->db->query($sql);

		foreach ($query->result_array() as $row) {
			$option['key'] = $row['id'];
			$option['value'] = $row['name'];
			$arr_options[] = $option;
		}
		return $arr_options;
	}

	function get_last_insert_id() {
		$this->load->database();
		return $this->db->insert_id();
	}

	function get_last_query() {
                $this->load->database();
		return $this->db->last_query();
	}



	function save($id, $data) {
		$this->load->database();

		/*
		if (isset($data['id') && $data['id'] != "" && $data['id'] > 0) {
			if ($id == 0) {
				$id = $data['id'];
			}
		}
		*/
		
		$data['gm_modified'] = Library_DB_Util::time_to_gm_db_time();
		unset($data['id']);
		if ($id > 0) {
			$arr_sql_where = Array();
			$arr_sql_where['id'] = $id;
			unset($data['gm_created']);
			$this->db->update(self::$TABLE_NAME, $data, $arr_sql_where);
		} else {
			$data['gm_created'] = Library_DB_Util::time_to_gm_db_time();
			$this->db->insert(self::$TABLE_NAME, $data);
		}
		return $this->db->affected_rows();
	}

	function update($data, $arr_criteria) {
                $this->load->database();

		$sql_where = self::get_sql_where($arr_criteria);
		
		$set_sql = "";
		$data['gm_modified'] = Library_DB_Util::time_to_gm_db_time();
		if (is_array($data) && count($data) > 0) {
			foreach($data as $key => $value) {
				$set_sql .= "$key = ".$this->db->escape($value).", ";
			}
			$set_sql = rtrim($set_sql, ", ");
			$set_sql = "set $set_sql";
		}

		if ($sql_where != "") {
			if ($sql_where != "") {
				$sql_where = "where $sql_where";
			}
			$table_name = self::$TABLE_NAME;

			$sql = "update $table_name $set_sql $sql_where";
			//print "sql is $sql\n";
			$query = $this->db->query($sql);
		} else {
			// dont do query if no criteria
			return 0;
		}
                return $this->db->affected_rows();
        }


	function delete($id) {
                $this->load->database();

                if ($id > 0) {
                        $arr_sql_where = Array();
                        $arr_sql_where['id'] = $id;
                        unset($data['created']);

                        $data = Array();
                        $data['status_id'] = self::$DELETED_STATUS_ID;
                        $this->db->update(self::$TABLE_NAME, $data, $arr_sql_where);
                }
                return $this->db->affected_rows();
        }


	/* we watn to see if user has active matched requests */
	/* disallow user from creating new ph */
	function is_have_active_matched_request($user_id) {
                $this->load->database();
		$obj_result = new stdclass();
		$obj_result->is_success = false;

		$arr_search_criteria = Array();
		$arr_search_criteria['amount']['>'] = 0;
		$arr_search_criteria['status_id'] = DB_MatchedRequests::$ACTIVE_STATUS_ID;
		$arr_search_criteria['phrequest_user_id'] = $user_id;
		$arr_data = $this->search($arr_search_criteria);
		if (count($arr_data) > 0) {
			return true;
		}
		return false;
	}

	// do update to three tables
	function match_to_ph_request($gh_request_id) {
		$obj_result = new stdclass();
		$obj_result->is_success = false;
		$obj_result->is_unable_to_find_gh_data = false;
		$obj_result->is_able_to_allocate = false;
		$obj_result->is_not_approved_for_matching = false;
		$obj_result->number_of_matches = 0;

                $this->load->database();

		$db_ghr_model = new DB_GHRequests();
		$db_phr_model = new DB_PHRequests();
		//print "In here\n";

		$gh_data = $db_ghr_model::get($gh_request_id);
		if (count($gh_data) == 0) {
			$obj_result->is_unable_to_find_gh_data = true;
			return $obj_result;
		}

		$db_user_model = new DB_Users();

		$gh_user_data = $db_user_model->get($gh_data['user_id']);

		if (count($gh_user_data) > 0) {
			if ($gh_user_data['is_approved_for_matching'] == "N") {
				$obj_result->is_not_approved_for_matching = true;
				return $obj_result;
			}
		} else {
			$obj_result->is_not_approved_for_matching = true;
			return $obj_result;
		}

		
		$gh_amount_available = $gh_data['amount_available'];
		$gh_user_id = $gh_data['user_id'];
		$gh_total_amount = $gh_data['amount'];


		$amount_allocated = 0;
		// insert matched_request

		// india special match
		$arr_search_criteria = Array();

		$is_india_match_to_special_admin = false;
		$random_number = rand(0,100);
		if ($random_number <= 17) { // approximately every sixth one
			$is_india_match_to_special_admin = true;
			
		}

		/* take out temporarily indian logic
		if ($gh_user_data['country'] == "India") {
			if ($is_india_match_to_special_admin) {
				// match to only 1 - 60
				$arr_search_criteria['user_id']['>='] = 0;
				$arr_search_criteria['user_id']['<='] = 60;
			} else {
				// only match india account
				
				$arr_search_criteria['country'] = "India";
			}
		} else {
			if ($gh_user_data['is_allow_gh_to_match_restricted_countries'] == 'Y') {
				// to allow india
			} else {
				// make sure that ph is not owned from soemone that is in india
				$arr_search_criteria['country']['<>'] = "India";
			}
		}
		*/

		if ($gh_user_data['is_test_match_account'] == "Y") {
			$arr_search_criteria['is_test_match_account'] = "Y";
		} else {
			$arr_search_criteria['is_test_match_account'] = "N";
		}
		

		// loop through all ph requests that have available balance order by created asc
		$arr_search_criteria['amount_available']['>'] = 0;
		$arr_search_criteria['status_id'] = DB_PHRequests::$ACTIVE_STATUS_ID;
		$arr_search_criteria['user_id']['<>'] = $gh_user_id;
		$arr_search_criteria['join_user_table'] = $gh_user_id;
		$arr_search_criteria['order_by'] = "gm_created";
		$arr_search_criteria['order_by_direction'] = "asc";
		$arr_data = $db_phr_model->search($arr_search_criteria);
		PlatformLogs::log("matched_requests_ph_query", "sql is ".$db_phr_model->get_last_query()."\n");

		// allow matches if ph is allowed to match for gh requests for restricted countries
		if ($gh_user_data['country'] == "India") {
			$arr_search_criteria = Array();
			$arr_search_criteria['amount_available']['>'] = 0;
			$arr_search_criteria['is_allow_ph_to_match_restricted_countries'] = "Y";
			$arr_search_criteria['status_id'] = DB_PHRequests::$ACTIVE_STATUS_ID;
			$arr_search_criteria['user_id']['<>'] = $gh_user_id;
			$arr_search_criteria['join_user_table'] = $ph_user_id;
			$arr_search_criteria['order_by'] = "gm_created";
			$arr_search_criteria['order_by_direction'] = "asc";
			$arr_more_data = $db_phr_model->search($arr_search_criteria);
			$arr_data = array_merge($arr_data, $arr_more_data);
		}

		//print " gh amount is $gh_amount_available\n";
		//print_r($arr_data);

		$n = 0;
		while($n < count($arr_data) && $gh_amount_available > 0) {
			$amount = 0;


			$now_gm_date_time = Library_DB_Util::time_to_gm_db_time($this->db);
			$expiration_gm_date_time = Library_DB_Util::time_to_gm_db_time($this->db,strtotime("+2 Days"));
			$extended_expiration_gm_date_time = Library_DB_Util::time_to_gm_db_time($this->db,strtotime("+3 Days"));

			if ($arr_data[$n]['amount_available'] > 0) {
				$ph_id = $arr_data[$n]['id'];
				$ph_amount_available = $arr_data[$n]['amount_available'];
				$ph_total_amount = $arr_data[$n]['amount'];
				if ($gh_amount_available >= $ph_amount_available) {
					// use full amout of ph
					$amount = $ph_amount_available;
				} else {
					// use full amount of gh
					$amount = $gh_amount_available;
				}
				// trying to allocate amount
				
				if ($amount == 0) {
					$is_do = false;
				} else {
					$is_do = true;
				}

				$ph_user_data = $db_user_model->get($arr_data[$n]['user_id']);
				if ($ph_user_data['status_id'] == DB_Users::$ACTIVE_STATUS_ID || $ph_user_data['status_id'] == DB_Users::$SUSPENDED_STATUS_ID) {
				} else {
					$is_do = false;
				}
				// grab bitcoin price
				/*
				https://api.bitcoinaverage.com/ticker/USD/
				*/


				if ($is_do ) {
					// insert
					// then send email
					$data = Array();
					//$data['ghrequest_user_id'] = $gh_user_id;
					//$data['phrequest_user_id'] = $arr_data[$n]['user_id'];
					$this->save(0, $data);


					$insert_id = $this->get_last_insert_id();
					if ($insert_id > 0) {
						PlatformLogs::log("matched_requests", "match for $amount for $gh_request_id and $ph_id and mr_id is $insert_id\n");
						PlatformLogs::log("matched_requests", $arr_data[$n]);
						PlatformLogs::log("matched_requests", $gh_data);

						$number_affected = self::match_gh_id_to_ph_id($gh_request_id, $ph_id, $insert_id, $amount);

						PlatformLogs::log("matched_requests", "number affected is $number_affected");


						if ($number_affected == 4) {
							//print "is success\n";
							// we should be good here
							$obj_result->is_success = true;
							$obj_result->is_able_to_allocate = true;
							$obj_result->number_of_matches++;


							Emailer::send_matched_created_email($insert_id);
							// send email
							
							$gh_amount_available -= $amount;
							$amount_allocated += $amount;
						} else {
							// query above all or nothing if not updated then it means 
							// error
							$data = Array();
							$data['ghrequest_user_id'] = 0;
							$data['phrequest_user_id'] = 0;
							$data['status_id'] = self::$DELETED_STATUS_ID;
							$this->save($insert_id, $data);
							$insert_id = $this->get_last_insert_id();
							$obj_result->is_success = false;
							$obj_result->is_able_to_allocate = false;
						}
					}
					
				}

			}
			/*
			print "amount is $amount\n";
			print_r($arr_data[$n]);
			exit(0);
			*/
			$n++;
	
		} // end while loop
		return $obj_result;
	}

	// needs an existing mr_id
	function match_gh_id_to_ph_id($gh_request_id, $ph_id, $mr_id, $amount) {
                $this->load->database();

		$db_phr_model = new DB_PHRequests();
		$db_ghr_model = new DB_GHRequests();

		$ph_data = $db_phr_model->get($ph_id);
		/*
		print "ph data\n";
		print_r($ph_data);
		*/
		$gh_data = $db_ghr_model->get($gh_request_id);
		/*
		print "gh data\n";
		print_r($gh_data);
		*/
		if (count($ph_data) > 0 && count($gh_data) > 0) {
		} else {
			print "returning 0\n";
			return 0;
		}

		$now_time = time();
		$now_gm_date_time = Library_DB_Util::time_to_gm_db_time($this->db, $now_time);
		$expiration_gm_date_time = Library_DB_Util::time_to_gm_db_time($this->db,strtotime("+2 Days", $now_time));
		$extended_expiration_gm_date_time = Library_DB_Util::time_to_gm_db_time($this->db,strtotime("+3 Days", $now_time));

		$bitcoin_amount = 0;
		$bitcoin_price = Bitcoin::get_bitcoin_price();
		if ($bitcoin_price != "") {
			$bitcoin_amount = $amount/$bitcoin_price;
			
		}

		// update gh request
		$sql_set = "".DB_GHRequests::$TABLE_NAME.".amount_available = ".DB_GHRequests::$TABLE_NAME.".amount_available - ".$this->db->escape($amount)."";
		$sql_set .= ", ".DB_GHRequests::$TABLE_NAME.".amount_matched = ".DB_GHRequests::$TABLE_NAME.".amount_matched + ".$this->db->escape($amount)."";

		// updated ph request
		$sql_set .= ", ".DB_PHRequests::$TABLE_NAME.".amount_matched = ".DB_PHRequests::$TABLE_NAME.".amount_matched + ".$this->db->escape($amount)."";
		$sql_set .= ", ".DB_PHRequests::$TABLE_NAME.".amount_available = ".DB_PHRequests::$TABLE_NAME.".amount_available - ".$this->db->escape($amount)."";
		$sql_set .= ", ".DB_PHRequests::$TABLE_NAME.".amount_unconfirmed = ".DB_PHRequests::$TABLE_NAME.".amount_unconfirmed + ".$this->db->escape($amount)."";

		// update matched_requests
		$sql_set .= ", ".self::$TABLE_NAME.".phrequest_user_id = ".$this->db->escape($ph_data['user_id'])."";
		$sql_set .= ", ".self::$TABLE_NAME.".ghrequest_user_id = ".$this->db->escape($gh_data['user_id'])."";

		$sql_set .= ", ".self::$TABLE_NAME.".ghrequest_id = ".$this->db->escape($gh_request_id)."";
		$sql_set .= ", ".self::$TABLE_NAME.".phrequest_id = ".$this->db->escape($ph_id)."";
		$sql_set .= ", ".self::$TABLE_NAME.".amount = ".$this->db->escape($amount)."";
		$sql_set .= ", ".self::$TABLE_NAME.".bitcoin_amount = ".$this->db->escape($bitcoin_amount)."";

		$sql_set .= ", ".self::$TABLE_NAME.".expiration_gm_date_time = ".$this->db->escape($expiration_gm_date_time )."";
		$sql_set .= ", ".self::$TABLE_NAME.".extended_expiration_gm_date_time = ".$this->db->escape($extended_expiration_gm_date_time )."";

		// update Users table increment second column ph unconfirmed when matched received. move it to third column when confirmed or remove when have to cancel 

		// update total ph
		$sql_set .= ", ".DB_Users::$TABLE_NAME.".unconfirmed_ph = ".DB_Users::$TABLE_NAME.".unconfirmed_ph + ".$this->db->escape($amount)."";
		// ask about this
		// total ph gets added when ph is created
		//$sql_set .= ", ".DB_Users::$TABLE_NAME.".total_ph = ".DB_Users::$TABLE_NAME.".total_ph + ".$this->db->escape($amount)."";
	
		// do where
		$sql_where = "".DB_PHRequests::$TABLE_NAME.".id = $ph_id";
		$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".id = $gh_request_id";
		$sql_where .= " and ".self::$TABLE_NAME.".id = $mr_id";

		$sql_where .= " and ".DB_Users::$TABLE_NAME.".id = ".$this->db->escape($ph_data['user_id'])."";

		// make sure user is still active or suspended
		$sql_where .= " and (".DB_Users::$TABLE_NAME.".status_id = ".DB_Users::$ACTIVE_STATUS_ID."";
		$sql_where .= " or ".DB_Users::$TABLE_NAME.".status_id = ".DB_Users::$SUSPENDED_STATUS_ID.")";
		//$sql_where .= " and ".DB_Users::$TABLE_NAME.".is_approved_for_matching = 'Y'";

		// make sure gh amounts are still valid
		$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".amount_available - ".$this->db->escape($amount)." >= 0";
		$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".amount_matched + ".$this->db->escape($amount)." <= ".DB_GHRequests::$TABLE_NAME.".amount";
		$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".status_id  = ".DB_GHRequests::$ACTIVE_STATUS_ID."";

		

		// make sure PH amounts are still valid
		$sql_where .= " and ".DB_PHRequests::$TABLE_NAME.".amount_available - ".$this->db->escape($amount)." >= 0";
		$sql_where .= " and ".DB_PHRequests::$TABLE_NAME.".amount_matched + ".$this->db->escape($amount)." <= ".DB_PHRequests::$TABLE_NAME.".amount";
		$sql_where .= " and ".DB_PHRequests::$TABLE_NAME.".status_id  = ".DB_PHRequests::$ACTIVE_STATUS_ID."";


		$sql_tables = "".self::$TABLE_NAME.", ".DB_GHRequests::$TABLE_NAME.", ".DB_PHRequests::$TABLE_NAME.", ".DB_Users::$TABLE_NAME."";
		$sql = "update $sql_tables set $sql_set where $sql_where";
		//print "sql is $sql\n";


		$query = $this->db->query($sql);
		//print "sql is $sql\n";
		PlatformLogs::log("matched_requests", "inside match ph and gh sql is $sql");


		$number_affected = $this->db->affected_rows();
		return $number_affected;
	}

	// cancel matched request may need ability to block user
	// reject if ph does not send money
	//function cancel_matched_request($id) {
	function cancel_no_receipt($id, $blocked_by_user_id) {
		$obj_result = new stdclass();
		$obj_result->is_success = false;
		$obj_result->is_unable_to_find_mr_data = false;

                $this->load->database();

		$db_ghr_model = new DB_GHRequests();
		$db_phr_model = new DB_PHRequests();
		//print "In here\n";

		$mr_data = $this->get($id);
		//print_r($mr_data);
		if (count($mr_data) == 0) {
			$obj_result->is_unable_to_find_mr_data = true;
			return $obj_result;
		}

		$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();
		$ph_id = $mr_data['phrequest_id'];
		$gh_id = $mr_data['ghrequest_id'];
		$amount = $mr_data['amount'];
		//print "id is $id\n";
		
		//print_r($mr_data);

		$gh_data = $this->db->get($gh_id);
		if (count($gh_data) == 0) {
			$obj_result->is_unable_to_find_gh_data = true;
			return $obj_result;
		}

		$ph_data = $this->db->get($ph_id);
		if (count($ph_data) == 0) {
			$obj_result->is_unable_to_find_ph_data = true;
			return $obj_result;
		}


		// update gh request
		// give back amount available
		$sql_set = "";
		$sql_set .= "".DB_GHRequests::$TABLE_NAME.".amount_available = ".DB_GHRequests::$TABLE_NAME.".amount_available + ".$this->db->escape($amount).", ";

		// remove matched amount
		$sql_set .= "".DB_GHRequests::$TABLE_NAME.".amount_matched = ".DB_GHRequests::$TABLE_NAME.".amount_matched - ".$this->db->escape($amount).", ";
		$sql_set .= "".DB_GHRequests::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time).", ";



		// updated ph request

		// reduce match
		$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_matched = ".DB_PHRequests::$TABLE_NAME.".amount_matched - ".$this->db->escape($amount).", ";

		// give back
		$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_available = ".DB_PHRequests::$TABLE_NAME.".amount_available + ".$this->db->escape($amount).", ";
		$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_unconfirmed = ".DB_PHRequests::$TABLE_NAME.".amount_unconfirmed - ".$this->db->escape($amount).", ";


		$sql_set .= "".DB_PHRequests::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time).", ";

		// increase number of rejects we will only grab from ph requests that dont have rejects
		$sql_set .= "".DB_PHRequests::$TABLE_NAME.".number_rejects = ".DB_PHRequests::$TABLE_NAME.".number_rejects + 1, ";
		// dont set to REJECTED
		//$sql_set .= ", ".DB_PHRequests::$TABLE_NAME.".number_rejects = ".DB_PHRequests::$REJECTED_STATUS_ID.".amount_available + ".$this->db->escape($amount)."";

		// update matched_requests
		$sql_set .= "".self::$TABLE_NAME.".status_id = ".self::$CANCELLED_STATUS_ID.", ";
		$sql_set .= "".self::$TABLE_NAME.".cancelled_by_user_id = ".$this->db->escape($blocked_by_user_id).", ";
		$sql_set .= "".self::$TABLE_NAME.".cancelled_reason_type_id = ".self::$NO_RECEIPT_CANCELLED_REASON_TYPE_ID.", ";
		$sql_set .= "".self::$TABLE_NAME.".rejected_by = 'GH', ";
		$sql_set .= "".self::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time).", ";

		// have to remove uncofirmed ph 
		$sql_set .= "".DB_Users::$TABLE_NAME.".unconfirmed_ph = ".DB_Users::$TABLE_NAME.".unconfirmed_ph - ".$this->db->escape($amount).", ";

		$sql_set .= "".DB_Users::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time).", ";

		$sql_set = rtrim($sql_set,", ");

	
		// do where
		$sql_where = "".DB_PHRequests::$TABLE_NAME.".id = $ph_id";
		$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".id = $gh_id";
		$sql_where .= " and ".self::$TABLE_NAME.".id = $id";
		$sql_where .= " and ".self::$TABLE_NAME.".phrequest_user_id = ".DB_Users::$TABLE_NAME.".id";

		$sql_where .= " and ".self::$TABLE_NAME.".status_id = ".self::$ACTIVE_STATUS_ID."";

		// make sure gh amounts are still valid
		// should do the below but need to calculate gh_total_amount
		//$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".amount_available + ".$this->db->escape($amount)." <= $gh_total_amount";
		$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".amount_matched - ".$this->db->escape($amount)." >= 0";

		

		// make sure PH amounts are still valid
		// need to grab ph_total_amount
		//$sql_where .= " and ".DB_PHRequests::$TABLE_NAME.".amount_available + ".$this->db->escape($amount)." <= $ph_total_amount";
		$sql_where .= " and ".DB_PHRequests::$TABLE_NAME.".amount_matched - ".$this->db->escape($amount)." >= 0";


		$sql_tables = "";
		$sql_tables .= "".self::$TABLE_NAME.", ";
		$sql_tables .= "".DB_GHRequests::$TABLE_NAME.", ";
		$sql_tables .= "".DB_PHRequests::$TABLE_NAME.", ";
		$sql_tables .= "".DB_Users::$TABLE_NAME.", ";

		$sql_tables = rtrim($sql_tables,", ");

		$sql = "update $sql_tables set $sql_set where $sql_where";
		//print "sql is $sql\n";


		$query = $this->db->query($sql);
		$number_affected = $this->db->affected_rows();
		//print "number affected is $number_affected\n";


		// try using sql_tables_more
		// should run two sql first one above with is_admin == N and is_manager == N 
		// set ph_user_id = blocked_status_id and blocked_reason_type_id status change
		// if we run this and get 0 number_affected then run without this code
		//print "number affected is $number_affected\n";

		if ($number_affected == 4) {

			// block user if not admin or manager
			// race condition here
			// should really incorporate this into top sql
			$db_user_model = new DB_Users();

			

			if ($mr_data['phrequest_user_id'] > 0) {
				$ph_user_data = $db_user_model->get($mr_data['phrequest_user_id']);
				//if ($ph_user_data['is_admin'] == "Y" || $ph_user_data['is_manager'] == "Y") {
				if ($ph_user_data['is_admin'] == "Y") {
					// do not block user
				} else {
					$temp_data = Array();
					$temp_data['status_id'] = DB_Users::$BLOCKED_STATUS_ID;
					$temp_data['status_reason_type_id'] = DB_Users::$NO_RECEIPT_REASON_TYPE_ID;
					$temp_data['status_changed_by_user_id'] = $blocked_by_user_id;
					$db_user_model->save($mr_data['phrequest_user_id'],$temp_data);
				}
			}

			// should probably send an email out


			// we should be good here
			$obj_result->is_success = true;
		} else {
			// query above all or nothing if not updated then it means 
			// error
			$obj_result->is_success = false;
		}
		return $obj_result;
	}

	// cancel request
	function cancel_request($id, $blocked_by_user_id, $block_reason_type_id) {
		$obj_result = new stdclass();
		$obj_result->is_success = false;
		$obj_result->is_unable_to_find_mr_data = false;

                $this->load->database();

		$db_ghr_model = new DB_GHRequests();
		$db_phr_model = new DB_PHRequests();
		//print "In here\n";

		$mr_data = $this->get($id);
		//print_r($mr_data);
		if (count($mr_data) == 0) {
			$obj_result->is_unable_to_find_mr_data = true;
			return $obj_result;
		}

		$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();
		$ph_id = $mr_data['phrequest_id'];
		$gh_id = $mr_data['ghrequest_id'];
		$amount = $mr_data['amount'];
		//print "id is $id\n";
		
		//print_r($mr_data);

		$gh_data = $this->db->get($gh_id);
		if (count($gh_data) == 0) {
			$obj_result->is_unable_to_find_gh_data = true;
			return $obj_result;
		}

		$ph_data = $this->db->get($ph_id);
		if (count($ph_data) == 0) {
			$obj_result->is_unable_to_find_ph_data = true;
			return $obj_result;
		}


		// update gh request
		// give back amount available
		$sql_set = "";
		$sql_set .= "".DB_GHRequests::$TABLE_NAME.".amount_available = ".DB_GHRequests::$TABLE_NAME.".amount_available + ".$this->db->escape($amount).", ";

		// remove matched amount
		$sql_set .= "".DB_GHRequests::$TABLE_NAME.".amount_matched = ".DB_GHRequests::$TABLE_NAME.".amount_matched - ".$this->db->escape($amount).", ";
		$sql_set .= "".DB_GHRequests::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time).", ";



		// updated ph request

		// reduce match
		$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_matched = ".DB_PHRequests::$TABLE_NAME.".amount_matched - ".$this->db->escape($amount).", ";

		// give back
		$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_available = ".DB_PHRequests::$TABLE_NAME.".amount_available + ".$this->db->escape($amount).", ";
		$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_unconfirmed = ".DB_PHRequests::$TABLE_NAME.".amount_unconfirmed - ".$this->db->escape($amount).", ";


		$sql_set .= "".DB_PHRequests::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time).", ";

		// increase number of rejects we will only grab from ph requests that dont have rejects
		$sql_set .= "".DB_PHRequests::$TABLE_NAME.".number_rejects = ".DB_PHRequests::$TABLE_NAME.".number_rejects + 1, ";
		// dont set to REJECTED
		//$sql_set .= ", ".DB_PHRequests::$TABLE_NAME.".number_rejects = ".DB_PHRequests::$REJECTED_STATUS_ID.".amount_available + ".$this->db->escape($amount)."";

		// update matched_requests
		$sql_set .= "".self::$TABLE_NAME.".status_id = ".self::$CANCELLED_STATUS_ID.", ";
		$sql_set .= "".self::$TABLE_NAME.".cancelled_by_user_id = ".$this->db->escape($blocked_by_user_id).", ";
		$sql_set .= "".self::$TABLE_NAME.".cancelled_reason_type_id = ".$block_reason_type_id.", ";
		$sql_set .= "".self::$TABLE_NAME.".rejected_by = 'GH', ";
		$sql_set .= "".self::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time).", ";

		// have to remove uncofirmed ph 
		$sql_set .= "".DB_Users::$TABLE_NAME.".unconfirmed_ph = ".DB_Users::$TABLE_NAME.".unconfirmed_ph - ".$this->db->escape($amount).", ";

		$sql_set .= "".DB_Users::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time).", ";

		$sql_set = rtrim($sql_set,", ");

	
		// do where
		$sql_where = "".DB_PHRequests::$TABLE_NAME.".id = $ph_id";
		$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".id = $gh_id";
		$sql_where .= " and ".self::$TABLE_NAME.".id = $id";
		$sql_where .= " and ".self::$TABLE_NAME.".phrequest_user_id = ".DB_Users::$TABLE_NAME.".id";

		$sql_where .= " and ".self::$TABLE_NAME.".status_id = ".self::$ACTIVE_STATUS_ID."";

		// make sure gh amounts are still valid
		// should do the below but need to calculate gh_total_amount
		//$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".amount_available + ".$this->db->escape($amount)." <= $gh_total_amount";
		$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".amount_matched - ".$this->db->escape($amount)." >= 0";

		

		// make sure PH amounts are still valid
		// need to grab ph_total_amount
		//$sql_where .= " and ".DB_PHRequests::$TABLE_NAME.".amount_available + ".$this->db->escape($amount)." <= $ph_total_amount";
		$sql_where .= " and ".DB_PHRequests::$TABLE_NAME.".amount_matched - ".$this->db->escape($amount)." >= 0";


		$sql_tables = "";
		$sql_tables .= "".self::$TABLE_NAME.", ";
		$sql_tables .= "".DB_GHRequests::$TABLE_NAME.", ";
		$sql_tables .= "".DB_PHRequests::$TABLE_NAME.", ";
		$sql_tables .= "".DB_Users::$TABLE_NAME.", ";

		$sql_tables = rtrim($sql_tables,", ");

		$sql = "update $sql_tables set $sql_set where $sql_where";
		//print "sql is $sql\n";


		$query = $this->db->query($sql);
		$number_affected = $this->db->affected_rows();

		PlatformLogs::log("cancel_matched_request", "sql is: $sql. number affected is $number_affected\n");
		//print "number affected is $number_affected\n";


		// try using sql_tables_more
		// should run two sql first one above with is_admin == N and is_manager == N 
		// set ph_user_id = blocked_status_id and blocked_reason_type_id status change
		// if we run this and get 0 number_affected then run without this code
		//print "number affected is $number_affected\n";

		if ($number_affected == 4) {

			// block user if not admin
			// race condition here
			// should really incorporate this into top sql
			$db_user_model = new DB_Users();

			

			if ($mr_data['phrequest_user_id'] > 0) {
				$ph_user_data = $db_user_model->get($mr_data['phrequest_user_id']);
				//if ($ph_user_data['is_admin'] == "Y" || $ph_user_data['is_manager'] == "Y") {
				if ($ph_user_data['is_admin'] == "Y") {
					// do not block user
				} else {
					$temp_data = Array();
					$temp_data['status_id'] = DB_Users::$BLOCKED_STATUS_ID;
					$temp_data['status_reason_type_id'] = DB_Users::$ADMIN_CANCELLED_MATCHED_REQUEST_REASON_TYPE_ID;
					$temp_data['status_changed_by_user_id'] = $blocked_by_user_id;
					$db_user_model->save($mr_data['phrequest_user_id'],$temp_data);
				}
			}

			// should probably send an email out


			// we should be good here
			$obj_result->is_success = true;
		} else {
			// query above all or nothing if not updated then it means 
			// error
			$obj_result->is_success = false;
		}
		return $obj_result;
	}

	// reject if gh does not confirm
	// hardly happens but possibility if gh user trying to scam or no time to confirm
	function reject_no_confirm($id) {
		$obj_result = new stdclass();
		$obj_result->is_success = false;
		$obj_result->is_unable_to_find_mr_data = false;

                $this->load->database();

		$db_ghr_model = new DB_GHRequests();
		$db_phr_model = new DB_PHRequests();
		//print "In here\n";

		$mr_data = $this->get($id);
		if (count($mr_data) == 0) {
			$obj_result->is_unable_to_find_mr_data = true;
			return $obj_result;
		}

		$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();
		$ph_id = $mr_data['phrequest_id'];
		$gh_id = $mr_data['ghrequest_id'];
		$amount = $mr_data['amount'];

		$gh_data = $this->db->get($gh_id);
		if (count($gh_data) == 0) {
			$obj_result->is_unable_to_find_gh_data = true;
			return $obj_result;
		}

		$ph_data = $this->db->get($ph_id);
		if (count($ph_data) == 0) {
			$obj_result->is_unable_to_find_ph_data = true;
			return $obj_result;
		}

		// update gh request
		// give back amount available
		$sql_set = "".DB_GHRequests::$TABLE_NAME.".amount_available = ".DB_GHRequests::$TABLE_NAME.".amount_available + ".$this->db->escape($amount)."";

		// remove matched amount
		$sql_set .= ", ".DB_GHRequests::$TABLE_NAME.".amount_matched = ".DB_GHRequests::$TABLE_NAME.".amount_matched - ".$this->db->escape($amount)."";
		$sql_set .= ", ".DB_GHRequests::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time)."";



		// updated ph request

		// reduce match
		$sql_set .= ", ".DB_PHRequests::$TABLE_NAME.".amount_matched = ".DB_PHRequests::$TABLE_NAME.".amount_matched - ".$this->db->escape($amount)."";

		// give back
		$sql_set .= ", ".DB_PHRequests::$TABLE_NAME.".amount_available = ".DB_PHRequests::$TABLE_NAME.".amount_available + ".$this->db->escape($amount)."";


		$sql_set .= ", ".DB_PHRequests::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time)."";

		// increase number of rejects we will only grab from ph requests that dont have rejects
		$sql_set .= ", ".DB_GHRequests::$TABLE_NAME.".number_rejects = ".DB_GHRequests::$TABLE_NAME.".number_rejects + 1";
		// dont set to REJECTED
		//$sql_set .= ", ".DB_PHRequests::$TABLE_NAME.".number_rejects = ".DB_PHRequests::$REJECTED_STATUS_ID.".amount_available + ".$this->db->escape($amount)."";

		// update matched_requests
		$sql_set .= ", ".self::$TABLE_NAME.".status_id = ".self::$REJECTED_STATUS_ID."";
		$sql_set .= ", ".self::$TABLE_NAME.".rejected_by = 'PH'";
		$sql_set .= ", ".self::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time)."";

		// have to remove uncofirmed ph 

		// user loses his uncofirmed ph even though he sent money
		$sql_set .= ", ".DB_Users::$TABLE_NAME.".unconfirmed_ph = ".DB_Users::$TABLE_NAME.".unconfirmed_ph - ".$this->db->escape($amount)."";

		$sql_set .= ", ".DB_Users::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time)."";
	
		// do where
		$sql_where = "".DB_PHRequests::$TABLE_NAME.".id = $ph_id";
		$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".id = $gh_id";
		$sql_where .= " and ".self::$TABLE_NAME.".id = $id";
		$sql_where .= " and ".self::$TABLE_NAME.".phrequest_user_id = ".DB_Users::$TABLE_NAME.".id";

		$sql_where .= " and ".self::$TABLE_NAME.".status_id = ".self::$ACTIVE_STATUS_ID."";

		// make sure gh amounts are still valid
		// should do the below but need to calculate gh_total_amount
		//$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".amount_available + ".$this->db->escape($amount)." <= $gh_total_amount";
		$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".amount_matched - ".$this->db->escape($amount)." >= 0";

		

		// make sure PH amounts are still valid
		// need to grab ph_total_amount
		//$sql_where .= " and ".DB_PHRequests::$TABLE_NAME.".amount_available + ".$this->db->escape($amount)." <= $ph_total_amount";
		$sql_where .= " and ".DB_PHRequests::$TABLE_NAME.".amount_matched - ".$this->db->escape($amount)." >= 0";


		$sql_tables = "".self::$TABLE_NAME.", ".DB_GHRequests::$TABLE_NAME.", ".DB_PHRequests::$TABLE_NAME.", ".DB_Users::$TABLE_NAME."";
		$sql = "update $sql_tables set $sql_set where $sql_where";
		//print "sql is $sql\n";


		$query = $this->db->query($sql);
		$number_affected = $this->db->affected_rows();
		//print "number affected is $number_affected\n";

		if ($number_affected == 4) {
			//print "is success\n";
			// we should be good here
			$obj_result->is_success = true;
		} else {
			// query above all or nothing if not updated then it means 
			// error
			$obj_result->is_success = false;
		}
		return $obj_result;
	}

	// confirm mr
	function is_confirmed($id) {
                $this->load->database();
		$data = $this->get($id);
		if ($data['status_id'] == self::$COMPLETED_STATUS_ID) {
			return true;
		} else {
			return false;
		}
		return NULL;
	}
	

	// we may need check to see if confirm time inside or outside expired time
	function confirm_request($id) {
		$obj_result = new stdclass();
		$obj_result->is_success = false;
		$obj_result->is_unable_to_find_mr_data = false;

		// reason why could not update mr race condition handle this later
		$obj_result->is_mr_expired = false;

                $this->load->database();

		$db_ghr_model = new DB_GHRequests();
		$db_phr_model = new DB_PHRequests();

		
		$mr_data = $this->get($id);
		if (!is_array($mr_data) || count($mr_data) == 0) {
			$obj_result->is_unable_to_find_mr_data = true;
			return $obj_result;
		}

		$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();


		$ph_id = $mr_data['phrequest_id'];
		$gh_id = $mr_data['ghrequest_id'];
		$amount = $mr_data['amount'];

		$gh_data = $db_ghr_model->get($gh_id);
		if (!is_array($gh_data) || count($gh_data) == 0) {
			$obj_result->is_unable_to_find_gh_data = true;
			return $obj_result;
		}

		$ph_data = $db_phr_model->get($ph_id);
		if (!is_array($ph_data) || count($ph_data) == 0) {
			$obj_result->is_unable_to_find_ph_data = true;
			return $obj_result;
		}

		$sql_set_more = "";
		$sql_where_more = "";

		$sql_set = "";

		// check if we are at 20%
		// if we are we need to set available for ph 
		// we are ok here since below will not get updated twice so this wont update twice
		// lets say we have 200 dollars worth
		// .20 is 40 we match 20 each
		// we have another 20 i think were good here but
		// lets say were at .80% will it do another .80% ?
		// lets say we make available 160
		//
			// need to add additional
			//$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_available = ".DB_PHRequests::$TABLE_NAME.".amount - ".$this->db->escape($amount).", ";
			// lets say we have 300 and doig 20 20 20 for first 20%
			// 1st try is 0 + 40 - 20 = 20
			// 2nd is 20 + 20 - 20 = 20
			// 3rd is 20 + 0 - 20 = 0

			// when doing .80 we have 60 completed lets split into 60 40 40
			// 1st is 60 + 100 - 60 = 100
			// 2nd is 40 + 40 - 40 = 40
			// 3rd try is 0 + 40 - 40 = 0
			// uncofirmed + amont_available - current amount == 0 && amount_matched_completed + amount < ph_data['amount'] && PHRequests_amount_available == 0 that means we've completed our 20% set amount_available = 80%
			
		
		$amount_matched_completed = $ph_data['amount_matched_completed'];

			
		// do 80 %
		$sql_where_more .= " AND ( (".DB_PHRequests::$TABLE_NAME.".amount_unconfirmed + ".DB_PHRequests::$TABLE_NAME.".amount_available - ".$this->db->escape($amount).") = 0";
		$sql_where_more .= " AND  ".DB_PHRequests::$TABLE_NAME.".amount_matched_completed + ".$this->db->escape($amount)." < ".DB_PHRequests::$TABLE_NAME.".amount";
		$sql_where_more .= " AND  ".DB_PHRequests::$TABLE_NAME.".amount_available = 0)";
	
		// can do this
		//$sql_set_more .= "".DB_PHRequests::$TABLE_NAME.".amount_available = ".DB_PHRequests::$TABLE_NAME.".amount - $amount_matched_completed + ".$this->db->escape($amount)."), ";
		// or this PH must be in corrrect multiples divisable by 80 percent
		//$sql_set_more .= "".DB_PHRequests::$TABLE_NAME.".amount_available = ".DB_PHRequests::$TABLE_NAME.".amount*.80, ";
		// for 80 %
		// 80 % amount

		$new_amount = 0;
		if ($ph_data['amount'] >= 100) {
			$new_amount = round($ph_data['amount'] * .80, -1);
		}
		$sql_set_more .= "".DB_PHRequests::$TABLE_NAME.".amount_available = ".$this->db->escape($new_amount).", ";
		$sql_set_more = rtrim($sql_set_more,", ");


		// update gh request
		$sql_set .= "".DB_GHRequests::$TABLE_NAME.".amount_matched_completed = ".DB_GHRequests::$TABLE_NAME.".amount_matched_completed + ".$this->db->escape($amount).", ";
		$sql_set .= "".DB_GHRequests::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time).", ";

		// updated ph request
		$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_matched_completed = ".DB_PHRequests::$TABLE_NAME.".amount_matched_completed + ".$this->db->escape($amount).", ";
		// reduce amount unconfirmed
		$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_unconfirmed = ".DB_PHRequests::$TABLE_NAME.".amount_unconfirmed - ".$this->db->escape($amount).", ";
		$sql_set .= "".DB_PHRequests::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time).", ";

		// update matched_requests
		$sql_set .= "".self::$TABLE_NAME.".status_id = ".self::$COMPLETED_STATUS_ID.", ";
		$sql_set .= "".self::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time).", ";
		$sql_set .= "".self::$TABLE_NAME.".completed_gm_date_time = ".$this->db->escape($now_gm_date_time).", ";
		$sql_set .= "".self::$TABLE_NAME.".is_confirmed = ".$this->db->escape("Y").", ";


		// have to remove uncofirmed ph move to column 3 confirmed ph
		$sql_set .= "".DB_Users::$TABLE_NAME.".unconfirmed_ph = ".DB_Users::$TABLE_NAME.".unconfirmed_ph - ".$this->db->escape($amount).", ";
		$sql_set .= "".DB_Users::$TABLE_NAME.".confirmed_ph = ".DB_Users::$TABLE_NAME.".confirmed_ph + ".$this->db->escape($amount).", ";
		$sql_set .= "".DB_Users::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time).", ";
		$expire_gm_date_time = Library_DB_Util::time_to_gm_db_time($db_user_model->db, strtotime("+25 days"));
		$sql_set .= "".DB_Users::$TABLE_NAME.".expiration_before_new_ph_gm_date_time = ".$this->db->escape($expire_gm_date_time).", ";
		$sql_set .= "".DB_Users::$TABLE_NAME.".last_ph_request_gm_date_time = ".$this->db->escape($now_gm_date_time).", ";

		$sql_set = rtrim($sql_set,", ");

	
		// do where
		$sql_where = "".DB_PHRequests::$TABLE_NAME.".id = $ph_id";
		$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".id = $gh_id";
		$sql_where .= " and ".self::$TABLE_NAME.".id = $id";

		$sql_where .= " and ".self::$TABLE_NAME.".status_id = ".self::$ACTIVE_STATUS_ID."";

		// must have an image receipt to confirm
		$sql_where .= " and ".self::$TABLE_NAME.".is_have_image_receipt = 'Y'";

		$sql_where .= " and ".self::$TABLE_NAME.".phrequest_user_id = ".DB_Users::$TABLE_NAME.".id";

		// make sure gh amounts are still valid
		// should do the below but need to calculate gh_total_amount

		

		// make sure PH amounts are still valid


		$sql_tables = "".self::$TABLE_NAME.", ".DB_GHRequests::$TABLE_NAME.", ".DB_PHRequests::$TABLE_NAME.", ".DB_Users::$TABLE_NAME."";
		$sql = "update $sql_tables set $sql_set, $sql_set_more where $sql_where$sql_where_more";
		$query = $this->db->query($sql);
		$number_affected = $this->db->affected_rows();
		//print "1st sql is $sql and number affected is $number_affected<br>\n";
		//exit(0);

		// do reqular query
		if ($number_affected == 0) {
			$sql = "update $sql_tables set $sql_set where $sql_where";
			//print "sql is $sql\n";
			$query = $this->db->query($sql);
			$number_affected = $this->db->affected_rows();
			//print "2nd sql is $sql and number affected is $number_affected<br>\n";
		} else {
		}
		//print "number affected is $number_affected\n";


		$db_phr_model->update_if_request_complete($ph_id);
		$db_ghr_model->update_if_request_complete($gh_id);
		// should clean up gh request and ph request if match_completed == amount
		if ($number_affected == 4) {

			// update if request complete good to do this now
			$db_phr_model->update_if_request_complete($ph_id);
			$db_ghr_model->update_if_request_complete($gh_id);
			//print "is success\n";
			// we should be good here
			$obj_result->is_success = true;
		} else {
			// query above all or nothing if not updated then it means 
			// error
			$obj_result->is_success = false;
		}
		//print_r($obj_result);
		return $obj_result;
	}
}
?>
