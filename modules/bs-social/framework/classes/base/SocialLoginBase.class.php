<?php
class SocialLoginBase {
	
	protected $user_raw_data;
	protected $name;
	protected $first_name;
	protected $last_name;
	protected $email;
	protected $phone;
	protected $address;
	protected $job_title;
	protected $company;
	protected $location;
	protected $gender;
	
	/**
	 * Run function is called from the constructor
	 */
	public function Run() {
		$this->Init();
		$this->Authorize();
		$this->SessionHandler();
	}
	
	protected function Init() {}
	protected function Authorize() {}
	protected function SessionHandler() {}
	public function IsUserLoggedIn() {return false;}
	public function Logout() {}
	public function PrintScripts() {}
	
	///////////////////////////////
	// DISPLAY FUNCTIONS
	///////////////////////////////
	public function Render($html = null) {
		$this->PrintScripts();
		$this->RenderLogin($html);
	}
	public function RenderLogin($html) {}
	public function RenderLogout() {}
	
	///////////////////////////////
	// GET FUNCTIONS
	///////////////////////////////
	public function GetEmail(){
		return $this->email;
	}
	
	public function GetFirstName(){
		return $this->first_name;
	}
	
	public function GetLastName(){
		return $this->last_name;
	}
	
	public function GetName(){
		return $this->name;
	}
	
	public function GetLocation(){
		return $this->location;
	}
	
	public function GetGender(){
		return $this->gender;
	}
}