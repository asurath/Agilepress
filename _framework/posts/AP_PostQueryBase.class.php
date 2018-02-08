<?php

/**
 * 
 * Base class for all Post querying
 * 
 * @author AgilePress Core Developement Team
 *
 * @property integer AuthorID
 * @property string[] TypeArray
 * @property string[] StatusArray
 * @property string Order
 * @property string OrderBy
 * @property boolean NoPaging
 * @property integer PostsPerPage
 * @property integer Offset
 * @property integer Paged
 * @property integer PageCount
 * @property integer ResultCount/Count
 * @property AP_PostCollectionBase Results
 * @property AP_PostTaxQuery TaxQuery
 * @property string TaxQueryRelationship
 * @property integer[] IncludedArray
 * @property integer[] ExcludedArray
 * @property integer[] RelatedID
 * @property string[] RelatedTermArray
 * @property string MetaKey
 * @property string MetaValue
 * @property AP_MetaQuery MetaQuery
 * @property string Search
 * @property AP_PostItem[] Posts
 * @property array All
 */


class AP_PostQueryBase extends AP_Base {

	protected $intAuthorID;
	protected $strPostTypeArray = array();
	protected $strPostStatusArray = array();
	protected $strOrder = AP_OrderType::DESCENDING;
	protected $strOrderBy = AP_OrderByPost::ID;
	protected $boolNoPaging = true;
	protected $intPostsPerPage = 10;
	protected $intOffset;
	protected $intPaged = 1;
	protected $intPageCount;
	protected $intResultCount;
	protected $objResults;
	protected $objTaxQuery;
	protected $strTaxQueryRelation;
	protected $intPostInArray = array();
	protected $intPostNotInArray = array();
	protected $intRelatedPostID;
	protected $mixRelatedTermsArray = array();
	protected $mixQueryDateLength;
	protected $strMetaKey;
	protected $strMetaValue;
	protected $objMetaQuery;
	protected $mixSearch;

	/**
	 *  
	 *  Takes the current objects query parameters, performs the query and returns a
	 *  AP_PostCollection as well as setting $this->Results to the collection
	 * 
	 * @return AP_PostCollectionBase
	 */
	
	public function Query() {

		////////////////////////////////////////////////
		// SET QUERY ARGUMENTS
		////////////////////////////////////////////////
		$mixQueryArgsArray = array(
				'post_type' => $this->strPostTypeArray,
				'post_status' => array('publish'),
				'order' => $this->strOrder,
				'orderby' => $this->strOrderBy
		);

		// Post Statuses
		if ($this->strPostStatusArray)
			$mixQueryArgsArray['post_status'] = $this->strPostStatusArray;

		// Post Include list
		if ($this->intPostInArray)
			$mixQueryArgsArray['post__in'] = $this->intPostInArray;

		// Post Exclusion list
		if ($this->intPostNotInArray)
			$mixQueryArgsArray['post__not_in'] = $this->intPostNotInArray;

		// Author
		if ($this->intAuthorID)
			$mixQueryArgsArray['author'] = $this->intAuthorID;

		// Post Parent
		if ($this->post_parent)
			$mixQueryArgsArray['post_parent'] = $this->post_parent;

		// Search
		if ($this->mixSearch){
			add_filter('posts_search', array($this, 'filter_search_by_title'), 500, 2);
			$mixQueryArgsArray['s'] = $this->mixSearch;
		}

		// Paging
		if ($this->boolNoPaging) {
			$mixQueryArgsArray['nopaging'] = true;
		}
		else {
			$mixQueryArgsArray['posts_per_page'] = $this->intPostsPerPage;
				
			// 'paged' is ignored if 'offset' is set
			if ($this->intOffset)
				$mixQueryArgsArray['offset'] = $this->intOffset;
			else
				$mixQueryArgsArray['paged'] = $this->intPaged;
		}

		// Date Length
		if ($this->mixQueryDateLength)
			$mixQueryArgsArray['date_query'] = $this->mixQueryDateLength;

		// Meta Values
		if ($this->strMetaKey)
			$mixQueryArgsArray['meta_key'] = $this->strMetaKey;
		if ($this->strMetaValue)
			$mixQueryArgsArray['meta_value'] = $this->strMetaValue;

		// Meta Query
		if ($this->objMetaQuery)
			$mixQueryArgsArray['meta_query'] = $this->objMetaQuery;

		// Tax Query
		if ($this->objTaxQuery){
			$mixQueryArgsArray['tax_query'] = $this->objTaxQuery;
		}


		////////////////////////////////////////////////
		// EXECUTE QUERY
		////////////////////////////////////////////////
		$this->objResults = new WP_Query($mixQueryArgsArray);
		$this->SetResultCount($this->objResults->found_posts);
		$objNewCollection = new AP_PostCollectionBase;
		$objNewCollection->Add($this->Posts);
		$this->objResults = $objNewCollection;
		return $this->objResults;
	}

