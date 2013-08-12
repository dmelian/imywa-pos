<?php
class pos_terminalBuildGrid extends pos_terminalHandleTicket{

	protected $curSale;
	protected $currentApp;
	protected $cssClass = array("groups"=>"menuPOS","items"=>"itemPOS","quantity"=>"numPOS","actions"=>"actionPOS");
	
	// #display contiene un objeto Display, que gestiona la construcción del display. # actionDisplay es un objeto Grid, que establece las acciones a mostrar y su colocación.
	protected $actionDisplay;		protected $display;
	
	protected $activeGroup="g2";
	protected $modeView = false;

	
	public function OnLoad(){
		parent::OnLoad();
		$this->createDisplay();
	}
	
	protected function createDisplay(){
		$qry = "select saleNo,saleAmount from sale where state <> 'deleted' and state <>'paid'order by saleNo DESC limit 1";
		$dataset= new bas_sql_myquery($qry);
		
		$this->curSale = $dataset->result['saleNo'];
		
		$qry = "select item,quantity, price from saleLine where saleNo={$this->curSale} order by lineNo DESC limit 3;";
		$ds = new bas_sql_myqrydataset($qry);
		
		$lastItems=array();
		$rec = $ds->reset();
		while ($rec){ 
			$lastItems[] = $rec;
			$rec = $ds->next();			
		}	
		$ds->close();
		
		$this->display = new pos_display($this->curSale,$dataset->result['saleAmount'],$lastItems);
	}
	
	protected function buildItemsGrid(){
		$items= new bas_frmx_panelGridQuery("item",array('width'=>4,'height'=>5));

		$items->mainField="item";
		$items->setEvent("select_item");
		$items->setGnralClass($this->cssClass["items"]);

		$items->setQuery($this->buildQuery(array("itemGroup"=>$this->activeGroup)));
		return $items;
	}
	
	protected function buildGroupsGrid(){
		$groups= new bas_frmx_panelGridQuery("group",array('width'=>1,'height'=>5));
		
		$groups->mainField="item";
		$groups->setEvent("select_group");
		$groups->setGnralClass($this->cssClass["groups"]);
		$groups->setQuery($this->buildQuery(array("itemGroup"=>"main")));
		return $groups;
	}
	
	protected function buildQuantityGrid(){
		$quantities= new bas_frmx_panelGrid("items",array('width'=>10,'height'=>1));
		$quantities->setEvent("num_items");
		$quantities->setGnralClass($this->cssClass["quantity"]);

		$quantities->addComponent(1,1,"-","-");
		for ($ind=2; $ind<11;$ind++){
			$quantities->addComponent(1,$ind,$ind-1,$ind-1);
		}
		$quantities->setAttrId("-","itemClass","empty");
		return $quantities;
	}

	
	protected function buildActionGrid(){
		$location= $this->call('action_display');
		$ind=1;
		$location = explode("::",$location);
		
		$actions= new bas_frmx_panelGrid("action",array('width'=>1,'height'=>3));
		$actions->setEvent("actions_event");
		$actions->setGnralClass($this->cssClass["actions"]);
		foreach($location as $item){
			if ($item != "empty")$actions->addComponent($ind,1,$item,$item);
			$ind++;
		}
		if ($this->modeView)$actions->setAttrId("priceView","itemClass","select_".$this->cssClass["items"]);
// 		$this->frames["buttons"]->addComponent("action"	,$actions,5,9, 2,4);
		$this->actionDisplay=$actions;
	}
	
	
	protected function buildQuery($filter){
		$query = new bas_sqlx_querydef();
		$query->add("item");
		$query->addcol("item","I","item");
		$query->addcol("itemGroup", "G");
		
		$query->setfilterRecord($filter);
		
		return $query;
	}

	public function OnAction($action, $data=""){
		return parent::OnAction($action,$data);
	}
}
