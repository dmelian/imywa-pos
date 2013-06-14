<?php
class pos_step1 extends bas_frmx_form{

	public function OnLoad(){
		parent::OnLoad();
		$this->toolbar= new bas_frmx_toolbar('close');
		$this->title= 'The estheticien';

		$this->buttonbar = new bas_frmx_buttonbar();

		$groups= new bas_frmx_panelGridQuery("groups",array('width'=>1,'height'=>5));
		$groups->query->add("item");
		$groups->query->addcol("item","I","item");
		$groups->query->addcol("itemGroup", "G");
		$groups->query->setFilter('main');
		$groups->classMain="item";
		$groups->setEvent("select_group");
		$groups->setRecord();

		$items= new bas_frmx_panelGridQuery("items",array('width'=>4,'height'=>5));
		$items->query->add("item");
		$items->query->addcol("item","I","item");
		$items->query->addcol("itemGroup", "G");
		$items->query->setFilter('g1');
		$items->classMain="item";
		$items->setEvent("select_item");
		$items->setRecord();

		$quantities= new bas_frmx_panelGrid("items",array('width'=>5,'height'=>1));

		// id,obj,y,x,width,height
		$frame= new bas_frmx_gridFrame("buttons", array("POS"));
		$frame->addComponent("group",$groups	,1,1, 1,2);
		$frame->addComponent("item"	,$items		,1,4, 3,2);
		$frame->addComponent("qty"	,$quantities,3,1, 4,1);

		$this->addFrame($frame);
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
		}
	}
}