	//////////////////////////////////////////
	// GETTERS/SETTERS
	//////////////////////////////////////////

	public function __get($strName){
		switch($strName){
			case 'AuthorID' :
				return $this->intAuthorID;
			case 'PostTypeArray' : 
				return $this->strPostTypeArray;
			case 'TypeArray' : 
				return $this->strPostTypeArray;
			case 'StatusArray' :
				return $this->strPostStatusArray;
			case 'Order' : 
				return $this->strOrder;
			case 'OrderBy' :
				return $this->strOrderBy;
			case 'NoPaging' :
				return $this->boolNoPaging;
			case 'PostsPerPage' :
				return $this->intPostsPerPage;
			case 'Offset' :
				return $this->intOffset;
			case 'Paged' :
				return $this->intPaged;
			case 'PageCount' :
				return $this->intPageCount;
			case 'Count' :
				return $this->intResultCount;
			case 'ResultCount' :
				return $this->intResultCount;
			case 'Results' :
				return $this->objResults;
			case 'TaxQuery' :
				return $this->objTaxQuery;
			case 'TaxQueryRelationship' :
				return $this->strTaxQueryRelation;
			case 'IncludedArray' :
				return $this->intPostInArray;
			case 'ExcludedArray' :
				return $this->intPostNotInArray;
			case 'RelatedID' :
				return $this->intRelatedPostID; 
			case 'RelatedTermArray' :
				return $this->mixRelatedTermsArray;
			case 'QueryDateLength' :
				return $this->mixQueryDateLength;
			case 'MetaKey' :
				return $this->strMetaKey;
			case 'MetaValue' :
				return $this->strMetaValue;
			case 'MetaQuery' :
				return $this->objMetaQuery;
			case 'Search' :
				return $this->mixSearch; 
			case 'Posts' :
				return ($this->objResults->posts);
			case 'SQL' :
				return ($this->objResults->request);
			case 'All' :
				$mixAllArray = array();
				foreach ($this as $key => $value)
					$mixAllArray[$key] = $value;
				return $mixAllArray; 
				break; 
		}
	}
	
