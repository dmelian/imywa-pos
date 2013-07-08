<?php
class pos_step1 extends bas_frmx_form{
	private $signo=1;
	private $quantity = 1;
	private $curSale;
	private $currentApp;
	private $lastItems=array();
	private $lastTicket;
	
	public function OnLoad(){
		parent::OnLoad();

		global $_SESSION;
		$this->currentApp = $_SESSION->currentApp;
		
		$this->toolbar= new bas_frmx_toolbar('close');
		$this->title= 'The estheticien';

		$this->buttonbar = new bas_frmx_buttonbar();

		$groups= new bas_frmx_panelGridQuery("group",array('width'=>1,'height'=>5));
// 		$groups->query->add("item");
// 		$groups->query->addcol("item","I","item");
// 		$groups->query->addcol("itemGroup", "G");
// 		$groups->query->setFilter('main');
		
		$groups->mainField="item";
		$groups->setQuery($this->buildQuery(array("itemGroup"=>"main")));
		$groups->setEvent("select_group");
// 		$groups->setRecord();

		$items= new bas_frmx_panelGridQuery("item",array('width'=>4,'height'=>5));
// 		$items->query->add("item");
// 		$items->query->addcol("item","I","item");
// 		$items->query->addcol("itemGroup", "G");
// 		$items->query->setFilter('g2');
		
		
		$items->mainField="item";
		$items->setQuery($this->buildQuery(array("itemGroup"=>"g2")));
		$items->setEvent("select_item");
// 		$items->setRecord();

		$quantities= new bas_frmx_panelGrid("items",array('width'=>10,'height'=>1));
		$quantities->setEvent("num_items");
		
// 		addComponent($y=0, $x=0, $field_id,$caption,$event="")
		
		$quantities->addComponent(1,1,"-","-");
		for ($ind=2; $ind<11;$ind++){
			$quantities->addComponent(1,$ind,$ind-1,$ind-1);
		}
		
		$qry = "select saleNo from sale where state <> 'deleted' and state <>'paid'order by saleNo DESC limit 1";
		$dataset= new bas_sql_myquery($qry);
		
		$this->curSale = $dataset->result['saleNo'];
		
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
		
		$this->addFrame($frame);
		if ($this->curSale != null)$this->lookupSubItems('g2');
	}
	
	
	
	private function buildQuery($filter){
		$query = new bas_sqlx_querydef();
		$query->add("item");
		$query->addcol("item","I","item");
		$query->addcol("itemGroup", "G");
		
		$query->setfilterRecord($filter);
		
		return $query;
	}
	
	private function make_action($action){
		switch ($action){
			case 'cobro':
				$out = $this->call("sale_bill");
				if ($out != "error") {
					$this->lastTicket = $out;
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
					$this->lastTicket = $out;
					$this->printTicket();
				}
			break;
			case 'revisar':
				$out = $this->call("sale_revise");
				if ($out != "error") {
					$this->lastTicket = $out;
					$this->printTicket();
				}
			break;
			case 'paid':
				$this->call("sale_pay",array("cash",500));
			break;
			case 'delete':
				$this->call("sale_delete");
			break;
			default:
				
		
		}
	}
	
	private function OnRefreshDashBoard(){
		global $_LOG;
		$_LOG->log("###  Venta ACtual:: {$this->curSale}");
		
		foreach ($this->lastItems as $item){
			$_LOG->log("###  Último item:: {$item["item"]}.  Cantidad:: {$item["quantity"]}");
		}
		
	
	}
	
	private function call($action, $value=array()){
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
	
	private function pushSubItems($item,$value){
		
		if ($value == "0") {
			$this->frames["buttons"]->getObjComponent("item")->setAttrId($item,"subItem","");
		}
		else{
			$this->frames["buttons"]->getObjComponent("item")->setAttrId($item,"subItem",$value);
		}
	}
	
	
	private function printTicket(){
		$ticket = array();

// 		$qry ="select item,description, concat(quantity,'x',price) as quantity, lineAmount from ticketLine where ticketNo ='{$this->lastTicket}'";
		$qry ="select item, concat(quantity,'x',price) as quantity, lineAmount from ticketLine where ticketNo ='{$this->lastTicket}'";		
		$ds = new bas_sql_myqrydataset($qry);
		$rec = $ds->reset();
		while ($rec){ // obtenemos los periodos por factura
			$ticket[] = $rec;
			$rec = $ds->next();			
		}	
		$ds->close();
		
		
		global $_LOG;
		$_LOG->debug("Valor del ticket",$ticket);
		
		$printer = new pos_ticketPrinter();
		$header[] = array("item"=>"Producto","quantity"=>"Cantidad","lineAmount"=>"Total");
		$printer->insertBlocK("header",$header);
		$printer->configBlock("header","item",1,1,12);
		$printer->configBlock("header","quantity",2,1,8);
		$printer->configBlock("header","lineAmount",3,1,11);
		
		$printer->insertBlocK("ticket",$ticket);
		$printer->configBlock("ticket","item",1,1,12);		
		$printer->configBlock("ticket","lineAmount",3,1,11);
		$printer->configBlock("ticket","quantity",2,1,8);
		
		$printer->printTicket();
		
// 		$text = $printer->textOnly();
// 		$msg= new bas_html_messageBox(false, 'Ticket',$text);
// 		echo $msg->jscommand();
				
		
// 		$printer->configBlock("ticket","item",4);
		
	}

	
	private function lookupSubItems($group){
		$qry ="select saleLine.item as item,sum(quantity) as quantity from saleLine left join item on item.item = saleLine.item where item.itemGroup='$group' and saleLine.saleNo={$this->curSale} group by item";
		$ds = new bas_sql_myqrydataset($qry);
		$rec = $ds->reset();
		while ($rec){ // obtenemos los periodos por factura
			$this->pushSubItems( $rec["item"], $rec["quantity"]);
			$rec = $ds->next();			
		}	
		$ds->close();
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
					$this->pushSubItems($data['item'],$proc->errormsg);
					$this->OnPaint("jscommand");
					$this->lastItems[] = array("item"=>$data['item'],"quantity"=>intval(($this->quantity)*intval($this->signo)) );
                }
                else{
					// se ha producido un error en la inserción.
                    $msg= new bas_html_messageBox(false, 'Atención.', $proc->errormsg);
                    echo $msg->jscommand();
                }               
			
				break;
			case 'select_group':
// 				$this->frames["buttons"]->getObjComponent("item")->query->setfilter($data["item"],"itemGroup");
// 				$this->frames["buttons"]->getObjComponent("item")->Reload();
				$this->frames["buttons"]->getObjComponent("item")->setQuery($this->buildQuery(array("itemGroup"=>$data["item"])));
				$this->lookupSubItems($data["item"]);
				$this->OnPaint("jscommand");
				break;
			case 'actions_event':
				$this->make_action($data["item"]);
// 				 $msg= new bas_html_messageBox(false, 'Item!!',$data["item"]);
// 				echo $msg->jscommand();
			break;
			case 'num_items':
				if ($data["item"] == "-") $this->signo = $this->signo * -1;
				else	$this->quantity= $data["item"];
// 				$msg= new bas_html_messageBox(false, 'Item!!',$data["item"]);
// 				echo $msg->jscommand();
			break;
			 
		}
		$this->OnRefreshDashBoard();
	}
}
