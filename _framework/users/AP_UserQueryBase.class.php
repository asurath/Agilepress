<?php

/**
 * Base class for all User querying 
 * 
 * @author AgilePress Core Developement Team
 *
 *@property string Order
 *@property string OrderBy
 *@property array OrderByArray
 *@property array IncludeArray
 *@property array ExcludeArray
 *@property integer Offset
 *@property string Role
 *@property string Fields
 *@property array FieldsArray
 *@property string Search
 *@property array SearchCollumns
 *@property integer Count
 *@property AP_UserCollection Results
 *@property array MetaQueryArray
 *@property string MetaQueryRelation
 *@property string MetaKey
 */

class AP_UserQueryBase extends AP_Base {

	protected $strOrder = AP_OrderType::DESCENDING; // default: DESC
	protected $strOrderBy = AP_UserOrderByType::ID;
	protected $intIncludeArray = array(); //array
	protected $intExcludeArray = array(); //array
	protected $intOffset;
	protected $strRole;
	protected $strFields = AP_UserFieldType::META_ALL;
	protected $strSearch;
	protected $strSearchColumnArray = array(); //array
	protected $intResultCount;
	protected $objResults;
	protected $mixMetaQueryArray = array(); //array
	protected $strMetaQueryRelation;
	protected $strMetaKey;
	
	/** 
	 * Performs the query based on the entered data, returns an AP_UserCollection and sets $this->Results to the UserCollection
	 *
	 * @return AP_UserCollectionBase objResults
	 */
	
	public function Query() {
	
		$strQueryArgumentArray = array();
		foreach($this as $key=> $value){
			if(isset($this->strOrder))
				$strQueryArgumentArray[AP_UserQueryType::ORDER] = $this->strOrder; 
			if(isset($this->strOrderBy))
				$strQueryArgumentArray[AP_UserQueryType::ORDERBY] = $this->strOrderBy;
			if(isset($this->intIncludeArray) && is_array($this->intIncludeArray))
				$strQueryArgumentArray[AP_UserQueryType::INCLUDEID] = $this->intIncludeArray;
			if(isset($this->intExcludeArray) && is_array($this->intExcludeArray))
				$strQueryArgumentArray[AP_UserQueryType::EXCLUDEID] = $this->intExcludeArray;
			if(isset($this->intOffset))
				$strQueryArgumentArray[AP_UserQueryType::OFFSET] = $this->intOffset;
			if(isset($this->strRole))
				$strQueryArgumentArray[AP_UserQueryType::ROLE] = $this->strRole;
			if(isset($this->strFields))
				$strQueryArgumentArray[AP_UserQueryType::FIELDS] = $this->strFields;
			if(isset($this->strSearch) && isset($this->strSearchColumnArray) && is_array($this->strSearchColumnArray)){
				$strQueryArgumentArray[AP_UserQueryType::SEARCH] = $this->strSearch;
				$strQueryArgumentArray[AP_UserQueryType::SEARCH_COLUMNS] = $this->strSearchColumnArray;
			}
			if(isset($this->mixMetaQueryArray) && !empty($this->mixMetaQueryArray))
				$strQueryArgumentArray[AP_UserQueryType::META_QUERY] = $this->mixMetaQueryArray;
			if(isset($this->strMetaQueryRelation))
				$strQueryArgumentArray['relation'] = $this->strMetaQueryRelation;
			if(isset($this->strMetaKey))
				$strQueryArgumentArray[AP_UserQueryType::META_KEY] = $this->strMetaKey;
		}
		$this->objResults = new WP_User_Query($strQueryArgumentArray);
		$this->Count = $this->objResults->get_total();
		$objUserCollection = new AP_UserCollectionBase; 
		$objUserCollection->Add($this->Results->results);
		$this->objResults = $objUserCollection;
		return $this->objResults;
	}

	//////////////////////////////////////////
	// GETTERS/SETTERS
	//////////////////////////////////////////
	