	public function __set($strName, $mixValue){
		switch($strName){
			case 'AuthorID' :
				return ($this->intAuthorID = $mixValue);
			case 'TypeArray' :
				return ($this->SetPostTypes($mixValue));
			case 'PostTypeArray' :
				return ($this->SetPostTypes($mixValue));
			case 'StatusArray' :
				if(is_array($mixValue))
					return ($this->SetPostStatusArray($mixValue));
				else
					$this->error('StatusArray property must be of type Array');
				break;
			case 'Order' :
				if($mixValue == AP_OrderType::ASCENDING || $mixValue == AP_OrderType::DESCENDING)
					return ($this->strOrder = $mixValue);
				else
					$this->error('Invalid order value: Use AP_OrderType constants');
				break;
			case 'OrderBy' :
				if(in_array($mixValue, AP_OrderByPost::$OptionArray))
					return ($this->strOrderBy = $mixValue);
				else
					$this->error('Invalid order-by value: Use AP_OrderByPost constants');
				break;			
			case 'NoPaging' :
				return ($this->boolNoPaging = $mixValue);
			case 'PostsPerPage' :
				return ($this->intPostsPerPage = $mixValue);
			case 'Offset' :
				return ($this->intOffset = $mixValue);
			case 'Paged' :
				return ($this->intPaged = $mixValue);
			case 'PageCount' :
				return ($this->intPageCount = $mixValue);
			case 'ResultCount' :
				return ($this->SetResultCount($mixValue));
			case 'Count' :
				return ($this->SetResultCount($mixValue));
			case 'Results' :
				return ($this->objResults = $mixValue);
			case 'TaxQuery' :
				if (!$tax_query instanceof AP_PostTaxQuery)
					return false;
				$this->objTaxQuery = $tax_query->GetTaxQueryArray();
				break;
			case 'TaxQueryRelationship' :
				return ($this->SetTaxQueryRelation($mixValue));
			case 'IncludedArray' :
				if(is_array($mixValue))
					return ($this->intPostInArray = $intPostIDArray);
				else
					$this->error('IncludedIDArray property must be of type Array');
				break;
			case 'ExcludedArray' :
				if(is_array($mixValue))
					return ($this->intPostNotInArray = $intPostIDArray);
				else
					$this->error('ExcludedIDArray property must be of type Array');
				break;
			case 'RelatedID' :
				return ($this->intRelatedPostID = $mixValue);
			case 'RelatedTerms' :
				return ($this->mixRelatedTermsArray = $mixValue);
			case 'MetaKey' :
				return ($this->strMetaKey = $mixValue);
			case 'MetaValue' :
				return ($this->strMetaValue = $mixValue);
			case 'MetaQuery' :
				return ($this->objMetaQuery = $mixValue);
			case 'Search' :
				return ($this->mixSearch = $mixValue);
			case 'All' :
				if (is_array($mixValue)){ foreach($mixValue as $key => $value) $this->$key = $value;} else $this->error('All value must be an array'); 
				
		}
	}
	
	/**
	 * This function allows you to use WordPress core render functions such as the_title(), the_excerpt(), etc.
	 *
	 * @param AP_Post $objPost
	 */
	public static function SetWPPost($objPost){
		if($objPost instanceof AP_PostItemBase || $objPost instanceof WP_Post){
			global $post;
			if($objPost instanceof AP_PostItemBase)
				$post = $objPost->Post;
			elseif ($objPost instanceof WP_Post)
				$post = $objPost;
			setup_postdata($post);
		}
		else
			self::error("Object passed to AP_PostQueryBase::SetWPPost must be a WP_Post object or AP_PostItem object");
	}
	
	
	//---------------------------------------
	// Post Types
	//---------------------------------------
	
	/**
	 * Sets the PostTypes to be included in the query
	 * 
	 * @param {WP Post-Type slugs or WP custom Post-Type slugs or AP custom Post-Type slugs} $strPostTypeArray
	 */
	
	public function SetPostTypes($strPostTypeArray) {
		if (!is_array($strPostTypeArray) && $strPostTypeArray != "any")
			$strPostTypeArray = array($strPostTypeArray);
		return ($this->strPostTypeArray = $strPostTypeArray);
	}
	
	/**
	 *  Takes a post-type slug and adds it to the Post-types
	 * 
	 * @param string $strPostType
	 */
	
	public function AddPostType($strPostType) {
		array_push($this->strPostTypeArray, $strPostType);
	}

	//---------------------------------------
	// Post Status
	//---------------------------------------
	
	/**
	 *  Sets the Post Statuses to be included in the query
	 * 
	 * @param string[] $strPostStatusArray
	 */
	
