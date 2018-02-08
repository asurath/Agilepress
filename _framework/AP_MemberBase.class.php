<?php
	class AP_MemberBase {
		protected $session_id;
		protected $ip_address;
		protected $user_agent;
		protected $original_referrer;
		protected $last_referrer;
		protected $current_page;
		protected $entered_date;
		
		public function __construct() {
			
		}
		
		public static function GetCurrentUser() {
			$AP_Current_User = null;
			if (!array_key_exists('AP_User', $_SESSION)) {
				$AP_Current_User = AP_MemberBase::SetCurrentUser();
			}
			else {
				$AP_Current_User = unserialize($_SESSION['AP_User']);
			}
			
			return $AP_Current_User;
		}
		
		public static function SetCurrentUser() {
			
			if (session_id() == "")
				session_start();
			
			$AP_Current_User = null;
			
			if (!array_key_exists('AP_User', $_SESSION)) {
				
				// Initialize a new user
				$_SESSION['AP_User'] = array();
				$AP_Current_User = new AP_MemberBase();
				$AP_Current_User->OriginalReferrer = $_SERVER['HTTP_REFERER'];
				$AP_Current_User->EnteredDate = AP_DateTime::Now()->ToString(AP_DateTime::FormatIso);
			}
			else {
				$AP_Current_User = unserialize($_SESSION['AP_User']);
			}
			
			$AP_Current_User->SessionID = session_id();
			$AP_Current_User->IPAddress = $_SERVER['REMOTE_ADDR'];
			$AP_Current_User->UserAgent = $_SERVER['HTTP_USER_AGENT'];
			$AP_Current_User->LastReferrer = $_SERVER['HTTP_REFERER'];
			$AP_Current_User->CurrentPage = $_SERVER['REQUEST_URI'];
			
			$_SESSION['AP_User'] = serialize($AP_Current_User);
			
			return $AP_Current_User;
		}
		
		/////////////////////////
		// Public Properties: GET
		/////////////////////////
		public function __get($name) {
			switch ($name) {
				case 'SessionID':
					return $this->session_id;
				case 'IPAddress':
					return $this->ip_address;
				case 'UserAgent':
					return $this->user_agent;
				case 'OriginalReferrer':
					return $this->original_referrer;
				case 'LastReferrer':
					return $this->last_referrer;
				case 'CurrentPage':
					return $this->current_page;
				case 'EnteredDate':
					return $this->entered_date;
			}
		}
		
		/////////////////////////
		// Public Properties: SET
		/////////////////////////
		public function __set($name, $value) {
			switch ($name) {
				case 'SessionID':
					return ($this->session_id = $value);
				case 'IPAddress':
					return ($this->ip_address = $value);
				case 'UserAgent':
					return ($this->user_agent = $value);
				case 'OriginalReferrer':
					return ($this->original_referrer = $value);
				case 'LastReferrer':
					return ($this->last_referrer = $value);
				case 'CurrentPage':
					return ($this->current_page = $value);
				case 'EnteredDate':
					return ($this->entered_date = $value);
			}
		}
	}