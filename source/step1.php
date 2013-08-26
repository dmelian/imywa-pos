<?php
class pos_step1 extends pos_terminalBuildGrid{
	protected $signo=1;
	protected $quantity = 1;
	
	public function OnLoad(){
		parent::OnLoad();
		global $_SESSION;		$this->currentApp = $_SESSION->currentApp;
		
		$this->toolbar= new bas_frmx_toolbar('close');
		$this->title= 'The estheticien';

		$this->buttonbar = new bas_frmx_buttonbar();
		
		$quantities = $this->buildQuantityGrid();
		$groups = $this->buildGroupsGrid();
		$items = $this->buildItemsGrid();
		
		// id,obj,y,x,width,height
		$frame= new bas_frmx_gridFrame("buttons", array("POS"),array('width'=>10,'height'=>8));
		$frame->addComponent("group",$groups	,1,1, 2,7);
		$frame->addComponent("item"	,$items		,1,3, 8,7);
		
		$frame->addComponent("qty"	,$quantities,8,1, 10,1);
		
// 		$frame->setHeader("Prueba de header");
		
		$this->addFrame($frame);		
		$this->buildActionGrid();
		
		if ($this->curSale != null)$this->setSupItems($this->activeGroup);
		$this->setClass("group",$this->activeGroup,"select_".$this->cssClass["groups"]);
		$this->setClass("qty",$this->quantity,"select_".$this->cssClass["quantity"]);
	}
	
	protected function getCustomContent(){ // Funcion virtual del form base. Nos permite añadir contenido al OnPaint(jscommand). Le añadimos el refresco del display.
		return $this->display->getContent($this->actionDisplay);
	}
		
	protected function clearGrid($grid){
		$height = $this->frames["buttons"]->getObjComponent($grid)->grid["height"];
		$width = $this->frames["buttons"]->getObjComponent($grid)->grid["width"];

		for ($row=1; $row <= $height; $row++){
			for ($column=1; $column <= $width; $column++){
				$this->frames["buttons"]->getObjComponent($grid)->setAttrPos($column,$row,"subItem","");
				$this->frames["buttons"]->getObjComponent($grid)->setAttrPos($column,$row,"itemClass","");
			}
		}
	}
	protected function make_action($action,$data=array()){
		global $_LOG;
		$_LOG->debug("Accion realizada!!! $action",$data);
		$ret = "";
		switch ($action){
			case 'bill':
				$out = $this->call("sale_bill");
				if ($out != "error") {
					$this->curTicket = $out;
					$this->printTicket();
				}
			break;
			
			case 'new':
				$out = $this->call("sale_new");
				if ($out != "error") {
					$this->curSale = $out;
					$this->display->setSale($this->curSale);
					$this->display->clearItem();
					$this->clearGrid("item");
				}
			break;
			case 'ticket':
				$out = $this->call("sale_bill");
				if ($out != "error") {
					$this->curTicket = $out;
					$this->printTicket();
				}
			break;
			case 'revise':
				$out = $this->call("sale_revise");
				if ($out != "error") {
					$this->curTicket = $out;
					$this->printTicket();
				}
			break;
			case 'pay':
				$ret = $this->call("sale_pay",array($data["type"],$data["quantity"]));
			break;
			case 'delete':
				$this->call("sale_delete");
			break;
			default:
			break;		
		}
		$this->buildActionGrid();
		return $ret;
// select totalAmount,sale.saleNo,saleVersion.ticketNo,sale.discountAmount,sale.saleAmount,sale.typePayment from saleVersion inner join sale on sale.saleNo=saleVersion.saleNo inner join ticket on ticket.ticketNo = saleVersion.ticketNo where sale.workDay ='2013-07-18' and action='paid' group by sale.workDay,sale.saleNo,action  order by version DESC;
// select sum(totalAmount),sale.saleNo,saleVersion.ticketNo,sale.discountAmount,sale.saleAmount,sale.typePayment from saleVersion inner join sale on sale.saleNo=saleVersion.saleNo inner join ticket on ticket.ticketNo = saleVersion.ticketNo where sale.workDay ='2013-07-18' and action='paid' group by sale.workDay,typePayment order by version DESC;

}
	
