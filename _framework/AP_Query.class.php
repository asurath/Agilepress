<?php

class AP_Query {

	protected $author_id;
	protected $post_types = array();
	protected $post_statuses = array();
	protected $order = 'DESC';
	protected $orderby = 'date';
	protected $nopaging = false;
	protected $posts_per_page = 10;
	protected $offset;
	protected $paged = 1;
	protected $page_count;
	protected $result_count;
	protected $results;
	protected $tax_query;
	protected $tax_query_relation;
	protected $post__in = array();
	protected $post__not_in = array();
	protected $related_post_id;
	protected $related_terms = array();
	protected $meta_key;
	protected $meta_value;
	protected $meta_query;
	protected $post_rating;
	protected $search;

	public function query() {

		////////////////////////////////////////////////
		// SET QUERY ARGUMENTS
		////////////////////////////////////////////////
		$query_args = array(
				'post_type' => $this->post_types,
				'post_status' => array('publish'),
				'order' => $this->order,
				'orderby' => $this->orderby
		);

		// Post Statuses
		if ($this->post_statuses)
			$query_args['post_status'] = $this->post_statuses;

		// Post Include list
		if ($this->post__in)
			$query_args['post__in'] = $this->post__in;

		// Post Exclusion list
		if ($this->post__not_in)
			$query_args['post__not_in'] = $this->post__not_in;

		// Author
		if ($this->author_id)
			$query_args['author'] = $this->author_id;

		// Post Parent
		if ($this->post_parent)
			$query_args['post_parent'] = $this->post_parent;

		// Search
		if ($this->search){
			add_filter('posts_search', array($this, 'filter_search_by_title'), 500, 2);
			$query_args['s'] = $this->search;
		}

		// Paging
		if ($this->nopaging) {
			$query_args['nopaging'] = true;
		}
		else {
			$query_args['posts_per_page'] = $this->posts_per_page;
				
			// 'paged' is ignored if 'offset' is set
			if ($this->offset)
				$query_args['offset'] = $this->offset;
			else
				$query_args['paged'] = $this->paged;
		}

		// Meta Values
		if ($this->meta_key)
			$query_args['meta_key'] = $this->meta_key;
		if ($this->meta_value)
			$query_args['meta_value'] = $this->meta_value;

		// Meta Query
		if ($this->meta_query)
			$query_args['meta_query'] = $this->meta_query;

		// Tax Query
		if ($this->tax_query){
			$query_args['tax_query'] = $this->tax_query;
		}

		// Fields to return
		if ($this->fields){
			$query_args['fields'] = $this->fields;
		}

		////////////////////////////////////////////////
		// EXECUTE QUERY
		////////////////////////////////////////////////
		$this->results = new WP_Query($query_args);
		$this->set_result_count($this->results->found_posts);

		return $this->results;
	}

	//////////////////////////////////////////
	// GETTERS/SETTERS
	//////////////////////////////////////////

	//---------------------------------------
	// Author
	//---------------------------------------
	public function set_author_id($author_id) {
		return ($this->author_id = $author_id);
	}

	//---------------------------------------
	// Post Types
	//---------------------------------------
	public function set_post_types($post_types) {
		if (!is_array($post_types) && $post_types != "any")
			$post_types = array($post_types);
		return ($this->post_types = $post_types);
	}

	public function get_post_types(){
		return $this->post_types;
	}
	public function add_post_type($post_type) {
		array_push($this->post_types, $post_type);
	}

	//---------------------------------------
	// Post Status
	//---------------------------------------
	public function set_post_statuses($post_statuses) {
		if (!is_array($post_statuses))
			$post_statuses = array($post_statuses);
		return ($this->post_statuses = $post_statuses);
	}

	public function get_post_statuses(){
		return $this->post_statuses;
	}

	public function add_post_status($post_status) {
		array_push($this->post_statuses, $post_status);
	}

	//---------------------------------------
	// Fields to return
	//---------------------------------------
	public function set_fields($fields){
		return ($this->fields = $fields);
	}

