<?php 
/******************************************
Command processor to execute every request
post JSON
*******************************************/
$url   	= (!empty($_SERVER['HTTPS'])) ? 
		  "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : 
		  "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
$url   	= $_SERVER['REQUEST_URI'];
$my_url = explode('wp-content' , $url); 
$path   = $_SERVER['DOCUMENT_ROOT']."/".$my_url[0];

require_once($path . "wp-load.php");
require_once($path . "wp-includes/pluggable.php");
require_once($path . "wp-includes/query.php");

require_once("ip-range-checker.php");
require_once("wp-broadbean-content-template.php");

class wp_broadbean_command_processor {
	/******************************************
	 Keys
	*******************************************/
	private $API_OFF 			= "API_OFF";
	private $IP_FAIL 			= "IP_FAIL";
	private $USER_MISMATCH 		= "USER_MISMATCH";
	private $USER_PASSWORD 		= "USER_PASSWORD";
	private $IDENTIFIER_FAIL 	= "IDENTIFIER_FAIL";
	private $INVALID_CMD 		= "INVALID_CMD";
	private $ERROR_ADD 			= "ERROR_ADD";
	private $ERROR_UPDATE 		= "ERROR_UPDATE";
	private $ERROR_DELETE 		= "ERROR_DELETE";
	private $SUCCESS 			= "SUCCESS";

	/******************************************
	 Key with its associative message
	*******************************************/
	private $_responses = array(
		"API_OFF" 			=> "API is turned off",
		"IP_FAIL" 			=> "IP Address miss match",
		"USER_MISMATCH" 	=> "The user being sent does not match the user selected under options.",
		"USER_PASSWORD" 	=> "User password being sent is incorrect",
		"IDENTIFIER_FAIL" 	=> "A job already exists with the identifier ( %s )",
		"INVALID_CMD" 		=> "Invalid command. ( %s )",
		"ERROR_ADD" 		=> "Error adding job, %s",
		"ERROR_UPDATE" 		=> "Error updating job, %s",
		"ERROR_DELETE" 		=> "Error deleting job, %s",
		"SUCCESS" 			=> "Success"
	);

	private $ip_request = "";
	private $content_generator = null;
	private $person = null;
	
	function __construct($ip_request) {
		$this->ip_request = $ip_request;
	}

	
	/******************************************
	 Save data posted from outside, 
	 this method includes sanitation checking 
	*******************************************/
	public function save($data) {
		$input = (object)$data;

		$isApiAvailable = $this->isApiAvailable();		
		if(!$isApiAvailable) {
			return $this->errorResponse($input, $this->API_OFF, "");
		}

		$isIPAddressMeetRange = $this->isIPAddressMeetRange();
		if(!$isIPAddressMeetRange) {
			return $this->errorResponse($input, $this->IP_FAIL, "");
		}

		$hasValidUserName = $this->hasValidUserName($input);
		if(!$hasValidUserName) {
			return $this->errorResponse($input, $this->USER_MISMATCH, "");
		}

		$hasValidPassword = $this->hasValidPassword($input);
		if(!$hasValidPassword) {
			return $this->errorResponse($input, $this->USER_PASSWORD, "");
		}

		$hasValidCommand = $this->hasValidCommand($input);
		if(!$hasValidCommand) {
			return $this->errorResponse($input, $this->INVALID_CMD, "");
		}

		$hasValidIdentifier = $this->hasValidIdentifier($input);
		if(!$hasValidIdentifier) {
			return $this->errorResponse($input, $this->IDENTIFIER_FAIL, "");
		}

		return $this->processCommand($input);
	}


	/******************************************
	 check if current api setting is available
	*******************************************/
	private function isApiAvailable() {
		return get_option("wp_broadbean_ipavailibility") == 1;
	}
	
	/******************************************
	 check current ip range setting
	*******************************************/
	private function isIPAddressMeetRange() {
		if(get_option("wp_broadbean_iprange") == "") {
			return true;
		} else {
			return ip_in_range($this->ip_request, get_option("wp_broadbean_iprange"));
		}
	}

	/******************************************
	 check current user name setting and 
	 compared it with the user name being posted
	*******************************************/
	private function hasValidUserName($input) {
		return strtolower($input->user) == strtolower(get_option("wp_broadbean_users"));
	}

	/******************************************
	 validate username and password
	*******************************************/
	private function hasValidPassword($input) {
		$this->person = get_user_by('login', $input->user);

		if ($this->person && wp_check_password($input->key, $this->person->data->user_pass, $this->person->ID)) {
			return true;
		} else {
			return false;
		}
	}

