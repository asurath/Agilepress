<?php
	/**
	 * 
	 *  @package Controls
	 *	
	 *	@property integer	$ID
	 *	@property string 	$Name
	 *	@property string	$NameCSSClass
	 *	@property string 	$Value
	 *	@property string 	$CSSClass
	 *	@property array 	$CSSClassArray
	 *	@property string 	$Title
	 *	@property integer 	$TabIndex
	 *	@property string 	$Style
	 *	@property boolean	$IsBlockElement
	 *	@property string	$HtmlBefore
	 *	@property string	$HtmlAfter
	 *	@property string	$WrapperCssClass
	 *	@property string	$WrapControl
	 */
	
	abstract class AP_Control {
		protected $intID;
		protected $strName;
		protected $strNameCSSClass = 'ap-control-name';
		protected $mixValue;
		protected $strCSSClass;
		protected $strCSSClassArray;
		protected $strTitle;
		protected $intTabIndex;
		protected $strStyle;
		protected $boolIsBlockElement = false;
		protected $boolWrapControl = true;
		protected $strWrapperCSSClass = 'ap-control-wrap';
		protected $strHTMLBefore;
		protected $strHTMLAfter;
		
		public function __construct($intID) {
			$this->intID = $intID;
			
			$this->Init();
		}
		
		
		// Static switch method //
		
		public static function GetControl($strControlType, $strControlSlug, $mixValue = null){
			$strControlType = strtolower($strControlType);
			switch ($strControlType) {
				case 'textbox' :
					$objNewControl = new AP_TextBox($strControlSlug);
					break;
				case 'textarea' :
					$objNewControl = new AP_TextBox($strControlSlug);
					$objNewControl->TextMode = AP_TextMode::MULTILINE;
					break;
				case 'password' :
					$objNewControl = new AP_TextBox($strControlSlug);
					$objNewControl->TextMode = AP_TextMode::PASSWORD;
					break;
				case 'listbox' :
					$objNewControl = new AP_ListBox($strControlSlug);
					break;
				case 'checkbox' :
					$objNewControl = new AP_CheckBox($strControlSlug);
					break;
				case 'checkboxlist' :
					$objNewControl = new AP_CheckBoxList($strControlSlug);
					break;
				case 'radio' :
					$objNewControl = new AP_RadioButton($strControlSlug);
					break;
			}	
			$objNewControl->Value = ($mixValue != null) ?  $mixValue : null;
			return $objNewControl;
		}
		
		protected function Init(){}
		abstract function GetControlHtml();
		
		/**
		 * Renders the output of the control;
		 * @return unknown
		 */
		public function Render() {
			$output = $this->GetControlHtml();
			
			return $this->RenderOutput($output);
		}
		
		/**
		 * Renders a control with the name of the object
		 */
		public function RenderWithName() {
			if ($this->boolIsBlockElement) {
				$output = sprintf('<div class="%s">%s</div>%s%s%s',
					$this->strNameCSSClass,
					$this->strName,
					$this->strHTMLBefore,
					$this->GetControlHtml(),
					$this->strHTMLAfter);
			}
			else {
				$output = sprintf('<span class="%s">%s</span><br />%s%s%s',
					$this->strNameCSSClass,
					$this->strName,
					$this->strHTMLBefore,
					$this->GetControlHtml(),
					$this->strHTMLAfter);
			}
			
			return $this->RenderOutput($output);
		}
		
		/**
		 * 
		 * @param unknown $output
		 * @param string $display_output - write something here to show up
		 * @return string
		 */
		protected function RenderOutput($output, $display_output = true) {
			
			if ($this->boolWrapControl) {
				$output = sprintf('<div class="%s">%s</div>',
					$this->strWrapperCSSClass,
					$output);
			}
			if ($display_output)
				echo $output;
			else
				return $output;
		}
		
		/**
		 * Adds CSS class
		 */
		public function AddCssClass($class) {
			if (!in_array($class, $this->strCSSClassArray)) {
				array_push($this->strCSSClassArray, $class);
				$this->strCSSClass = implode(' ', $this->strCSSClassArray);
			}
		}
		
		/**
		 * Removes a CSS class
		 * @param unknown_type $class
		 */
		public function RemoveCssClass($class) {
			
			$key = array_search($class, $this->strCSSClassArray);
			
			if ($key === false)
				return;
			else {
				unset($this->strCSSClassArray[$key]);
				$this->strCSSClassArray = array_values($this->strCSSClassArray);
			}	
		}
		
		/////////////////////////
		// Public Properties: GET
		/////////////////////////
		public function __get($strName) {
			switch ($strName) {
				case "ID": return $this->intID;
				case "Name": return $this->strName;
				case "NameCSSClass": return $this->strNameCSSClass;
				case "Value": return $this->mixValue;
				case "CssClass": return $this->strCSSClass;
				case "Style": return $this->strStyle;
				case "Title": return $this->strTitle;
				case "TabIndex": return $this->intTabIndex;
				case "IsBlockElement": return $this->boolIsBlockElement;
				case "HtmlBefore": return $this->strHTMLBefore;
				case "HtmlAfter": return $this->strHTMLAfter;
				case "WrapperCssClass": return $this->strWrapperCSSClass;
				case "WrapControl": return $this->boolWrapControl;
			}
		}
		
		/////////////////////////
		// Public Properties: SET
		/////////////////////////
		public function __set($strName, $mixValue) {
			switch ($strName) {
				case "ID":
					return ($this->intID = $mixValue);
				case "Name":
					return ($this->strName = $mixValue);
				case "NameCSSClass":
					return ($this->strNameCSSClass = $mixValue);
				case "Value":
					return ($this->mixValue = $mixValue);
				case "CSSClass":
					$this->strCSSClassArray = array();
					$this->AddCssClass($mixValue);
					break;
				case "Style":
					return ($this->strStyle = $mixValue);
				case "Title":
					return ($this->strTitle = $mixValue);
				case "TabIndex":
					if(is_numeric($mixValue))
						return ($this->intTabIndex = $mixValue);
					else
						return false;
				case "IsBlockElement":
					return ($this->boolIsBlockElement = $mixValue);
				case "HtmlBefore":
					return ($this->strHTMLBefore = $mixValue);
				case "HtmlAfter":
					return ($this->strHTMLAfter = $mixValue);
				case "WrapperCssClass":
					return ($this->strWrapperCSSClass = $mixValue);
				case "WrapControl":
					return ($this->boolWrapControl);
			}
		}
	}