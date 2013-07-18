<?php
class pos_itemCard extends bas_frmx_form{ 
	private $type="item";
	public function OnLoad(){
		parent::OnLoad();
		
		$this->toolbar = new bas_frmx_toolbar('pdf,close');
		
		$this->buttonbar= new bas_frmx_buttonbar();
		$this->buttonbar->addframeAction('aceptar','ficha_item');
		$this->buttonbar->addAction('cancelar');
		
		$card=new bas_frmx_cardframe('ficha_item',array(),array("width"=>4,"height"=>3));
		
		$card->query->add('item');
		$card->query->setkey(array('itemGroup','item'));
		
		$card->query->addcol('item','Nombre', 'item',true);
		$card->query->addcol('type','Tipo','item',false);

		$card->query->addcol('itemGroup','Grupo','item',false);
		$card->query->addcol('description','Descripción','item',false);
		$card->query->addcol('price','Precio','item',false);
		
		
		$this->addFrame($card);
	}
	
	
	private function createGroupCard(){
		$this->frames["ficha_item"]->tabs= array("Grupo");
		
		$this->frames["ficha_item"]->query->setFilter('group','type');
		
		$this->frames["ficha_item"]->addComponent('Grupo', 1, 1, 2, 2, 'item');
		$this->frames["ficha_item"]->addComponent('Grupo', 3, 1, 2, 2, 'itemGroup');	
		$this->frames["ficha_item"]->setAttr('itemGroup','lookup','pos_groupList');		
		
		$this->frames["ficha_item"]->addComponent('Grupo', 1, 2, 2, 2, 'description');	
// 		$card->addComponent('Grupo', 3, 2, 2, 2, 'price');
		
		$this->frames["ficha_item"]->setRecord();
		
	}
	
	private function createItemCard(){
		$this->frames["ficha_item"]->tabs= array("Artículo");
		
		$this->frames["ficha_item"]->query->setFilter('item','type');
		
		$this->frames["ficha_item"]->addComponent('Artículo', 1, 1, 2, 2, 'item');
		$this->frames["ficha_item"]->addComponent('Artículo', 3, 1, 2, 2, 'itemGroup');	
		$this->frames["ficha_item"]->setAttr('itemGroup','lookup','pos_groupList');		
		
		$this->frames["ficha_item"]->addComponent('Artículo', 1, 2, 2, 2, 'description');	
		$this->frames["ficha_item"]->addComponent('Artículo', 3, 2, 2, 2, 'price');
		
		$this->frames["ficha_item"]->setRecord();
	}
	
	
	
	public function OnAction($action, $data){
		global $ICONFIG;
		
		if ($ret =parent::OnAction($action, $data)) return $ret;
		switch($action){
			case 'cancelar': return array('close');
			case 'aceptar': 
				$data["type"] = $this->type;
				if(!isset($data["price"]) ) $data["price"] = null;
				if ($this->frames['ficha_item']->GetMode() == "new"){
                     $proc = new bas_sql_myprocedure('item_new', array( $data['item'],$data['type'],$data['itemGroup'],$data['description'],$data['price']));
                }else{
					 $proc = new bas_sql_myprocedure('item_edit', array( $data['item'],$data['type'],$data['itemGroup'],$data['description'],$data['price'],$this->frames['ficha_item']->record->original["item"]));
                }   
                if ($proc->success){
					return array('close');
                }
                else{
                    $msg= new bas_html_messageBox(false, 'error', $proc->errormsg);
                    echo $msg->jscommand();
                }  
				break;
			case 'newItem': 
				$this->type="item";			$this->title = "Nuevo Artículo"; 
				$this->createItemCard();
				$this->frames["ficha_item"]->SetMode("new");
				break;
			case 'newGroup':
				$this->type="group";		$this->title = "Nuevo Grupo"; 
				$this->createGroupCard();
				$this->frames["ficha_item"]->SetMode("new");
				break;
			case 'editItem':
				$this->type="item";
				$this->title = "Editar Artículo"; 

				$this->createItemCard();		$this->frames["ficha_item"]->SetMode("edit");
				$this->frames["ficha_item"]->query->setfilterRecord($data);
                $this->frames["ficha_item"]->setRecord();
				
				break;	
			case 'editGroup':			
				$this->type="group";
				$this->title = "Editar Grupo"; 
				
				$this->createGroupCard();		$this->frames["ficha_item"]->SetMode("edit");
				$this->frames["ficha_item"]->query->setfilterRecord($data);
				$this->frames["ficha_item"]->setRecord();
				
				break;
			case 'setvalues':
				$this->frames["ficha_item"]->saveData($data);
				break;
			case 'setfilter':
				$this->frames["ficha_item"]->query->setfilterRecord($data);
				$this->frames["ficha_item"]->Reload();
				break;				
			case 'lookup':
				$this->frames["ficha_item"]->saveData($data);
				return (array('open',$data["lookup"],'lookup',array()));
			break;
			
			case 'filtro':
				$save[] =  array('id'=> "setfilterRecord", 'type'=>'command', 'caption'=>"guardar", 'description'=>"guardar");
				$save[] =  array('id'=> "cancel", 'type'=>'command', 'caption'=>"cancelar", 'description'=>"Cancelar");
				
				$login= new bas_html_filterBox($this->frames["ficha_item"]->query, "Filtros",$save);
				echo $login->jscommand();
			break;
			
			case 'ok':case 'cancel':
				echo '{"command": "void"}';//. substr(json_encode($this),1);
			break;
		}
	}


}
?>
