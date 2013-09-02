<?php
class pos_terminalHandleTicket extends bas_frmx_form{
	protected $curTicket;
	
	protected function printTicket(){
		$ticket = array();

		$qry ="select item, if(quantity <> 1,if (quantity <> -1,concat(quantity,'x',truncate(price,2)),\" \"),\" \") as quantity, truncate(lineAmount,2) as lineAmount from ticketLine where ticketNo ='{$this->curTicket}'";
		$ds = new bas_sql_myqrydataset($qry);
		$rec = $ds->reset();
		while ($rec){ // obtenemos los periodos por factura
			$ticket[] = $rec;
			$rec = $ds->next();			
		}	
		$ds->close();
		
		$printer = new pos_ticketPrinter();
		
		
		$ticketNo[] = array("caption"=>"\nNo Ticket:","ticket"=>$this->curTicket);
		$printer->insertBlocK("ticketNo",$ticketNo);
		$printer->configBlock("ticketNo","caption",1,1,"none","left",12);
		$printer->configBlock("ticketNo","ticket",2,1,"none","left",-1);
		
		$header[] = array("item"=>"Producto","quantity"=>"Cantidad","lineAmount"=>"Total");
		$printer->insertBlocK("header",$header);
		$printer->configBlock("header","item",1,1,"none","center",12);
		$printer->configBlock("header","quantity",2,1,"none","center",8);
		$printer->configBlock("header","lineAmount",3,1,"bold","center",11);
		
		
		$printer->charSeparator();
		$printer->insertBlocK("ticket",$ticket);
		$printer->configBlock("ticket","item",1,1,"none","left",-1);		
		$printer->configBlock("ticket","lineAmount",3,1,"bold","right",11);
		$printer->configBlock("ticket","quantity",2,1,"none","right",8);
		
		$printer->charSeparator();
		
		$qry = "select discountAmount as discount, VATAmount as VAT, totalAmount as total from ticket where ticketNo='{$this->curTicket}'";
		$dataset= new bas_sql_myquery($qry);
		
		
		$total[] = array("empty"=>"","caption"=>"Total","total"=>$dataset->result['total']);
		$descuento[] = array("empty"=>"","caption"=>"Dto.","total"=>$dataset->result['discount']);
		$vat[] = array("empty"=>"","caption"=>"IGIC","total"=>$dataset->result['VAT']);
		
		$printer->insertBlocK("IGIC",$vat);
		$printer->configBlock("IGIC","empty",1,1,"none","left",-1);		
		$printer->configBlock("IGIC","caption",2,1,"bold","right",11);
		$printer->configBlock("IGIC","total",3,1,"none","right",8);
		
		$printer->insertBlocK("dto",$descuento);
		$printer->configBlock("dto","empty",1,1,"none","left",-1);		
		$printer->configBlock("dto","caption",2,1,"bold","right",11);
		$printer->configBlock("dto","total",3,1,"none","right",8);
		
		$printer->insertBlocK("total",$total);
		$printer->configBlock("total","empty",1,1,"none","left",-1);		
		$printer->configBlock("total","caption",2,1,"bold","right",11);
		$printer->configBlock("total","total",3,1,"none","right",8);
		
		
		$qry = "select ticketHeader as header, ticketFooter as footer from pos where id=1";
		$dataset= new bas_sql_myquery($qry);
		
		$printer->testCode();
		
		$printer->setHeader($dataset->result['header'],"default","alignCenter","none","heavy");		
		$printer->setFooter($dataset->result['footer'],"default","alignCenter","none","huge");
		
		$printer->printTicket();
		
		
	}

	
}
