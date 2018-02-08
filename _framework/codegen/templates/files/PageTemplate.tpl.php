/**
 *
 *<?= $strPageClassDocBlock ?>
 *
 *
 */

class <?= $strPageClassName ?> extends AP_PageBase {
	
	protected $strID = '<?= $strPageID ?>';
	protected $strBodyContent = '<?= $strPageBodyContentFile ?>';
	protected $boolUseSidebar = false;
	
	/**
	 * Class constructor
	 * 
	 * @since 1.0
	 * @return boolean
	 */
	public function __construct(){
		return true;
	}
	
	/**
	 * Page Init functionality
	 * 
	 * @since 1.0
	 * @return unknown
	 */ 
	public function Init(){}
	
	/**
	 *  Check if current request is an ajax request and diverts code flow depending on result
	 *  
	 *  @since 1.0
	 *  @return unknown 
	 */
	protected function CheckAJAX(){
		if ($this->IsAJAX()){
			$this->DoAJAX();
			exit();
		}
	}
	
	/**
	 * Returns a boolean depending on if current request is an 
	 * 
	 * @since 1.0
	 * @return boolean $boolIsAjax
	 */ 
	protected function IsAJAX(){
		return ($_POST['action'] == 'ajax') ? true : false;
	}
	
	/**
	 * Base function definition for a pages AJAX handler
	 * 
	 * @since 1.0
	 * @return unknown
	 */
	protected function DoAJAX(){}
	
	/**
	 * Includes code required before a page renders
	 * 
	 * @since 1.0
	 * @return unknown
	 */
	protected function Prerender(){}

	/**
	 * Start of the content
	 * 
	 * @since 1.0
	 * @return string $strContentStart
	 */
	protected function ContentStart(){ 
		GetMarkupFrom('HeaderMain.mrkp.php');
	}
	
	/**
	 * Content
	 *
	 * @since 1.0
	 * @return string $strContent
	 */
	protected function Content(){
		GetMarkupFrom('ContentMain.mrkp.php');
	}
	
	/**
	 * End of the content
	 *
	 * @since 1.0
	 * @return string $strContentEnd
	 */
	protected function ContentEnd(){
		GetMarkupFrom('FooterMain.mrkp.php');
	}
	
	/**
	 * Page sidebar functionality call
	 *
	 * @since 1.0
	 * @return string $strSidebar
	 */
	protected function Sidebar(){ GetSidebar(); }
	
	/**
	 * Page scripts call
	 *
	 * @since 1.0
	 * @return string $strScripts
	 */
	protected function Scripts(){}
	
	/**
	 * Page footer functionality call
	 *
	 * @since 1.0
	 * @return string $strFooter
	 */
	protected function Footer(){ GetFooter(); }
	
	/**
	 * Page pre-end functionality
	 * 
	 * @since 1.0
	 * @return unknown
	 */
	protected function End(){}
	
	
	/**
	 * Function wrapping for page method method call order
	 * 
	 * @since 1.0
	 * @return void
	 */
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

	/**
	 * function for setting the boolean value that determines if the sidebar page method will be called
	 * 
	 * @param string $boolean
	 * @return boolean
	 */
	public function UseSidebar($boolean = null){
		if (isset($boolean)){
			$this->boolUseSidebar = $boolean;
			return $this;
		} else {
			return $this->boolUseSidebar;
		}
	}
	
	/**
	 * function for setting the body content file associated with this class
	 *
	 * @param string $boolean
	 * @return void
	 */
	public function SetBodyContent($strfile){
		$this->strBodyContent = $strfile;
	}

	protected function OutputBodyContent(){
		ob_start();
		$strFile = include( APEXT_PAGES_PATH .  $this->strBodyContent . ".php");
		$strContents = ob_get_contents();
		ob_end_clean();
		echo $strContents;
	}

}