	public function __get($strName){
		switch($strName) {
			case "Order" :
				return $this->strOrder;
			case "OrderBy" :
				return $this->strOrderBy;
			case "OrderByArray" :
				return AP_UserOrderBy::$OptionArray;
			case "IncludeArray" :
				return $this->intIncludeArray;
			case "ExcludeArray" :
				return $this->intExcludeArray;
			case "Offset" :
				return $this->intOffset;
			case "Role" :
				return $this->strRole; 
			case "Fields" :
				return $this->strFields;
			case "FieldsArray" :
				return AP_UserFields::$OptionArray;
			case "Search" :
				return $this->strSearch;
			case "SearchColumns" :
				return $this->strSearchColumnArray;
			case "SearchColumnsArray" :
				return AP_UserCollumns::$OptionArray;
			case "Count" :
				return $this->intResultCount;
			case "Results" :
				return $this->objResults;
			case "MetaQueryArray" :
				return $this->mixMetaQueryArray;
			case "MetaQueryRelation" :
				return $this->strMetaQueryRelation;
			case "MetaKey" :
				return $this->strMetaKey;
		}
		
	}
	
	public function __set($strName, $mixValue){
		switch($strName) {
			case "Order" :
				return (in_array($mixValue, AP_OrderType::$OptionArray)) ? ($this->strOrder = $mixValue) : $this->error('Order Value must be included in AP_UserOrderBy class');
			case "OrderBy" :
				return (in_array($mixValue, AP_UserOrderByType::$OptionArray)) ? ($this->strOrderBy = $mixValue): $this->error('OrderBy Value must be included in AP_UserOrderBy class: to view call $this->OrderByArray');
			case "OrderByArray" :
				return (AP_UserOrderBy::$OptionArray = $mixValue);
			case "IncludeArray" :
				return ($this->intIncludeArray = $mixValue);
			case "ExcludeArray" :
				return ($this->intExcludeArray = $mixValue);
			case "Role" :
				return $this->strRole;
			case "Offset" :
				return ($this->intOffset = $mixValue);
			case "Fields" :
				return ($this->strFields = $mixValue);
			case "FieldsArray" :
				return (AP_UserFields::$OptionArray = $mixValue);
			case "Search" :
				return ($this->strSearch = $mixValue);
			case "SearchColumns" :
				return ($this->strSearchColumnArray = $mixValue);
			case "SearchColumnsArray" :
				return (AP_UserCollumns::$OptionArray = $mixValue);
			case "Count" :
				return ($this->intResultCount = $mixValue);
			case "MetaQueryRelation" :
				return ($this->SetMetaQueryRelation($mixValue));
			case "MetaKey" :
				return ($this->strMetaKey = $mixValue);
		}
	
	}
	
	/**
	 * Adds an individual UserID to the IncludeArray
	 * 
	 * @param integer $intUserID
	 */
	public function AddIDToInclude($intUserID){
		if(is_int($intUserID)){
			if(!is_array($this->intIncludeArray))
				$this->intIncludeArray = array($intUserID);
			else 
				$this->intIncludeArray[] = $intUserID;
		}
		else
			$this->error("UserID must be of type integer");
	}
	
	/**
	 * Removes an individual UserID from the IncludeArray
	 * 
	 * @param integer $intUserID
	 */
	
	public function RemoveIDToInclude($intUserID){
		if(is_int($intUserID)){
			if(is_array($this->intIncludeArray))
				foreach($this->intIncludeArray as $key => $value)
					if($value == $intUserID) unset($this->intIncludeArray[$key]);
			else
				$this->intIncludeArray = array();
		}
		else
			$this->error("UserID must be of type integer");
	}
		
	/**
	 * Adds an individual UserID to the ExcludeArray
	 * 
	 * @param integer $intUserID
	 */
	
	public function AddIDToExclude($intUserID){
		if(is_int($intUserID)){
			if (is_array($this->intExcludeArray))
				$this->intExcludeArray = array($intUserID);
			else
				$this->intExcludeArray[] = $intUserID;
		}
		else
			$this->error("UserID must be of type integer");
	}
	
	/**
	 * Removes an individual UserID from the ExcludeArray
	 *
	 * @param integer $intUserID
	 */
	
	public function RemoveIDToExclude(integer $intUserID){
		if(is_int($intUserID)){
			if (is_array($this->intExcludeArray))
				foreach($this->intExcludeArray as $key => $value)
					if($value == $intUserID) unset($this->intExcludeArray[$key]);
			else
				$this->intExcludeArray = array();
		}
		else
			$this->error("UserID must be of type integer");
			
	}