	protected function call($action, $value=array()){
		$proc = new bas_sql_myprocedure($action,$value);
		if ($proc->success){ // Elemento insertado en la venta actual.
			// Actualizamos el display.
			return $proc->errormsg;
		}
		else{
			// se ha producido un error en la inserción.
			$msg= new bas_html_messageBox(false, 'Atención.', $proc->errormsg);
			echo $msg->jscommand();
			return "error";
		} 
	}
	
	protected function pushSubItems($item,$value,$userSelected=false){
		if ($value == "0") {
			$this->frames["buttons"]->getObjComponent("item")->setAttrId($item,"subItem","");
			$this->frames["buttons"]->getObjComponent("item")->setAttrId($item,"itemClass",$this->cssClass["items"]);	

		}
		else{
			$this->frames["buttons"]->getObjComponent("item")->setAttrId($item,"subItem",$value);
			$this->frames["buttons"]->getObjComponent("item")->setAttrId($item,"itemClass","select_".$this->cssClass["items"]);	
		}
		if ($userSelected) $this->addItemToDisplay($item);
	}
	
	protected function setSupItems($group){
		$qry ="select saleLine.item as item,sum(quantity) as quantity from saleLine left join item on item.item = saleLine.item where item.itemGroup='$group' and saleLine.saleNo={$this->curSale} group by item";
		$ds = new bas_sql_myqrydataset($qry);
		$rec = $ds->reset();
		while ($rec){ // obtenemos los periodos por factura
			$this->pushSubItems( $rec["item"], $rec["quantity"]);
			$rec = $ds->next();			
		}	
		$ds->close();
	}

	protected function addItemToDisplay($item){
		$qry = "select price from item where item = '$item'";
		$dataset= new bas_sql_myquery($qry);					
		$price = $dataset->result['price'];
		$quantity = intval(($this->quantity)*intval($this->signo));
		
		$this->display->addItem($item,$quantity,$price*$quantity);
	}

	protected function setClass($grid,$id,$class){
		$this->frames["buttons"]->getObjComponent($grid)->setAttrId($id,"itemClass",$class);
	}

	protected function showGetTypePayment(){
		$typeGrid = new bas_sqlx_querydef();
        
        $typeGrid= new bas_frmx_panelGrid("items",array('width'=>2,'height'=>1));
		$typeGrid->setEvent("typePayment");
// 		$typeGrid->setGnralClass($this->cssClass["quantity"]);

		$typeGrid->addComponent(1,1,"creditCard","Tarjeta de crédito");
		$typeGrid->addComponent(1,2,"cash","Efectivo");
        
        
        
		$save[] =  array('id'=> "setfilterRecord", 'type'=>'command', 'caption'=>"Aceptar", 'description'=>"guardar");
		$save[] =  array('id'=> "cancel", 'type'=>'command', 'caption'=>"cancelar", 'description'=>"Cancelar");
        
        $box = new  bas_html_frameBox($typeGrid,"Prueba",array());
        echo $box->jscommand();
	}
	
	protected function showGetCash(){
		global $_LOG;
		$_LOG->debug("Entramos!!",array());
		$query = new bas_sqlx_querydef();
        $query->addcol('quantity','Cantidad', 'temp',true);
        
        $save[] =  array('id'=> "insertCash", 'type'=>'command', 'caption'=>"Aceptar", 'description'=>"guardar");
		
		$cash= new bas_html_filterBox($query, "Cantidad_entregada",$save);
		echo $cash->jscommand();
	}
	
