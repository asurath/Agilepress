<?php
	/**
	 * 
	 *  @package Controls
	 *	
	 *	@property integer	$ID
	 *	@property string 	$Name
	 *	@property string	$NameCssClass
	 *	@property string 	$Value
	 *	@property string 	$CssClass
	 *	@property array 	$CssClassArray
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
		protected $id;
		protected $name;
		protected $name_css_class = 'ap-control-name';
		protected $value;
		protected $css_class;
		protected $css_class_array;
		protected $title;
		protected $tab_index;
		protected $style;
		protected $is_block_element = false;
		protected $wrap_control = true;
		protected $wrapper_css_class = 'ap-control-wrap';
		protected $html_before;
		protected $html_after;
		
		public function __construct($id) {
			$this->id = $id;
			
			$this->Init();
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
			
			if ($this->is_block_element) {
				$output = sprintf('<div class="%s">%s</div>%s%s%s',
					$this->name_css_class,
					$this->name,
					$this->html_before,
					$this->GetControlHtml(),
					$this->html_after);
			}
			else {
				$output = sprintf('<span class="%s">%s</span><br />%s%s%s',
					$this->name_css_class,
					$this->name,
					$this->html_before,
					$this->GetControlHtml(),
					$this->html_after);
			}
			
			return $this->RenderOutput($output);
		}
		
		/**
		 * Returns or displays the final output of a control
		 */
		protected function RenderOutput($output, $display_output = true) {
			
			if ($this->wrap_control) {
				$output = sprintf('<div class="%s">%s</div>',
					$this->wrapper_css_class,
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
			if (!in_array($class, $this->css_class_array)) {
				array_push($this->css_class_array, $class);
				$this->css_class = implode(' ', $this->css_class_array);
			}
		}
		
		/**
		 * Removes a CSS class
		 * @param unknown_type $class
		 */
		public function RemoveCssClass($class) {
			
			$key = array_search($class, $this->css_class_array);
			
			if ($key === false)
				return;
			else {
				unset($this->css_class_array[$key]);
				$this->css_class_array = array_values($this->css_class_array);
			}	
		}
		
		/////////////////////////
		// Public Properties: GET
		/////////////////////////
		public function __get($name) {
			switch ($name) {
				case "ID": return $this->id;
				case "Name": return $this->name;
				case "NameCssClass": return $this->name_css_class;
				case "Value": return $this->value;
				case "CssClass": return $this->css_class;
				case "Style": return $this->style;
				case "Title": return $this->title;
				case "TabIndex": return $this->tab_index;
				case "IsBlockElement": return $this->is_block_element;
				case "HtmlBefore": return $this->html_before;
				case "HtmlAfter": return $this->html_after;
				case "WrapperCssClass": return $this->wrapper_css_class;
				case "WrapControl": return $this->wrap_control;
			}
		}
		
		/////////////////////////
		// Public Properties: SET
		/////////////////////////
		public function __set($name, $value) {
			switch ($name) {
				case "ID":
					return ($this->id = $value);
				case "Name":
					return ($this->name = $value);
				case "Name":
					return ($this->name_css_class = $value);
				case "Value":
					return ($this->value = $value);
				case "CssClass":
					$this->css_class_array = array();
					$this->AddCssClass($value);
					break;
				case "Style":
					return ($this->style = $value);
				case "Title":
					return ($this->title = $value);
				case "TabIndex":
					if(is_numeric($value))
						return ($this->tab_index = $value);
					else
						return false;
				case "IsBlockElement":
					return ($this->is_block_element = $value);
				case "HtmlBefore":
					return ($this->html_before = $value);
				case "HtmlAfter":
					return ($this->html_after = $value);
				case "WrapperCssClass":
					return ($this->wrapper_css_class = $value);
				case "WrapControl":
					return ($this->wrap_control);
			}
		}
	}