	//---------------------------------------
	// Ordering
	//---------------------------------------
	public function set_order($order) {
		return ($this->order = $order);
	}

	public function set_orderby($orderby) {
		return ($this->orderby = $orderby);
	}

	public function get_order(){
		return $this->order;
	}

	//---------------------------------------
	// Post Parent
	//---------------------------------------
	public function set_post_parent($parent_id) {
		return ($this->post_parent = $parent_id);
	}

	//---------------------------------------
	// Post ID to Include/Exclude
	//---------------------------------------
	public function add_post_id($post_id) {
		array_push($this->post__in, $post_id);
	}

	public function set_post__in($post_ids){
		return ($this->post__in = $post_ids);
	}

	public function add_post_id_to_exclude($post_id) {
		if ($post_id)
			array_push($this->post__not_in, $post_id);
	}

	public function set_post__not_in($post_ids) {
		return ($this->post__not_in = $post_ids);
	}

	//---------------------------------------
	// Counts & Paging
	//---------------------------------------
	public function get_posts_per_page(){
		return $this->posts_per_page;
	}

	public function get_paged(){
		return $this->paged;
	}

	public function get_page_count(){
		return $this->page_count;
	}

	public function get_result_count(){
		return $this->result_count;
	}

	public function has_more_results(){
		$cur_count = $this->get_paged() > 1 ? $this->get_posts_per_page() * $this->get_paged() : $this->get_posts_per_page();
		return $this->get_result_count() > $cur_count ? $this->get_result_count() - $cur_count : false;
	}

	public function set_count($count) {
		return $this->set_posts_per_page($count);
	}

	protected function set_result_count($result_count){
		$this->result_count = $result_count;

		if ($this->result_count > $this->posts_per_page)
			$this->page_count = ceil($this->result_count / $this->posts_per_page);
		else
			$this->page_count = 1;
	}
	public function set_nopaging($nopaging = true) {
		return ($this->nopaging = $nopaging);
	}

	public function set_posts_per_page($posts_per_page) {
		return ($this->posts_per_page = $posts_per_page);
	}

	public function set_offset($offset) {
		return ($this->offset = $offset);
	}

	public function set_paged($paged) {
		return ($this->paged = $paged);
	}

	//---------------------------------------
	// Meta Query
	//---------------------------------------
	public function set_meta_key($meta_key) {
		return ($this->meta_key = $meta_key);
	}
	public function set_meta_value($meta_value) {
		return ($this->meta_value = $meta_value);
	}

	public function add_meta_query(AP_PostMetaQuery $meta_query){
		if (!is_array($this->meta_query))
			$this->meta_query = array();

		$this->meta_query[] = $meta_query->get_meta_query_array();
	}

	public function set_meta_query_relation($relation){
		$relation = strtoupper($relation);
		if ($relation == 'OR')
			$this->meta_query['relation'] = $relation;
		else if ($relation == 'AND')
			unset($this->meta_query['relation']);
	}

	//---------------------------------------
	// Tax Query
	//---------------------------------------
	public function has_tax_query() {
		if ($this->tax_query)
			return true;
		return false;
	}

	public function add_tax_query_array($tax_query_array){
		if (!is_array($tax_query_array))
			return false;

		if (!is_array($this->tax_query))
			$this->tax_query = array();

		$this->tax_query[] = $tax_query_array;
	}

	public function set_tax_query_relation($relation){
		$relation = strtoupper($relation);
		if ($relation == 'AND')
			$this->tax_query['relation'] = 'AND';
		if ($relation == 'OR')
			$this->tax_query['relation'] = 'OR';
	}

	//---------------------------------------
	// Related Post ID
	//---------------------------------------
	public function get_related_post_id() {
		return $this->related_post_id;
	}

	public function set_related_post_id($post_id, $exclude = true) {
		$this->related_post_id = $post_id;
		if ($exclude)
			$this->add_post_id_to_exclude($post_id);
	}