	public function SetPostStatusArray($strPostStatusArray) {
		if (!is_array($strPostStatusArray))
			$strPostStatusArray = array($strPostStatusArray);
		return ($this->strPostStatusArray = $strPostStatusArray);
	}
	
	/**
	 * 	Adds a status to the post status array of statuses to be included 
	 * 
	 * @param string $strPostStatus
	 */

	public function AddPostStatus($strPostStatus) {
		array_push($this->strPostStatusArray, $strPostStatus);
	}

	
	//---------------------------------------
	// Post ID to Include/Exclude
	//---------------------------------------
	
	/**
	 * 	Adds a post ID to be included in the query
	 * 
	 * @param integer[] $intPostID
	 */
	
	public function AddPostIDToInclude($intPostID) {
		array_push($this->intPostInArray, $intPostID);
	}
	
	/**
	 * 	Adds a post ID to be Excluded in the query
	 *
	 * @param integer $intPostID
	 */
	
	public function AddPostIDToExclude($intPostID) {
		if ($intPostID)
			array_push($this->intPostNotInArray, $intPostID);
	}

	//---------------------------------------
	// Counts & Paging
	//---------------------------------------
	
	/**
	 *  Returns false or the amount of Results removed via paging
	 * 
	 * @return boolean or integer
	 */
	
	public function HasMoreResults(){
		$intCurrentCount = $this->Paged > 1 ? $this->PostsPerPage * $this->Paged : $this->PostsPerPage;
		return $this->ResultCount > $intCurrentCount ? $this->ResultCount - $intCurrentCount : false;
	}

	
	/**
	 * Sets the maximum result count of posts to be returned when Query() is called
	 * 
	 * @param integer $intResultCount
	 */
	
	protected function SetResultCount($intResultCount){
		$this->intResultCount = $intResultCount;
		if ($this->intResultCount > $this->intPostsPerPage)
			$this->intPageCount = ceil($this->intResultCount / $this->intPostsPerPage);
		else
			$this->intPageCount = 1;
	}

	//---------------------------------------
	// Date Query
	//---------------------------------------

	/**
	 * Sets length in time starting from now to return posts from
	 * 
	 * @param integer $intYears
	 * @param integer $intMonths
	 * @param integer $intDays
	 */
	
	public function SetQueryDateLength($intYears = 0, $intMonths = 0, $intDays = 0) {
		$objDate = new AP_DateTime(BS_DateTime::Now);
		$objDateNow = new AP_DateTime(BS_DateTime::Now);
		$objDate->AddMonths(-1*$intMonths);
		$objDate->AddYears(-1*$intYears);
		$objDate->AddDays(-1*$intDays);
		$this->SetQueryDates($objDateNow ,$date_object);
	}

	/**
	 * Sets the dates of posts to be included when Query() is called, inclusive
	 * 
	 * @param AP_DateTime $objStart
	 * @param AP_DateTime $objFinish
	 */

	public function SetQueryDates(AP_DateTime $objStart, AP_DateTime $objFinish = null) {
		if($objFinish == null)
			$objFinish = new AP_DateTime(AP_DateTime::Now);
		$this->mixQueryDateLength = array( array(
				'after'=> array(
						'year' => $objStart->ToString('YYYY'),
						'month' => $objStart->ToString('M'),
						'day' => $objStart->ToString('D') ),
				'before'=> array(
						'year' => $objFinish->ToString('YYYY'),
						'month' => $objFinish->ToString('M'),
						'day' => $objFinish->ToString('D') ),
				'inclusive' => true ));
	}

	/**
	 *  Clears all date qualifiers used by Query()
	 * 
	 */
	
	public function DeleteQueryDateLength() {
		$this->mixQueryDateLength = null;
	}

	//---------------------------------------
	// Meta Query
	//---------------------------------------
	
