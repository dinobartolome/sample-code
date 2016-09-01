<?

class BackEnd_MatchedRequests_View {

	function __construct() { }



	function build_html($data_object = null) {

		$t = new stdclass();

		$template_file = dirname(__FILE__)."/html/template.html";

		//$arr_options = InvoiceGroups::get_arr_values();

		$data_object = new stdclass();
		$data_object->contents_html = Common::build_html($template_file, $t);
		
		$s = BackEnd_Template_View::build_html($data_object);
		return $s;
	}

	function build_matched_request_ph_email_html($data_object = null) {

		$template_file = dirname(__FILE__)."/html/ph_email.html";

		//$arr_options = InvoiceGroups::get_arr_values();
		if (!is_object($data_object)) {
			$data_object = new stdclass();
		}

		$s = Common::build_html($template_file, $data_object);
		
		return $s;
	}

	function build_matched_request_gh_email_html($data_object = null) {

		$template_file = dirname(__FILE__)."/html/gh_email.html";

		//$arr_options = InvoiceGroups::get_arr_values();
		if (!is_object($data_object)) {
			$data_object = new stdclass();
		}

		$s = Common::build_html($template_file, $data_object);
		
		return $s;
	}

	function build_gh_confirm_email_html($data_object = null) {

		$template_file = dirname(__FILE__)."/html/gh_confirm_email.html";

		//$arr_options = InvoiceGroups::get_arr_values();
		if (!is_object($data_object)) {
			$data_object = new stdclass();
		}

		$s = Common::build_html($template_file, $data_object);
		
		return $s;

	}
	function build_image_receipt_upload_email_html($data_object = null) {

		$template_file = dirname(__FILE__)."/html/image_receipt_upload_email.html";

		//$arr_options = InvoiceGroups::get_arr_values();
		if (!is_object($data_object)) {
			$data_object = new stdclass();
		}

		$s = Common::build_html($template_file, $data_object);
		
		return $s;
	}

}
?>
