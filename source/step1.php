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
		$frame->addComponent("group",$groups	,1,1, 1,7);
		$frame->addComponent("item"	,$items		,1,2, 9,7);
		
		$frame->addComponent("qty"	,$quantities,8,1, 10,1);
		
// 		$frame->setHeader("Prueba de header");
		
		$this->addFrame($frame);		
		$this->buildActionGrid();
		
		if ($this->curSale != null)$this->setSupItems($this->activeGroup);
		$this->frames["buttons"]->getObjComponent("group")->setAttrId($this->activeGroup,"itemClass","select_itemPOS");
	}
	
	protected function getCustomContent(){ // Funcion virtual del form base. Nos permite añadir contenido al OnPaint(jscommand). Le añadimos el refresco del display.
		return $this->display->getContent($this->actionDisplay);
	}
		
	protected function make_action($action){
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
				$this->call("sale_pay",array("cash",500));
			break;
			case 'delete':
				$this->call("sale_delete");
			break;
			default:
				
		
		}
		$this->buildActionGrid();
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
		}
		else{
			$this->frames["buttons"]->getObjComponent("item")->setAttrId($item,"subItem",$value);
			$this->frames["buttons"]->getObjComponent("item")->setAttrId($item,"itemClass","select_itemPOS");	
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

	public function OnAction($action, $data=""){
		global $_LOG;
		$_LOG->debug("Evento enviado!!! $action",$data);
		switch($action){
			case 'close': return array('close');
			case 'edit':

				break;
			case 'prevGrid':case 'nextGrid':
				$this->frames[$data["idFrame"]]->OnAction($action,$data);
				$this->frames["buttons"]->getObjComponent("group")->setAttrId($this->activeGroup,"itemClass","select_itemPOS");

				$this->OnPaint("jscommand");
				break;
			case 'select_item': 
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
                
                $this->quantity = $this->signo;
			
				break;
			case 'select_group':
				$this->frames["buttons"]->getObjComponent("item")->setQuery($this->buildQuery(array("itemGroup"=>$data["item"])));

				$this->frames["buttons"]->getObjComponent("group")->setAttrId($this->activeGroup,"itemClass","");
				$this->frames["buttons"]->getObjComponent("group")->setAttrId($data["item"],"itemClass","select_itemPOS");
				$this->activeGroup = $data["item"];

				$this->setSupItems($data["item"]);
				$this->OnPaint("jscommand");
				break;
			case 'actions_event':
				$this->make_action($data["item"]);
// 				$this->sendButton();
				$this->OnPaint("jscommand");

			break;
			case 'num_items':
				if ($data["item"] == "-") $this->signo = $this->signo * -1;
				else	$this->quantity= $data["item"];
			break;
			default:
				if ($ret = parent::OnAction($action,$data)) return $ret;
			break;
		}
// 		$this->OnRefreshDashBoard();
	}
}