	/**
	 * Clears the MetaQueryArray and adds the UserMetaQuery passed as an argument
	 * 
	 * @param AP_UserMetaQuery $objMetaQueryArray
	 */
	
	public function SetMetaQuery(AP_UserMetaQuery $objMetaQueryArray){
		if($objMetaQueryArray instanceof AP_UserMetaQuery)
			$this->mixMetaQueryArray[] = $objMetaQueryArray->GetMetaQueryArray(); 
		else
			$this->error('Argument passed to SetMetaQuery() must be of type AP_UserMetaQuery');
	}
	
	/**
	 * Clears the MetaQueryArray
	 * 
	 */
	
	public function DeleteMetaQuery(){
		$this->mixMetaQueryArray = array();
	}
	
	/**
	 * Adds an individual UserMetaQuery to the MetaQueryArray
	 * 
	 * @param AP_UserMetaQuery $objMetaQueryArray
	 */
	
	public function AddMetaQuery(AP_UserMetaQuery $objMetaQueryArray){
		if (!is_array($this->mixMetaQueryArray))
			$this->mixMetaQueryArray = array();
		if($objMetaQueryArray instanceof AP_UserMetaQuery)
			$this->mixMetaQueryArray[] = $objMetaQueryArray->GetMetaQueryArray();
		else
			$this->error('Argument passed to AddMetaQuery() must be of type AP_UserMetaQuery');
	}
	
	/**
	 * Sets the relationship between multiple MetaQueries, checks for acceptable argument
	 * 
	 * @param string $strRelation
	 */
	
	public function SetMetaQueryRelation($strRelation){
		$strRelation = strtoupper($strRelation);
		if ($strRelation == 'AND')
			$this->mixMetaQueryArray['relation'] = 'AND';
		if ($strRelation == 'OR')
			$this->mixMetaQueryArray['relation'] = 'OR';
	}
	}
	
	
/**
 * Used to create all UserMetaQueries for AP_UserQuery to use 
 * 
 * @author AgilePress Core Developement Team
 *
 *@property string Key
 *@property string Value
 *@property string Compare
 *@property string Type
 */
	
class AP_UserMetaQuery extends AP_Base {
		protected $strKey;
		protected $strValue;
		protected $strCompare; // default: '='
		protected $strType; // default: 'CHAR'
		
		/**
		 * Sets the meta key requirement for a UserMetaQuery
		 * 
		 * @param string $strKey
		 */
		
		public function SetKey($strKey){
			return ($this->strKey = $strKey);
		}
	
		/**
		 * Sets the meta value requirement for a UserMetaQuery
		 *
		 * @param string $strKey
		 */
		
		public function SetValue($strValue){
			return ($this->strValue = $strValue);
		}
		
		/**
		 * Sets the operator to test the meta value
		 * 
		 * @param string $strCompare
		 */
		
		public function SetCompare($strCompare){
			$strCompare = strtoupper($strCompare);
			if (is_array($this->strValue)){
				if (in_array($strCompare, AP_UserCompareType::$OptionArray))
					return ($this->strCompare = $strCompare);
			} else {
				if (in_array($strCompare, AP_UserCompareType::$OptionArray))
					return ($this->strCompare = $strCompare);
			}
		}
		
		/**
		 * Sets the type of Meta Value
		 * 
		 * @param string $strType
		 */
		
		public function SetType($strType){
			$strType = strtoupper($strType);
			if (in_array($strType, AP_UserMetaType::$OptionArray))
				return ($this->strType = $strType);
		}
	
		/**
		 * Converts the current object into an array for use by AP_UserQuery
		 * 
		 * @return array $strMetaArray
		 */
		public function GetMetaQueryArray(){
			if (!$this->strKey)
				return false;
	
			$strMetaArray = array('key' => $this->strKey);
	
			if ($this->strValue)
				$strMetaArray['value'] = $this->strValue;
	
			if ($this->strCompare)
				$strMetaArray['compare'] = $this->strCompare;
	
			if ($this->strType)
				$strMetaArray['type'] = $this->strType;
	
			return $strMetaArray;
		}
	
	}
	
