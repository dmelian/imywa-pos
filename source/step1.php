<?php
class pos_step1 extends bas_frmx_form{
	private $signo=1;
	private $quantity = 1;
	public function OnLoad(){
		parent::OnLoad();
		$this->toolbar= new bas_frmx_toolbar('close');
		$this->title= 'The estheticien';

		$this->buttonbar = new bas_frmx_buttonbar();

		$groups= new bas_frmx_panelGridQuery("group",array('width'=>1,'height'=>5));
		$groups->query->add("item");
		$groups->query->addcol("item","I","item");
		$groups->query->addcol("itemGroup", "G");
		$groups->query->setFilter('main');
		$groups->classMain="item";
		$groups->setEvent("select_group");
		$groups->setRecord();

		$items= new bas_frmx_panelGridQuery("item",array('width'=>4,'height'=>5));
		$items->query->add("item");
		$items->query->addcol("item","I","item");
		$items->query->addcol("itemGroup", "G");
		$items->query->setFilter('g2');
		$items->classMain="item";
		$items->setEvent("select_item");
		$items->setRecord();

		$quantities= new bas_frmx_panelGrid("items",array('width'=>10,'height'=>1));
		$quantities->setEvent("num_items");
		
// 		addComponent($y=0, $x=0, $field_id,$caption,$event="")
		
		$quantities->addComponent(1,1,"-","-");
		for ($ind=2; $ind<11;$ind++){
			$quantities->addComponent(1,$ind,$ind-1,$ind-1);
		}
		
		$actions= new bas_frmx_panelGrid("action",array('width'=>2,'height'=>3));
		$actions->setEvent("actions_event");
		$actions->addComponent(1,1,"cobro","Cobro");
		$actions->addComponent(1,2,"ticket","Ticket");
		
		$actions->addComponent(2,1,"revisar","Revisar");
		$actions->addComponent(2,2,"delete","Borrar");
		
		$actions->addComponent(3,1,"paid","Pagar");
		$actions->addComponent(3,2,"new","nuevo");
		
		// id,obj,y,x,width,height
		$frame= new bas_frmx_gridFrame("buttons", array("POS"),array('width'=>10,'height'=>8));
		$frame->addComponent("group",$groups	,1,1, 1,7);
		$frame->addComponent("item"	,$items		,1,2, 7,7);
		
		$frame->addComponent("action"	,$actions,5,9, 2,4);
		$frame->addComponent("qty"	,$quantities,8,1, 8,1);
		
// 		$frame->setHeader("Prueba de header");
		
// 		$frame= new bas_frmx_gridFrame("buttons", array("POS"));
// 		$frame->addComponent("group",$groups	,1,1, 1,4);
// 		$frame->addComponent("item"	,$items		,1,2, 3,4);
// 		$frame->addComponent("qty"	,$quantities,5,1, 4,1);
		

		$this->addFrame($frame);
	}
	
	private function make_action($action){
		switch ($action){
			case 'cobro':
				$this->call("sale_bill");
			break;
			
			case 'new':
				$this->call("sale_new");
			break;
			
			case 'ticket':
				$this->call("sale_bill");
			break;
			case 'revisar':
				$this->call("sale_revise");
			break;
			case 'paid':
				$this->call("sale_pay");
			break;
			case 'delete':
				$this->call("sale_delete");
			break;
			default:
				
		
		}
	}
	
	private function call($action){
		$proc = new bas_sql_myprocedure($action,array());
		if ($proc->success){ // Elemento insertado en la venta actual.
			// Actualizamos el display.
					
		}
		else{
			// se ha producido un error en la inserción.
			$msg= new bas_html_messageBox(false, 'Atención.', $proc->errormsg);
			echo $msg->jscommand();
		} 
	}

	public function OnAction($action, $data=""){
		parent::OnAction($action,$data);
		switch($action){
			case 'close': return array('close');
			case 'edit':

				break;
			case 'prevGrid':case 'nextGrid':
				$this->frames[$data["idFrame"]]->OnAction($action,$data);
				$this->OnPaint("jscommand");
				break;
			case 'select_item': 
// 				$msg= new bas_html_messageBox(false, 'Item!!',$data["item"]);
// 				echo $msg->jscommand();
				
				$proc = new bas_sql_myprocedure('insert_item', array( $data['item'],( intval($this->quantity)*intval($this->signo) )  ));
	
				if ($proc->success){ // Elemento insertado en la venta actual.
					// Actualizamos el display.
					if ($proc->errormsg == "0") {
						$this->frames["buttons"]->getObjComponent("item")->setAttrId($data['item'],"subItem","");
					}
					else{
						$this->frames["buttons"]->getObjComponent("item")->setAttrId($data['item'],"subItem",$proc->errormsg);
					}
// 					$msg= new bas_html_messageBox(false, 'Atención.', $proc->errormsg);
// 			echo $msg->jscommand();
// 					$this->frames["buttons"]->getObjComponent("item")->Reload();
				$this->OnPaint("jscommand");	

                }
                else{
					// se ha producido un error en la inserción.
                    $msg= new bas_html_messageBox(false, 'Atención.', $proc->errormsg);
                    echo $msg->jscommand();
                } 
				
				break;
			case 'select_group':
				$this->frames["buttons"]->getObjComponent("item")->query->setfilter($data["item"],"itemGroup");
				$this->frames["buttons"]->getObjComponent("item")->Reload();
				$this->OnPaint("jscommand");				
				break;
			case 'actions_event':
				$this->make_action($data["item"]);
// 				 $msg= new bas_html_messageBox(false, 'Item!!',$data["item"]);
// 				echo $msg->jscommand();
			break;
			case 'num_items':
				if ($data["item"] == "-1") $this->signo = $this->signo * -1;
				else	$this->quantity= $data["item"];
// 				$msg= new bas_html_messageBox(false, 'Item!!',$data["item"]);
// 				echo $msg->jscommand();
			break;
			 
		}
	}
}