	/**
	 * 	Takes a singular or array of AP_PostMetaQuery objects and adds them to
	 * 	the Query() meta query list
	 * 
	 * @param {AP_PostMetaQuery or AP_PostMetaQuery[]} $objMetaQuery
	 */

	public function AddMetaQuery($objMetaQuery){
		if (!is_array($this->objMetaQuery))
			$this->objMetaQuery = array();
		$boolObjectTest = true;
		if(is_array($objMetaQuery)){
			foreach($objMetaQuery as $AP_MetaQuery)
				if(!$AP_MetaQuery instanceof AP_PostMetaQuery)
					$boolObjectTest = false; 
			if($boolObjectTest)
				$this->objMetaQuery = array_merge($this->objMetaQuery, $objMetaQuery->GetMetaQueryArray());
			else 
				$this->error('AddMetaQuery() only accepts AP_PostMetaQuery objects or an array of AP_PostMetaQuery objects');
		}	
		elseif ($objMetaQuery instanceof AP_PostMetaQuery)
			$this->objMetaQuery[] = $objMetaQuery->GetMetaQueryArray();
		else 
			$this->error('AddMetaQuery() only accepts AP_PostMetaQuery objects or an array of AP_PostMetaQuery objects');
	}
	
	/**
	 * Sets the Query() meta relation for the meta-queries in $this>MetaQuery 
	 * 
	 * @param string $strRelation
	 */
	public function SetMetaQueryRelation($strRelation){
		$strRelation = strtoupper($strRelation);
		if ($strRelation == 'OR')
			$this->objMetaQuery['relation'] = $strRelation;
		else if ($strRelation == 'AND')
			unset($this->objMetaQuery['relation']);
	}

	//---------------------------------------
	// Tax Query
	//---------------------------------------
	
	/**
	 *  Returns true if there TaxQuery has been set, false if no TaxQuery is set
	 * 
	 * @return boolean 
	 */
	
	
	public function HasTaxQuery() {
		if ($this->objTaxQuery)
			return true;
		return false;
	}

	/**
	 * Adds a Tax Query to the Tax Query Array
	 * 
	 * @param AP_PostTaxQuery $tax_query
	 */
	
	public function AddTaxQuery(AP_PostTaxQuery $objTaxQuery){
		if (!$objTaxQuery instanceof AP_PostTaxQuery)
			return false;
		
		if (!is_array($this->objTaxQuery))
			$this->objTaxQuery = array();

		$this->objTaxQuery[] = $objTaxQuery->GetTaxQueryArray();
	}

	/**
	 * Sets the Tax Query relationships of the Tax Query Array
	 *
	 * @param AP_PostTaxQuery $tax_query
	 */
	
	
	public function SetTaxQueryRelation($strRelation){
		$strRelation = strtoupper($strRelation);
		if ($strRelation == 'AND')
			$this->objTaxQuery['relation'] = 'AND';
		if ($strRelation == 'OR')
			$this->objTaxQuery['relation'] = 'OR';
	}

	//---------------------------------------
	// Related Post ID
	//---------------------------------------
	
	/**
	 *  Sets the Related ID field for Query(), if $boolExclude is set to true, Related posts are excluded
	 * 
	 * @param integer $intPostID
	 * @param boolean $boolExclude [default = true]
	 */
	
	public function SetRelatedPostID($intPostID, $boolExclude = false) {
		$this->intRelatedPostID = $intPostID;
		if (!$boolExclude)
			$this->AddPostIDToExclude($intPostID);
	}
	
	/**
	 * 	Returns true or false depending on if a Related ID has been set
	 * 
	 * @return boolean
	 */
	
	public function HasRelatedPostID() {
		if ($this->intRelatedPostID)
			return true;
		return false;
	}

}

