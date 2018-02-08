<?php

class AP_PageBase extends AP_Base {
	protected $intID = 'default-page';
	// TODO error codes
	protected $strErrorMessageArray = array();
	protected $intErrorCodeArray = array();
	protected $strSuccessMessageArray = array();
	protected $intSuccessCodeArray = array();
	protected $strTemplate;
	protected $boolUseSidebar = true; // on/off whether to call $this->sidebar() method
	
	public function __construct($intID = null){
		if ($intID)
			$this->intID = $intID;
	}
	
	// page process
	protected function Init(){}
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
	
	protected function Header(){
		get_header();
	}
	protected function ContentStart(){ ?>
		<div id="main">
	<?php }
	protected function Content(){
		if (have_posts()) :  while (have_posts()) : the_post(); ?>
			<div class="post">
				<h1 id="archive-title" class="page-title"><?php the_title(); ?></h1>	
				<?php the_content(); ?>
			</div><!-- .post -->
		<?php endwhile; endif;
	}
	protected function ContentEnd(){ ?>
		</div><!-- #main -->
	<?php }
	protected function Sidebar(){ get_sidebar(); }
	protected function Scripts(){}
	protected function Footer(){ get_footer(); }
	protected function End(){}
	
	public function run(){
		$this->init();
		$this->CheckAJAX();
		$this->Prerender();
		$this->Header();
		$this->ContentStart();
		$this->Content();
		$this->ContentEnd();

		if ($this->UseSidebar()){
			$this->Sidebar();
		}

		$this->Scripts();
		$this->Footer();
		$this->End();
	}

	public function UseSidebar($boolean = null){
		if (isset($boolean)){
			$this->boolUseSidebar = $boolean;
			return $this;
		} else {
			return $this->boolUseSidebar;
		}
	}

	public function SetTemplate($file){
		$this->strTemplate = $file;
	}
	/**
	 * Displays messages
	 */
	protected function DisplayMessages(){
		// TODO error code flags
		// This functionality, duplicated in the BS_Form class, and here, could be a part of the BS_Base class,
		// or could be put in a very light intermediate class:  BS_Base->BS_Messages->BS_Form and BS_Base->BS_Messages->BS_Page
		foreach ($this->intErrorCodeArray as $error_code => $error_code_msg){
			if($_GET[$error_code])
				$this->strErrorMessageArray[] = $error_code_msg;
		}
		foreach ($this->intSuccessCodeArray as $success_code => $success_code_msg){
			if($_GET[$success_code])
				$this->strSuccessMessageArray[] = $success_code_msg;
		}
		foreach ($this->strErrorMessageArray as $error_msg){ ?>
			<div class="bs-error"><?=$error_msg?></div>
		<?php }
		
		foreach ($this->strSuccessMessageArray as $success_msg){ ?>
			<div class="bs-confirmation bs-page-confirmation"><?=$success_msg?></div>
		<?php }
	}
	
	public static function fix_title($title, $sep, $seplocation){
		// The <title> gets messed up when using custom pagination
		// Used in:  BS_CommunityPage and BS_AccountQandAPage
		return "Find the Best Business Software: Reviews & Product Directory";
	}

	public function BackToTop(){

	}

	public static function GetMarkupFrom($strFileName, $boolCompletePath = false){

		if($boolCompletePath)
			$strFilePath = $strFileName;
		else
			$strFilePath =  APEXT_CONTENT_PATH . "/" . $strFileName;

		ob_start();
		$strFile = include($strFilePath);
		$strContents = ob_get_contents();
		ob_end_clean();

		echo $strContents;
		return ;
	}
	
	
}