	/******************************************
	 is input contain add command
	*******************************************/
	private function isAddCommand($input) {
		return strtolower($input->command) == "add";
	}

	/******************************************
	 is input contain update command
	*******************************************/
	private function isUpdateCommand($input) {
		return strtolower($input->command) == "update";
	}

	/******************************************
	 is input contain delete command
	*******************************************/
	private function isDeleteCommand($input) {
		return strtolower($input->command) == "delete";
	}


	/******************************************
	 check command value, valid commands are ADD, UPDATE, DELETE
	*******************************************/
	private function hasValidCommand($input) {
		return $this->isAddCommand($input) || $this->isUpdateCommand($input) || $this->isDeleteCommand($input);
	}


	/******************************************
	 check identifier based on command
	*******************************************/
	private function hasValidIdentifier($input) {
		$post = $this->getPostByJobId($input);	

		if($this->isAddCommand($input)) {
			if($post) {
				return false;
			} else {
				return true;
			}
		} else if($this->isUpdateCommand($input) || $this->isDeleteCommand($input)) {
			if($post) {
				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	private function processCommand($input) {
		$result = null;

		if($this->isAddCommand($input)) {
			$result = $this->saveCommand($input, $this->person);

		} else if($this->isUpdateCommand($input)) {
			$result = $this->updateCommand($input, $this->person);

		} else if($this->isDeleteCommand($input)) {
			$result = $this->deleteCommand($input);
		}

		return $result;
	}

	private function setupContentGenerator($input) {
		$this->content_generator = new wp_broadbean_content_template($input);
	}


	/******************************************
	 Create a post
	*******************************************/
	private function saveCommand($input, $person) {
		$this->setupContentGenerator($input);

		$job = $input->job[0];
		$jobId = $job["id"];

		$info = (object)$job["info"][0];
		$title = $info->title;
		$slug = str_replace(" ", "-", $info->title);

		$tags = $info->tags;

		$summaries = (object)$job["summary"];
		$summariesText = "<ul>";
		foreach ($summaries as $key => $value) {
			$summariesText .= "<li>". $value["point"] . "</li>";
		}
        $summariesText .= "</ul>";
		
		$post = array(
			  'comment_status' => 'closed', 															// 'closed' means no comments.
			  'post_author'    => $person->ID, 															//The user ID number of the author.
			  'post_category'  => array(get_option("wp_broadbean_categories")), 						//post_category no longer exists, try wp_set_post_terms() for setting a post's categories
			  'post_content'   => $this->content_generator->generateContents(), 						//The full text of the post.
			  'post_date'      => date("Y-m-d H:i:s"), 													//The time post was made.
			  'post_date_gmt'  => gmdate("Y-m-d H:i:s"), 												//The time post was made, in GMT.
			  'post_name'      => $slug, 																//The name (slug) for your post
			  'post_status'    => 'publish', 															//Set the status of the new post.
			  'post_title'     => $title, 																//The title of your post.
			  'post_type'      => 'post', 																//You may want to insert a regular post, page, link, a menu item or some custom post type
			  //'menu_order'     => [ <order> ], 														//If new post is a page, it sets the order in which it should appear in the tabs.
			  'ping_status'    => 'closed', 															// 'closed' means pingbacks or trackbacks turned off
			  //'pinged'         => [ ? ], 																//?
			  'post_excerpt'   => $summariesText, 														//For all your post excerpt needs. post summary
			  //'post_parent'    => [ <post ID> ], 														//Sets the parent of the new post.
			  //'post_password'  => [ ? ], 																//password for post?
			  'tags_input'     => $tags, 																//For tags.
			  //'to_ping'        => [ ? ], 																//?
			  //'tax_input'      => [ array( 'taxonomy_name' => array( 'term', 'term2', 'term3' ) ) ] 	// support for custom taxonomies. 
			);

        $result = wp_insert_post($post);

		if(is_wp_error($result)) {
			return $this->errorResponse($input, $this->ERROR_ADD, $result->get_error_message());
		}

		add_post_meta($result, 'job_id', $jobId);

		return $this->successResponse($input);
	}


	/******************************************
	 Update a post based on job.id and create response
	*******************************************/
	private function updateCommand($input, $person) {
		$this->setupContentGenerator($input);

		$job = $input->job[0];
		$jobId = $job["id"];

		$info = (object)$job["info"][0];
		$title = $info->title;
		$slug = str_replace(" ", "-", $info->title);

		$tags = $info->tags;

		$summaries = (object)$job["summary"];
		$summariesText = '';
		foreach ($summaries as $key => $value) {
			$summariesText .= $value["point"] . " ";
		}
		
		$existingPost = $this->getPostByJobId($input);

		$post = array(
			  'ID'				=> $existingPost->ID, 													//Are you updating an existing post?
			  'comment_status'	=> 'closed', 															// 'closed' means no comments.
			  'post_author'		=> $person->ID, 														//The user ID number of the author.
			  'post_category'	=> array(get_option("wp_broadbean_categories")),						//post_category no longer exists, try wp_set_post_terms() for setting a post's categories
			  'post_content'	=> $this->content_generator->generateContents(), 						//The full text of the post.
			  'post_name'		=> $slug, 																//The name (slug) for your post
			  'post_status'		=> 'publish', 															//Set the status of the new post.
			  'post_title'		=> $title, 																//The title of your post.
			  'post_type'		=> 'post',																//You may want to insert a regular post, page, link, a menu item or some custom post type
			  //'menu_order'     => [ <order> ], 														//If new post is a page, it sets the order in which it should appear in the tabs.
			  'ping_status'    => 'closed', 															// 'closed' means pingbacks or trackbacks turned off
			  //'pinged'         => [ ? ], 																//?
			  //'post_date'      => [ date("Y-m-d H:i:s") ], 											//The time post was made.
			  //'post_date_gmt'  => [ gmdate("Y-m-d H:i:s") ], 											//The time post was made, in GMT.
			  'post_excerpt'   => $summariesText, 														//For all your post excerpt needs. post summary
			  //'post_parent'    => [ <post ID> ], 														//Sets the parent of the new post.
			  //'post_password'  => [ ? ], 																//password for post?
			  'tags_input'     => $tags, 																//For tags.
			  //'to_ping'        => [ ? ], 																//?
			  //'tax_input'      => [ array( 'taxonomy_name' => array( 'term', 'term2', 'term3' ) ) ] 	// support for custom taxonomies. 
			);

        $result = wp_update_post($post);

		if(is_wp_error($result)) {
			return $this->errorResponse($input, $this->ERROR_UPDATE, $result->get_error_message());
		}

		return $this->successResponse($input);
	}


	/******************************************
	 Delete a post and create response
	*******************************************/
	private function deleteCommand($input) {
		$post = $this->getPostByJobId($input);

		if($post == null) {
			return $this->errorResponse($input, $this->ERROR_DELETE, "Cannot find post");
		}

		$result = wp_delete_post($post->ID, true);
		
		if(is_wp_error($result)) {
			return $this->errorResponse($input, $this->ERROR_DELETE, $result->get_error_message());
		}

		return $this->successResponse($input);
	}

	
	/******************************************
	 Get wordpress post based on job.id posted
	*******************************************/
	private function getPostByJobId($input) {
		$postId = -1;

		$query =  new WP_Query(array(
								'meta_value' 	=> $input->job[0]["id"], 
								'meta_key' 		=> 'job_id', 
								'post_type' 	=> 'post', 
								'post_status' 	=> 'publish'));
		
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$result = $query->the_post();
				$postId = $query->post->ID;
				break;
			}
		} 

		if($postId > 0) {
			return wp_get_single_post($postId);
		}

		return null;
	}


	/******************************************
	 create a success response
	*******************************************/
	private function successResponse($input) {
		$result = new stdClass;
		$result->success = true;
		$result->id = $input->job[0]["id"];
		$result->msgid = $input->msgid;
		$result->url = $input->job[0]["apply"];
		$result->msg = "";

		return $result;
	}

	
	/******************************************
	 create an error response
	*******************************************/
	private function errorResponse($input, $key, $description) {
		$result = new stdClass;
		$result->success = false;
		$result->id = $input->job[0]["id"];
		$result->msgid = $input->msgid;
		$result->url = "";
		$result->msg = $this->formatErrorResponse($input, $key, $description);

		return $result;
	}

	private function formatErrorResponse($input, $key, $description) {
		$errorFormat = $this->_responses["$key"];
		$result = "";

		if(strtolower($key) === strtolower($this->IDENTIFIER_FAIL)) {
			$result = sprintf($errorFormat  ,  $input->job[0]["id"]);
		} else if(strtolower($key) === strtolower($this->INVALID_CMD)) {
			$result = sprintf($errorFormat ,  $input->command);
		} else if(strtolower($key) === strtolower($this->ERROR_ADD) || 
			strtolower($key) === strtolower($this->ERROR_UPDATE) || 
			strtolower($key) === strtolower($this->ERROR_DELETE)) {
			$result = sprintf($errorFormat , $description);
		} else {
			$result = $errorFormat;
		}

		return $result;
	}
}
?>