/**
 *  Post tax query class for created new Taxonomy queries, Is usually passed to a Tax Query function in AP_PostQueryBase
 * 
 * @author AgilePress Core Developement Team
 * 
 * @property string Taxonomy
 * @property string Field
 * @property string[] FieldValuesArray
 * @property array TermsArray
 * @property string Operator
 * @property string OperatorValuesArray
 * @property boolean IncludeChildren
 *
 */

class AP_PostTaxQuery extends AP_Base {
	protected $strTaxonomy;
	protected $strField;
	protected $strFieldValuesArray = array('id', 'slug');
	protected $mixTermsArray; // int/string/array
	protected $strOperator;
	protected $strOperatorValuesArray = array('IN', 'NOT IN', 'AND');
	protected $boolIncludeChildren; // boolean, default: true

	public function __get($strName){
		switch ($strName) {
			case "Taxonomy":
				return $this->strTaxonomy;
			case "Field":
				return $this->strField;
			case "FieldValuesArray":
				return $this->strFieldValuesArray;
			case "TermArray":
				return $this->mixTermsArray;
			case "Operator":
				return $this->strOperator;
			case "OperatorValuesArray":
				return $this->strOperatorValuesArray;
			case "IncludeChildren":
				return $this->boolIncludeChildren;
		}
		
	}
	
	public function __set($strName, $mixValue){
		switch ($strName) {
			case "Taxonomy" :
				return ($this->strTaxonomy = $strTaxonomy);
			case "Field" :
				return ($this->SetField($mixValue));
			case "TermArray" :
				return ($this->mixTermsArray = $mixValue);
			case "Operator" :
				return ($this->strOperator = $mixValue);
			case "IncludeChildren" :
				if(is_bool($mixValue))
					return ($this->boolIncludeChildren = $mixValue);
				else 
					return $this->error("AP_PostTaxQuery::IncludeChildren must be of type boolean");
				break;
		}
		
	}

	/**
	 * Sets the current objects Field to the value passed, checks the value against acceptable values in $this->FieldValuesArray
	 * 
	 * @param string $strField
	 */
	
	public function SetField($strField){
		$strField = strtolower($strField);
		if (in_array($strField, $this->strFieldValuesArray))
			return ($this->strField = $strField);
	}
	
	/**
	 * Adds a term to the current term array 
	 * 
	 * @param string $strTerm
	 */
	
	public function AddTerm($strTerm){
		if (is_array($this->mixTermsArray)){
			$this->mixTermsArray[] = $strTerm;
		} else {
			if ($this->mixTermsArray)
				$this->mixTermsArray = array($this->mixTermsArray, $strTerm);
			else
				$this->mixTermsArray = $strTerm;
		}
	}

	/**
	 * Adds an array of terms to the current term array
	 * 
	 * @param string[] $strTermsArray
	 */
	
	public function AddTerms(array $strTermsArray){
		if (is_array($this->mixTermsArray)){
			$this->mixTermsArray = array_merge($this->mixTermsArray, $strTermsArray);
		} else {
			if ($this->mixTermsArray)
				array_push($strTermsArray, $this->mixTermsArray);
			$this->mixTermsArray = $strTermsArray;
		}
	}

	/**
	 * Sets the operator of the current object, checks the value against acceptable values in $this->OperatorValuesArray
	 * 
	 * @param string $strOperator
	 */
	
	public function SetOperator($strOperator){
		if (in_array($strOperator, $this->strOperatorValuesArray)){
			return ($this->strOperator = $strOperator);
		}
	}
	
	/**
	 * Takes the current TaxQuery object and returns an array to be used by Query()
	 * 
	 * @return array $mixTaxArray
	 */
	
	public function GetTaxQueryArray(){
		if (!$this->strTaxonomy)
			return false;

		$mixTaxArray = array(
				'taxonomy' => $this->strTaxonomy,
				'field' => $this->strField,
				'terms' => $this->mixTermsArray
		);

		if ($this->strOperator)
			$mixTaxArray['operator'] = $this->strOperator;

		if ($this->boolIncludeChildren === false)
			$mixTaxArray['include_children'] = false;

		return $mixTaxArray;
	}
	
