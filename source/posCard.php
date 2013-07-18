<?php
class pos_posCard extends bas_frmx_form{ 
	public function OnLoad(){
		parent::OnLoad();
		
		$this->toolbar = new bas_frmx_toolbar('pdf,close');
		
		$this->buttonbar= new bas_frmx_buttonbar();
		$this->buttonbar->addframeAction('aceptar','ficha_pos');
		$this->buttonbar->addAction('cancelar');
		
		$card=new bas_frmx_cardframe('ficha_pos',array("POS"),array("width"=>4,"height"=>4));
		
		$card->query->add('pos');
		$card->query->setkey(array('id'));
		
		$card->query->addcol('id','id', 'pos',true);
		$card->query->addcol('workDay','Día abierto','pos',false);

		$card->query->addcol('pricesIncludeVAT','Impuestos Incluidos','pos',false);
		$card->query->addcol('VATPercentage','% Impuestos','pos',false);
		
		$card->query->addcol('mainItemGroup','Grupo principal','pos',false);
		$card->query->addcol('ticketSerialNo','Nº de serie(ticket)','pos',false);
		
		$card->query->addcol('ticketHeader','Cabecera de ticket','pos',false,"","textarea");
		$card->query->addcol('ticketFooter','Pie de ticket','pos',false,"","textarea");
		
		$card->query->setFilter('1','id');
		
		$card->addComponent('POS', 1, 1, 2, 2, 'workDay');					$card->addComponent('POS', 3, 1, 2, 2, 'pricesIncludeVAT');	
		$card->addComponent('POS', 3, 2, 2, 2, 'VATPercentage');
		$card->addComponent('POS', 1, 3, 2, 2, 'mainItemGroup');			$card->addComponent('POS', 3, 3, 2, 2, 'ticketSerialNo');	
		$card->addComponent('POS', 1, 4, 2, 2, 'ticketHeader');			$card->addComponent('POS', 3, 4, 2, 2, 'ticketFooter');	
		
// 		$card->setAttr('itemGroup','lookup','pos_groupList');	
		
		
		$card->setRecord();
// 		$card->Reload();
		
		$this->addFrame($card);
	}
		
	public function OnAction($action, $data){
		global $ICONFIG;
		
		if ($ret =parent::OnAction($action, $data)) return $ret;
		switch($action){
			case 'cancelar': return array('close');
			case 'aceptar': 
                $proc = new bas_sql_myprocedure('pos_edit', array( $data['workDay'],$data['pricesIncludeVAT'],$data['VATPercentage'],$data['mainItemGroup'],$data['ticketSerialNo'],$data['ticketHeader'],$data['ticketFooter']));
                if ($proc->success){
					return array('close');
                }
                else{
                    $msg= new bas_html_messageBox(false, 'error', $proc->errormsg);
                    echo $msg->jscommand();
                }  
				break;
			case 'setvalues':
				if (isset($data["companyID"]))$data["company"] = $data["companyID"];
				$this->frames["ficha_pos"]->saveData($data);
				break;
			case 'setfilter':
				global $_LOG;
				$_LOG->debug("############## Entramos en el setfilter.JackpotCard",$data);
				$this->frames["ficha_pos"]->query->setfilterRecord($data);
				$this->frames["ficha_pos"]->setRecord();
				break;				
			case 'lookup':
				$this->frames["ficha_pos"]->saveData($data);
				return (array('open',$data["lookup"],'lookup',array()));
			break;
			
			case 'filtro':
				$save[] =  array('id'=> "setfilterRecord", 'type'=>'command', 'caption'=>"guardar", 'description'=>"guardar");
				$save[] =  array('id'=> "cancel", 'type'=>'command', 'caption'=>"cancelar", 'description'=>"Cancelar");
				
				$login= new bas_html_filterBox($this->frames["ficha_pos"]->query, "Filtros",$save);
				echo $login->jscommand();
			break;
			
			case 'ok':case 'cancel':
				echo '{"command": "void"}';//. substr(json_encode($this),1);
			break;
			
			case 'setfilterRecord':
				$this->frames['ficha_pos']->query->setfilterRecord($data);	
				$this->frames['ficha_pos']->Reload(true);
			break;
			
			
		}
	}


}
?>
