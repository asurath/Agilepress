<?php

class AP_AdminPageBase extends AP_Base {
	
	protected $id = 'default-page';
	// TODO error codes
	protected $error_msgs = array();
	protected $error_codes = array();
	protected $success_msgs = array();
	protected $success_codes = array();
	protected $tpl;
	protected $UseSidebar = false; // on/off whether to call $this->sidebar() method
	
	public function __construct(){
		return true;
	}
	
	// page process
	public static function Init(){}
	protected function CheckAJAX(){
		if ($this->IsAJAX()){
			$this->DoAJAX();
			exit();
		}
	}
	protected function IsAJAX(){
		return $_POST['action'] == 'ajax';
	}
	
	protected function DoAJAX(){}
	protected function Prerender(){}

	protected function ContentStart(){ ?>
		<div id="main">
	<?php }
	protected function Content(){}
	protected function ContentEnd(){ ?>
		</div><!-- #main -->
	<?php }
	protected function Sidebar(){ GetSidebar(); }
	protected function Scripts(){}
	protected function Footer(){ GetFooter(); }
	protected function End(){}
	
	public function Run(){
		$this->CheckAJAX();
		$this->Prerender();
		$this->ContentStart();
		$this->Content();
		$this->ContentEnd();

		if ($this->UseSidebar()){
			$this->Sidebar();
		}

		$this->Scripts();
		$this->End();
	}

	public function UseSidebar($boolean = null){
		if (isset($boolean)){
			$this->UseSidebar = $boolean;
			return $this;
		} else {
			return $this->UseSidebar;
		}
	}

	public function SetTemplate($strfile){
		$this->tpl = $strfile;
	}
	
	protected function AdminError($strValue){
		?>
		<div class="error">
		<p><?= $strValue;?></p>
		</div>
		<?php 
	}
	
	protected function AdminCodeGenError(){
		?>
			<style type="text/css">
				.ap-admin-button { text-decoration:none !important; }
				.ap-admin-button .ap-admin-button-2 { text-decoration:none; display:block; width:150px; height:40px; border-radius:5px; border-style:solid; border-width:1px; border-color: rgb(9, 128, 171); margin-bottom: 20px; background: -moz-linear-gradient(rgb(54,183,231),rgb(7,148,198)); text-align:center; }
				.ap-admin-button .ap-admin-button-2 span {text-decoration:none; position:relative; top:10px; color:white; font-weight:bold;}
			</style>
			<script type="text/javascript">
			function CodeGenOffPage(){
				$.post("",{ "action" : "ajax" , "CodeGen" : true }, function(data){ document.location.reload(); });
			}
			</script>
			<div class="error" style="border-left: 4px solid rgb(241, 146, 7) !important; margin-top:30px;">
			<p style="padding-top:10px;" >You have changes to your AgilePress configuration that have not been codegened</p>
			<br>
			<a class="ap-admin-button" style="" onclick="CodeGenOffPage(); return false;" href = "">
				<span class="ap-admin-button-2">
				<span style=""> Update Code </span>
				</span>
			</a>
			</div>
			<?php 
		}
	
}