	public function OnAction($action, $data=""){
		global $_LOG;
		$_LOG->debug("Evento enviado!!! $action",$data);
		switch($action){
			case 'close': return array('close');
			case 'prevGrid':case 'nextGrid':
				$this->frames[$data["idFrame"]]->OnAction($action,$data);
				$this->setClass("group",$this->activeGroup,"select_".$this->cssClass["groups"]);

				$this->OnPaint("jscommand");
				break;
			case 'select_item': 
				if ($this->modeView)
				{
					$qry = "select price, description from item where item = '{$data['item']}'";
					$dataset= new bas_sql_myquery($qry);					
					
					$msg= new bas_html_messageBox(false, $dataset->result['description'], $dataset->result['price']."€");
                    echo $msg->jscommand();
                    
					$this->modeView = false; //### No se puede mostrar el dialog y cambiar el color a la vez..
					$this->actionDisplay->setAttrId("priceView","itemClass","");
				}
				else{
					$proc = new bas_sql_myprocedure('insert_item', array( $data['item'],( intval($this->quantity)*intval($this->signo) )  ));
		
					if ($proc->success){ // Elemento insertado en la venta actual.
						// Actualizamos el display.
						$this->pushSubItems($data['item'],$proc->errormsg,1);
						$this->OnPaint("jscommand");
					}
					else{
						// se ha producido un error en la inserción.
						$msg= new bas_html_messageBox(false, 'Atención.', $proc->errormsg);
						echo $msg->jscommand();
					}             
					$this->setClass("qty",$this->quantity,"");
					$this->quantity= 1;
					$this->setClass("qty",$this->quantity,"select_".$this->cssClass["quantity"]);
				}
				break;
			case 'select_group':
				$this->frames["buttons"]->getObjComponent("item")->setQuery($this->buildQuery(array("itemGroup"=>$data["item"])));

				$this->setClass("group",$this->activeGroup,"");
				$this->setClass("group",$data["item"],"select_".$this->cssClass["groups"]);

				$this->activeGroup = $data["item"];

				$this->setSupItems($data["item"]);
				$this->OnPaint("jscommand");
				break;
			case 'actions_event':
				switch ($data["item"]){
					case "pay":
						$this->showGetTypePayment();
						
					break;
					case "priceView":
						if ($this->modeView){
							$this->actionDisplay->setAttrId("priceView","itemClass","");
							$this->modeView=false;
						}
						else {
							$this->actionDisplay->setAttrId("priceView","itemClass","select_".$this->cssClass["items"]);
							$this->modeView=true;							
						}
						$this->OnPaint("jscommand");
						
					break;
					default:
						$this->modeView=false;
						$this->make_action($data["item"]);
						$this->OnPaint("jscommand");
					break;
				}
			
// 				if ($data["item"] == "pay"){
// 					$this->showGetTypePayment();
// 				}
// 				else {
// 					$this->make_action($data["item"]);
// 					$this->OnPaint("jscommand");
// 				}


			break;
			case 'num_items':
				if ($data["item"] == "-") {
					$this->signo = $this->signo * -1;
					if($this->signo < 0)	$this->setClass("qty",'-',"select_".$this->cssClass["items"]);
					else	$this->setClass("qty",'-',"empty");

				}
				else{
					$this->setClass("qty",$this->quantity,"");
					$this->quantity= $data["item"];
					$this->setClass("qty",$this->quantity,"select_".$this->cssClass["quantity"]);
				}
				$this->OnPaint("jscommand");
			break;
			case 'typePayment':
				if ($data["item"] == "creditCard"){
					 if ($this->make_action("pay",array("type"=>$data["item"],"quantity"=>'null')) != "error")$this->OnPaint("jscommand");
				}
				else{
					$this->showGetCash();
// 					$this->make_action("pay",array("type"=>$data["item"],"quantity"=>'200'));
// 					$this->OnPaint("jscommand");
				}
			break;
			case 'insertCash':
				if ($this->make_action("pay",array("type"=>"cash","quantity"=>$data["quantity"])) != "error")$this->OnPaint("jscommand");
// 				else echo '{"command": "void"}';
			break;
			default:
				if ($ret = parent::OnAction($action,$data)) return $ret;
				else echo '{"command": "void"}';
			break;
		}
	}
}
