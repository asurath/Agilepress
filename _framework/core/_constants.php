<?php

class AP_CodeGenConstants {
	
	public static $PHPReservedWordArray = array(" new ", " null ", " break ", " return ", " switch ", " self ", " case ", " const ", " clone ", " continue ", " declare ", " default ", " echo ", " else ", " elseif ", " empty ", " exit ", " eval ", " if ", " try ", " throw ", " catch ", " public ", " private ", " protected ", " function ", " extends ", " foreach ", " for ", " while ", " do ", " var ", " class ", " static ", " abstract ", " isset ", " unset ", " implements ", " interface ", " instanceof ", " include ", " include_once ", " require ", " require_once ", " abstract ", " and ", " or ", " xor ", " array ", " list ", " false ", " true ", " global ", " parent ", " print ", " exception ", " namespace ", " goto ", " final ", " endif ", " endswitch ", " enddeclare ", " endwhile ", " use ", " as ", " endfor ", " endforeach ", " this ");
	public static $FolderNames = array("post", "user", "post-types","display", "user-types" , "pages",  "js", "install", "css", "controls", "auto-includes", "ajax", "taxonomy","constants");
	public static $HasGeneratedArray = array( "post" , "user" , "post-types", "user-types" , "display" , "taxonomy", "constants" );
	public static $PostSupports = array( "title", "editor", "author", "thumbnail", "excerpt", "trackbacks", "custom-fields", "comments", "revisions", "page-attributes", "post-formats");
}

class AP_PostStatusType {
	const PUBLISHED = 'publish';
	const DRAFT = 'draft';
	const AUTODRAFT = 'auto-draft';
	const INHERIT = 'inherit';

	public static $OptionArray = array(
		AP_PostStatusType::AUTODRAFT,
		AP_PostStatusType::DRAFT,
		AP_PostStatusType::INHERIT,
		AP_PostStatusType::PUBLISHED
	);
}


class AP_UserMetaType {
	const NUMERIC = 'NUMERIC';
	const BINARY = 'BINARY';
	const CHARACTER = 'CHAR';
	const DATE = 'DATE';
	const DATETIME ='DATETIME';
	const DECIMAL = 'DECIMAL';
	const SIGNED = 'SIGNED';
	const TIME = 'TIME';
	const UNSIGNED = 'UNSIGNED';

	public static $OptionArray = array(
			AP_UserMetaType::BINARY,
			AP_UserMetaType::CHARACTER,
			AP_UserMetaType::DATE,
			AP_UserMetaType::DATETIME,
			AP_UserMetaType::DECIMAL,
			AP_UserMetaType::NUMERIC,
			AP_UserMetaType::SIGNED,
			AP_UserMetaType::TIME,
			AP_UserMetaType::UNSIGNED
	);


}


class AP_UserCompareType {
	const IN = 'IN';
	const NOT_IN = 'NOT_IN';
	const BETWEEN = 'BETWEEN';
	const NOT_BETWEEN = 'NOT BETWEEN';
	const EXISTS = 'EXISTS';
	const NOT_EXISTS = 'NOT_EXISTS';

	public static $OptionArray = array(
			AP_UserCompareType::BETWEEN,
			AP_UserCompareType::EXISTS,
			AP_UserCompareType::IN,
			AP_UserCompareType::NOT_BETWEEN,
			AP_UserCompareType::NOT_EXISTS,
			AP_UserCompareType::NOT_IN
	);

}

class AP_UserQueryType {
	const ORDER = 'order';
	const ORDERBY = 'orderby';
	const INCLUDEID = 'include';
	const EXCLUDEID = 'exclude';
	const NUMBER = 'number';
	const OFFSET = 'offset';
	const FIELDS = 'fields';
	const SEARCH = 'search';
	const SEARCH_COLUMNS = 'search_columns';
	const META_QUERY = 'meta_query';
	const META_KEY = 'meta_key';
	const ROLE = 'role';

