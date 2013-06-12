<?php
class pos_start extends bas_frmx_form{
	private $menu;

	public function OnLoad(){
		parent::OnLoad();
		
		$menu= new bas_frmx_menu("main","Main Menu");
		$menu->add('Step 1. The estheticien', 'step1');
		
		$this->addFrame($menu);
		
	}
	
	public function OnAction($action, $data){
		if ($ret = parent::OnAction($action,$data)) return $ret;
		return array('open', "pos_$action");
		
	}
	
	public function getBreadCrumbCaption(){ return "Main"; }
	
}


?>


