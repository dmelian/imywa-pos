<?php
class pos_groupList extends bas_frmx_form {

	public function OnLoad(){
		parent::OnLoad();
		
		$this->title = 'Grupos';		
	
		$this->toolbar = new bas_frmx_toolbar('filtro,pdf,close');
		
		// ### Definicion del buttonbar
		$this->buttonbar= new bas_frmx_buttonbar();
		$this->buttonbar->addAction('borrar');$this->buttonbar->addAction('nuevo'); $this->buttonbar->addAction('editar');	$this->buttonbar->addAction('salir');
		
		
		$list = new bas_frmx_listframe('lista_articulos',"Grupos Existentes");
		
		$list->query->add('item');
		$list->query->setkey(array('itemGroup','item'));
		
		$list->query->addcol('item','Nombre', 'item',true);
		$list->query->addcol('type','tipo','item',false);

		$list->query->addcol('itemGroup','Grupo','item',false);
		$list->query->addcol('description','Descripción','item',false);
		$list->query->addcol('price','Precio','item',false);

		$list->query->setFilter('group','type');
// 		$list->query->setOrder(array("itemGroup"));
		
		$width=100; $height=1;
		
		$list->addComponent($width, $height,"item");
		$list->addComponent($width, $height,"description");		
// 		$list->addComponent($width, $height,"price");
		
		$list->addComponent($width, $height,"itemGroup");
// 		$list->addComponent($width, $height,"type");

		
		//$list->autoSize(); # El autosize es incompatible con el loadconfig.
		$list->setRecord();
		$this->addFrame($list);
	}
	public function OnRefresh(){
        $this->frames['lista_articulos']->Reload();
    }
	
	
	
	private function OnFilter(){
		$query = new bas_sqlx_querydef();
        
		$query->addcol('item','Nombre', 'item',true);
// 		$query->addcol('type','tipo','item',false);

		$query->addcol('itemGroup','Grupo','item',false);
		$query->addcol('description','Descripción','item',false);
        
        $filters = $this->frames["lista_articulos"]->query->getfilters();
        $query->setfilterRecord($filters);    

		return $query;
    }
	
	
	public function OnAction($action, $data){
		if ($ret = parent::OnAction($action,$data)) return $ret;
		if (isset($data['selected'])){
			$this->frames["lista_articulos"]->setSelected($data['selected']);
		}
		switch ($action){
            case 'salir': case 'cancelar':
                return array("close");
            break;
			case 'nuevo':
                 return array('open','pos_itemCard',"newGroup");
            break;
            case 'editar':
                if (isset($data['selected'])){
                    $aux = $this->frames["lista_articulos"]->getkeySelected();
                    return array('open','pos_itemCard','editGroup',$aux);
                }
                else{
                    $msg= new bas_html_messageBox(false, 'Atención', "Seleccione una tarea");
                    echo $msg->jscommand();
                }
            break;
            
            case 'borrar':
				 if (isset($data['selected'])){
					$data = $this->frames["lista_articulos"]->getkeySelected();
                    $proc = new bas_sql_myprocedure('item_delete', array( $data['item']));
					if ($proc->success){
						$this->frames["lista_articulos"]->Reload(true);
					}
					else{
						$msg= new bas_html_messageBox(false, 'error', $proc->errormsg);
						echo $msg->jscommand();
					} 
                }
                else{
                    $msg= new bas_html_messageBox(false, 'Atención', "Seleccione una tarea");
                    echo $msg->jscommand();
                }
            break;

            case 'filtro':
                $save[] =  array('id'=> "setfilterRecord", 'type'=>'command', 'caption'=>"Aceptar", 'description'=>"guardar");
				$save[] =  array('id'=> "cancel", 'type'=>'command', 'caption'=>"cancelar", 'description'=>"Cancelar");
				
				$query = $this->OnFilter();
                $login= new bas_html_filterBox($query, "Filtros",$save);
				echo $login->jscommand();
            break;
            
            case 'cancel':
                echo '{"command": "void",'. substr(json_encode($this),1);
            break;
            
            case 'setfilterRecord':
                $this->frames['lista_articulos']->query->setfilterRecord($data);   
                $this->frames['lista_articulos']->Reload(true);
            break;
            case 'setFilter';
                $this->frames['lista_articulos']->query->setfilterRecord($data);   
                $this->frames['lista_articulos']->Reload();
            break;
            
            case "lookup":
                $this->buttonbar= new bas_frmx_buttonbar();
                $this->buttonbar->addAction('aceptar'); $this->buttonbar->addAction('cancelar');
            break;
            case "aceptar":
                if (isset($data['selected'])){
                    $aux = $this->frames["lista_articulos"]->getSelected();
                    return array("return","setvalues",array("itemGroup"=>$aux[0]["item"]));
                }
                else{
                    $msg= new bas_html_messageBox(false, 'Atención', "Seleccione una tarea");
                    echo $msg->jscommand();
                }
                
            break;
		}
	}
}
?>