	/**
	 * Takes several arguments and returns an array to be used by Query()
	 * 
	 * @param string $strTaxonomy
	 * @param string $strField
	 * @param array $strTermsArray
	 * @param boolean $boolIncludeChildren
	 * @param string $strOperator
	 */
	

	public static function CreateArray($strTaxonomy, $strField, array $strTermsArray, $boolIncludeChildren, $strOperator){
		$objTaxQuery = new AP_PostTaxQuery();
		$objTaxQuery->SetTaxonomy($strTaxonomy);
		$objTaxQuery->SetField($strField);
		$objTaxQuery->AddTerms($strTermsArray);
		$objTaxQuery->IncludeChildren($boolIncludeChildren);
		$objTaxQuery->SetOperator($strOperator);

		return $objTaxQuery->GetTaxQueryArray();
	}
}

/**
 * Post meta query class for creating new meta queries, the object is usually passed to an AP_PostQueryBase function
 * 
 * @author AgilePress Core Developement Team
 *
 * @property string Key
 * @property mixed Value
 * @property string Compare [default = "="]
 * @property string[] CompareValuesArray
 * @property string Type
 * @property string TypeValuesArray 
 */

class AP_PostMetaQuery extends AP_Base {
	protected $strKey;
	protected $mixValue;
	protected $strCompare; // defaults to '='
	protected $strCompareValuesArray = array('=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS', 'NOT EXISTS');
	protected $strType; // defaults to 'CHAR'
	protected $strTypeValuesArray = array('NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED');

	
	public function __get($strName){
		switch($strName){
			case "Key":
				return $this->strKey;
			case "Value":
				return $this->mixValue;
			case "Compare":
				return $this->strCompare;
			case "CompareValuesArray":
				return $this->CompareValuesArray;
			case "Type":
				return $this->strType;
			case "TypeValuesArray":
				return $this->strTypeValuesArray;
		}
	}
	
	public function __set($strName, $mixValue){
		switch($strName){
			case "Key":
				return($this->strKey = $mixValue);
			case "Value":
				return($this->mixValue = $mixValue);
			case "Compare":
				return ($this->SetCompare($mixValue));
			case "Type":
				return ($this->SetType($mixValue));
		}		
	}
	
	/**
	 * Sets the current objects Compare value, checks the value passed against acceptable values in $this->CompareValuesArray
	 * 
	 * @param strings $strCompare
	 */

	public function SetCompare($strCompare){
		$strCompare = strtoupper($strCompare);
		if (in_array($strCompare, $this->strCompareValuesArray)){
			$this->strCompare = $strCompare;
			return $this;
		}
	}
	
	/**
	 * Sets the current objects Type value, checks the value passed against acceptable values in $this->TypeValuesArray
	 *
	 * @param strings $strCompare
	 */
	
	public function SetType($strType){
		$strType = strtoupper($strType);
		if (in_array($strType, $this->strTypeValuesArray)){
			$this->strType = $strType;
			return $this;
		}
	}

	/**
	 * Takes the current object and returns a MetaQuery Array for use by Query()
	 * 
	 * @return array $mixMetaArray
	 */
	
	public function GetMetaQueryArray(){
		if (is_null($this->strKey) || is_null($this->mixValue))
			return false;

		$mixMetaArray = array(
				'key' => $this->strKey,
				'value' => $this->mixValue,
		);

		if (is_array($this->mixValue) && is_null($this->strCompare))
			$mixMetaArray['compare'] = 'IN';

		if (!is_null($this->strCompare))
			$mixMetaArray['compare'] = $this->strCompare;

		if (!is_null($this->strType))
			$mixMetaArray['type'] = $this->strType;

		return $mixMetaArray;
	}
}