	public function has_related_post_id() {
		if ($this->related_post_id)
			return true;
		return false;
	}

	//---------------------------------------
	// Search
	//---------------------------------------

	public function set_search($search){
		$this->search = $search;
	}

	public function get_search(){
		return $this->search;
	}
	//---------------------------------------
	// Results
	//---------------------------------------
	public function get_results(){
		return $this->results;
	}

	public function get_posts() {
		return isset($this->results->posts) ? $this->results->posts : array();
	}

	public function get_sql(){
		return $this->results->request;
	}
}

class AP_PostTaxQuery {
	protected $taxonomy;
	protected $field;
	protected $field_values = array('id', 'slug');
	protected $terms; // int/string/array
	protected $operator;
	protected $operator_values = array('IN', 'NOT IN', 'AND');
	protected $include_children; // boolean, default: true

	public function set_taxonomy($taxonomy){
		return ($this->taxonomy = $taxonomy);
	}

	public function set_field($field){
		$field = strtolower($field);
		if (in_array($field, $this->field_values))
			return ($this->field = $field);
	}

	public function add_term($term){
		if (is_array($this->terms)){
			$this->terms[] = $term;
		} else {
			if ($this->terms)
				$this->terms = array($this->terms, $term);
			else
				$this->terms = $term;
		}
	}

	public function add_terms(array $terms){
		if (is_array($this->terms)){
			$this->terms = array_merge($this->terms, $terms);
		} else {
			if ($this->terms)
				array_push($terms, $this->terms);
			$this->terms = $terms;
		}
	}

	public function do_not_include_children(){
		$this->include_children = false;
	}

	public function set_operator($operator){
		if (in_array($operator, $this->operator_values)){
			return ($this->operator = $operator);
		}
	}

	public function get_tax_query_array(){
		if (!$this->taxonomy)
			return false;

		$tax_arr = array(
				'taxonomy' => $this->taxonomy,
				'field' => $this->field,
				'terms' => $this->terms
		);

		if ($this->operator)
			$tax_arr['operator'] = $this->operator;

		if ($this->include_children === false)
			$tax_arr['include_children'] = false;

		return $tax_arr;
	}

	public static function CreateArray($taxonomy, $field, array $terms, $include_children, $operator){
		$tax_query = new BS_PostTaxQuery();
		$tax_query->set_taxonomy($taxonomy);
		$tax_query->set_field($field);
		$tax_query->add_terms($terms);

		if (!$include_children)
			$tax_query->do_not_include_children();

		$tax_query->set_operator($operator);

		return $tax_query->get_tax_query_array();
	}
}

class AP_PostMetaQuery {
	protected $key;
	protected $value;
	protected $compare; // defaults to '='
	protected $compare_values = array('=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS', 'NOT EXISTS');
	protected $type; // defaults to 'CHAR'
	protected $type_values = array('NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED');

	public function set_key($key){
		$this->key = $key;
		return $this; // going to attempt function chaining
	}

	public function set_value($value){
		// if $value is array, $this->compare_values = array(in, not in, between, not between);
		$this->value = $value;
		return $this;
	}

	public function set_compare($compare){
		$compare = strtoupper($compare);
		if (in_array($compare, $this->compare_values)){
			$this->compare = $compare;
			return $this;
		}
	}

	public function set_type($type){
		$type = strtoupper($type);
		if (in_array($type, $this->type_values)){
			$this->type = $type;
			return $this;
		}
	}

	public function get_meta_query_array(){
		if (is_null($this->key) || is_null($this->value))
			return false;

		$meta_arr = array(
				'key' => $this->key,
				'value' => $this->value,
		);

		if (is_array($this->value) && is_null($this->compare))
			$meta_arr['compare'] = 'IN';

		if (!is_null($this->compare))
			$meta_arr['compare'] = $this->compare;

		if (!is_null($this->type))
			$meta_arr['type'] = $this->type;

		return $meta_arr;
	}
}