<?php

class AP_PageBase extends AP_Base {
	protected $id = 'default-page';
	// TODO error codes
	protected $error_msgs = array();
	protected $error_codes = array();
	protected $success_msgs = array();
	protected $success_codes = array();
	protected $tpl;
	protected $use_sidebar = true; // on/off whether to call $this->sidebar() method
	
	public function __construct($id = null){
		if ($id)
			$this->id = $id;
	}
	
	// page process
	protected function Init(){}
	protected function check_ajax(){
		if ($this->is_ajax()){
			$this->do_ajax();
			exit();
		}
	}
	protected function is_ajax(){
		return $_POST['action'] == 'ajax';
	}
	
	protected function do_ajax(){}
	protected function prerender(){}
	
	protected function header(){
		get_header();
	}
		protected function content_start(){ ?>
			<div id="main">
		<?php }
		protected function content(){
			if (have_posts()) :  while (have_posts()) : the_post(); ?>
				<div class="post">
					<h1 id="archive-title" class="page-title"><?php the_title(); ?></h1>	
					<?php the_content(); ?>
				</div><!-- .post -->
			<?php endwhile; endif;
		}
		protected function content_end(){ ?>
			</div><!-- #main -->
		<?php }
		protected function sidebar(){ get_sidebar(); }
		protected function scripts(){}
		protected function footer(){ get_footer(); }
		protected function end(){}
		
		public function run(){
			$this->init();
			$this->check_ajax();
			$this->prerender();
			$this->header();
			$this->content_start();
			$this->content();
			$this->content_end();
	
			if ($this->use_sidebar()){
				$this->sidebar();
			}
	
			$this->scripts();
			$this->footer();
			$this->end();
		}
	
		public function use_sidebar($boolean = null){
			if (isset($boolean)){
				$this->use_sidebar = $boolean;
				return $this;
			} else {
				return $this->use_sidebar;
			}
		}
	
		public function set_tpl($file){
			$this->tpl = $file;
		}
		/**
		 * Displays messages
		 */
		protected function display_messages(){
			// TODO error code flags
			// This functionality, duplicated in the BS_Form class, and here, could be a part of the BS_Base class,
			// or could be put in a very light intermediate class:  BS_Base->BS_Messages->BS_Form and BS_Base->BS_Messages->BS_Page
			foreach ($this->error_codes as $error_code => $error_code_msg){
				if($_GET[$error_code])
					$this->error_msgs[] = $error_code_msg;
			}
			foreach ($this->success_codes as $success_code => $success_code_msg){
				if($_GET[$success_code])
					$this->success_msgs[] = $success_code_msg;
			}
			foreach ($this->error_msgs as $error_msg){ ?>
				<div class="bs-error"><?=$error_msg?></div>
			<?php }
			
			foreach ($this->success_msgs as $success_msg){ ?>
				<div class="bs-confirmation bs-page-confirmation"><?=$success_msg?></div>
			<?php }
		}
		
		public static function fix_title($title, $sep, $seplocation){
			// The <title> gets messed up when using custom pagination
			// Used in:  BS_CommunityPage and BS_AccountQandAPage
			return "Find the Best Business Software: Reviews & Product Directory";
		}
	
		public function back_to_top(){
	
		}
	
	
	
}