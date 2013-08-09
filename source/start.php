<?php
class pos_start extends bas_frmx_form{
	private $menu;

	public function OnLoad(){
		parent::OnLoad();
		
		$menu= new bas_frmx_menu("main_pos","Main Menu");
		$menu->add('Step 1. The estheticien', 'step1');
		$this->addFrame($menu);
		
		$menu= new bas_frmx_menu("main_items","Menu de artículos");
		$menu->add('Lista de artículos', 'itemList');
		$menu->add('Lista de grupos', 'groupList');
		$this->addFrame($menu);
		
		
		$menu= new bas_frmx_menu("main_config","Menu de configuración");
		$menu->add('Modificación de cabeceras', 'posCard');
		$menu->add('Sustitución de logo', 'groupList');
		$this->addFrame($menu);
		
		
		$qry = "select workday from pos where id=1";
		$dataset= new bas_sql_myquery($qry);
		
		$this->buttonbar= new bas_frmx_buttonbar();
		
		if ($dataset->result["workday"] == null) $this->buttonbar->addAction('openDay','Abrir Día');
		else $this->buttonbar->addAction('closeDay','Cerrar Día');
		
	}
	
	protected function getCustomContent(){ // Funcion virtual del form base. Nos permite añadir contenido al OnPaint(jscommand). Le añadimos el refresco del display.
		$ret = array();
		$ret[".buttonDisplay"] ="";
		$ret[".saleDisplay"] = "";
		return $ret;
	}
	
	public function OnAction($action, $data){
		if ($ret = parent::OnAction($action,$data)) return $ret;
		switch ($action){
			case 'openDay':
				 $proc = new bas_sql_myprocedure('workDay_open', array());
				if ($proc->success){
					$this->buttonbar= new bas_frmx_buttonbar();
					$this->buttonbar->addAction('closeDay','Cerrar Día');
					
					$this->OnPaint("jscommand");
				}
				else{
					$msg= new bas_html_messageBox(false, 'error', $proc->errormsg);
					echo $msg->jscommand();
				} 
			break;
			case 'closeDay':
				 $proc = new bas_sql_myprocedure('workDay_close', array());
				if ($proc->success){
					$this->buttonbar= new bas_frmx_buttonbar();
					$this->buttonbar->addAction('openDay','Abrir Día');
					
					$this->OnPaint("jscommand");
				}
				else{
					$msg= new bas_html_messageBox(false, 'error', $proc->errormsg);
					echo $msg->jscommand();
				} 
			break;
			default:
					return array('open', "pos_$action");
		}
		
	}
	
	public function getBreadCrumbCaption(){ return "Main"; }
	
}


?>


