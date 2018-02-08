<?php

	/** 
	 * @author Arun
	 *
	 *
	 *
	 */


	class AP_Tracker {
		
		protected $strSessionDataArray = null;
		
		
		public function __construct() {
			$this->strSessionDataArray = $this->SetCurrentUser();
			$this->StoreSessionData($this->strSessionDataArray);
		}
		
		
		public static function SetCurrentUser() {
			session_start();
			$objCurrentUser = null;
			if (!array_key_exists('AP_Tracker', $_SESSION)) {
				
				// Initialize a new user

				$_SESSION['AP_Tracker'] = array();
				
				$_SESSION['AP_Tracker']['SessionID'] = session_id();
				$_SESSION['AP_Tracker']['original_referrer'] = $_SERVER['HTTP_REFERER'];
				$_SESSION['AP_Tracker']['EnteredDate'] = AP_DateTime::Now()->ToString(AP_DateTime::FormatIso);
				
				
				//AP_Application::$OriginalReferrer = $_SESSION['AP_Tracker']['original_referrer'];
				
				$objCurrentUser = new stdClass;
				$objCurrentUser->sessionID = session_id();
				$objCurrentUser->OriginalReferrer = $_SERVER['HTTP_REFERER'];
				$objCurrentUser->EnteredDate = AP_DateTime::Now()->ToString(AP_DateTime::FormatIso);
				$objCurrentUser->IPAdress = $_SERVER['REMOTE_ADDR'];
			}
			else {
				$objCurrentUser->sessionID = $_SESSION['AP_Tracker']['SessionID'];
				$objCurrentUser->OriginalReferrer = $_SESSION['AP_Tracker']['original_referrer'];
				$objCurrentUser->EnteredDate = $_SESSION['AP_Tracker']['EnteredDate'];
				$objCurrentUser->EnteredDate = $_SESSION['AP_Tracker']['EnteredDate'];
			}
			

			$objCurrentUser->UserAgent = $_SERVER['HTTP_USER_AGENT'];
			$objCurrentUser->LastReferrer = $_SERVER['HTTP_REFERER'];
			$objCurrentUser->CurrentPage = $_SERVER['REQUEST_URI'];
			return $objCurrentUser;
		}
		
		
		/**
		 * Takes the session and server data stored in @strSessionDataArray and pushes it to a custom WP tracking table
		 * Is called recursively from SessionGetData()
		 */
		
		protected function StoreSessionData($objCurrentUser){
			global $wpdb;
			$intUser_id = get_current_user_id();
			$strTable_name = $wpdb->prefix . 'ap_tracker';
			$strQuery = "
					INSERT INTO $strTable_name 
					(
					user_id,
					session_id,
					ip_address,
					user_agent,
					page_url,
					original_referrer,
					last_referrer,
					entered_date
					) 
 					VALUES 
					(
					'$intUser_id',
					'$this->SessionID',
					'$this->IPAddress',
					'$this->UserAgent',
					'$this->CurrentPage',
					'$this->OriginalReferrer',
					'$this->LastReferrer',
					'$this->EnteredDate'
					)
					ON DUPLICATE KEY UPDATE 
   					user_id = '$intUser_id', 
					ip_address = '$this->IPAddress',
					user_agent = '$this->UserAgent',
					page_url = '$this->CurrentPage', 
					original_referrer = '$this->OriginalReferrer',
					last_referrer = '$this->LastReferrer',
					entered_date = '$this->EnteredDate';
			";
			
			$wpdb->query($strQuery);
		
		
		}
		
		
		/////////////////////////
		// Public Properties: GET
		/////////////////////////
		public function __get($strName) {
			switch ($strName) {
				case 'SessionID':
					return $this->strSessionDataArray->sessionID;
				case 'IPAddress':
					return $this->strSessionDataArray->IPAdress;
				case 'UserAgent':
					return $this->strSessionDataArray->UserAgent;
				case 'OriginalReferrer':
					return $this->strSessionDataArray->OriginalReferrer;
				case 'LastReferrer':
					return $this->strSessionDataArray->LastReferrer;
				case 'CurrentPage':
					return $this->strSessionDataArray->CurrentPage;
				case 'EnteredDate':
					return $this->strSessionDataArray->EnteredDate;
			}
		}
		
		/////////////////////////
		// Public Properties: SET
		/////////////////////////
		public function __set($strName, $mixValue) {
			switch ($strName) {
				case 'SessionID':
					return ($this->strSessionDataArray->sessionID = $strValue);
				case 'IPAddress':
					return ($this->strSessionDataArray->IPAdress = $strValue);
				case 'UserAgent':
					return ($this->strSessionDataArray->UserAgent = $strValue);
				case 'OriginalReferrer':
					return ($this->strSessionDataArray->OriginalReferrer = $strValue);
				case 'LastReferrer':
					return ($this->strSessionDataArray->LastReferrer = $strValue);
				case 'CurrentPage':
					return ($this->strSessionDataArray->CurrentPage = $strValue);
				case 'EnteredDate':
					return ($this->strSessionDataArray->EnteredDate = $strValue);
			}
		}
	}