	public static $OptionArray = array(
			AP_UserQueryType::EXCLUDE_ID,
			AP_UserQueryType::FIELDS,
			AP_UserQueryType::INCLUDE_ID,
			AP_UserQueryType::META_KEY,
			AP_UserQueryType::META_QUERY,
			AP_UserQueryType::NUMBER,
			AP_UserQueryType::OFFSET,
			AP_UserQueryType::ORDER,
			AP_UserQueryType::ORDERBY,
			AP_UserQueryType::SEARCH,
			AP_UserQueryType::SEARCH_COLUMNS,
			AP_UserQueryType::ROLE
	);

}


class AP_UserOrderByType {
	const USERNAME = 'user_name';
	const NICENAME = 'user_nicename';
	const DATE = 'date';
	const LOGIN = 'user_login';
	const EMAIL = 'user_email';
	const URL = 'user_url';
	const REGISTERED = 'user_registered';
	const DISPLAY_NAME = 'display_name';
	const POST_COUNT = 'post_count';
	const ID = 'ID';

	public static $OptionArray = array(
			AP_UserOrderByType::DATE,
			AP_UserOrderByType::DISPLAY_NAME,
			AP_UserOrderByType::EMAIL,
			AP_UserOrderByType::ID,
			AP_UserOrderByType::LOGIN,
			AP_UserOrderByType::NICENAME,
			AP_UserOrderByType::POST_COUNT,
			AP_UserOrderByType::REGISTERED,
			AP_UserOrderByType::URL,
			AP_UserOrderByType::USERNAME
	);

}

class AP_UserFieldType {
	const ID = 'ID';
	const DISPLAY_NAME = 'display_name';
	const LOGIN = 'user_login';
	const NICENAME = 'user_nicename';
	const EMAIL = 'user_email';
	const URL = 'user_url';
	const REGISTERED = 'user_registered';
	const ALL = 'all';
	const META_ALL = 'all_with_meta';

	public static $OptionArray = array(
			AP_UserFieldType::ALL,
			AP_UserFieldType::DISPLAY_NAME,
			AP_UserFieldType::EMAIL,
			AP_UserFieldType::ID,
			AP_UserFieldType::LOGIN,
			AP_UserFieldType::META_ALL,
			AP_UserFieldType::NICENAME,
			AP_UserFieldType::REGISTERED,
			AP_UserFieldType::URL
	);
}
	
class AP_UserColumnType {
	const ID = 'ID';
	const LOGIN = 'user_login';
	const EMAIL = 'user_email';
	const URL = 'user_url';
	const NICENAME = 'user_nicename';

	public static $OptionArray = array(
			AP_UserColumnType::EMAIL,
			AP_UserColumnType::ID,
			AP_UserColumnType::LOGIN,
			AP_UserColumnType::NICENAME,
			AP_UserColumnType::URL
	);
}

abstract class AP_TextMode {
	const SINGLELINE = 'SingleLine';
	const MULTILINE = 'MultiLine';
	const PASSWORD = 'Password';
}

class AP_OrderType {
	const ASCENDING = 'ASC';
	const DESCENDING = 'DESC';

	public static $OptionArray = array(
			AP_OrderType::ASCENDING,
			AP_OrderType::DESCENDING
	);
}

class AP_OrderByPost {
	const NONE = 'none';
	const ID = 'id';
	const AUTHOR = 'author';
	const TITLE = 'title';
	const NAME = 'name';
	const DATE = 'date';
	const MODIFIED = 'modified';
	const PARENT_POST = 'parent';
	const RANDOM = 'rand';
	const COMMENT_COUNT = 'comment_count';
	const MENU_ORDER = 'menu_order';
	const META = 'meta_value';
	const META_NUMERIC = 'meta_value_num';
	const POST_IN = 'post__in';

	public static $OptionArray = array(
			AP_OrderByPost::NONE,
			AP_OrderByPost::ID,
			AP_OrderByPost::AUTHOR,
			AP_OrderByPost::TITLE,
			AP_OrderByPost::NAME,
			AP_OrderByPost::DATE,
			AP_OrderByPost::MODIFIED,
			AP_OrderByPost::PARENT_POST,
			AP_OrderByPost::RANDOM,
			AP_OrderByPost::COMMENT_COUNT,
			AP_OrderByPost::MENU_ORDER,
			AP_OrderByPost::META,
			AP_OrderByPost::META_NUMERIC,
			AP_OrderByPost::POST_IN
	);

}

