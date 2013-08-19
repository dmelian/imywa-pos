<?php
class pos_ticketPrinter{
	private $ip= "192.168.33.48";
	private $port = "50000";
    private $noImportacion;
    
    private $typography=array();
    protected $blocks = array();
    protected $commands=array();
    
//     protected $lineSize=42;
    protected $lineSize=56;
    protected $defaultSeparation=1;
    
    protected $separation;
    protected $header=null;
    protected $footer=null;

    public function __construct($ip="192.168.33.48",$port="50000"){
		$this->ip = $ip;
		$this->port= $port;
		$this->setConfig();
    }

    private function setConfig(){
		$this->commands["cutPaper"] = "\x1dVA1";
		$this->commands["rot90"] = "\x1B\x561";
		
		// 1 = enable. 0 = disable		
		$this->commands["bold"] = "\x1BE";
		$this->commands["italic"] = "";
		$this->commands["underscore"] = "\x1B-";
		
		$this->commands["alignCenter"] = "\x1B\x611";
		$this->commands["alignLeft"] = "\x1B\x610";
		$this->commands["alignRight"] = "\x1B\x612";
		
		
		$this->commands["codeBarWidth"] = "\x1Dw";
		$this->commands["codeBarHeight"] = "\x1Dh"; //SEguido del tamaño en Hex.
		$this->commands["codeBarPrint"] = "\x1D\x6B\x02"; //COD CTR TypeCODEBAR seguido por el codigo y finalizado por NULL.
		
		$this->commands["standarMode"] = "\x1B\x53";
		
		$this->typography["normal"] = "\x1D!\x00";
		$this->typography["tall"] = "\x1D!\x01";
		$this->typography["heavy"] = "\x1D!\x10";
		$this->typography["huge"] = "\x1D!\x11";
		
		$this->typography["default"] = "\x1B\x4D\x00";
		$this->typography["condensed"] = "\x1B\x4D\x01" ;
    }
    
    
    public function insertBlocK($id,$data,$typography="condensed",$align="alignLeft",$charSize="normal"){
		if (!isset($this->blocks[$id])) $this->blocks[$id] = array("data"=>$data,"order"=>"","align"=>$align,"typography"=>$typography,"charSize"=>$charSize,"priority"=>"");
		else{
			global $_LOG;
			$_LOG->log("Ha intentado introducir dos bloques con el mismo identificador. POS::ticketPrinter::insertBloc");
		}
    }
    
    
    public function charSeparator($char="-"){
		$id = uniqid("SEP_");
		$this->insertBlock($id,array(array("data"=>str_repeat($char,$this->lineSize-1))) );// Identificador aleatorio
		$this->configBlock($id,"data",1,1,"none","none",$this->lineSize-1);
    }
    
    public function configBlock($block,$field,$order,$priority=1,$style="none",$align="none",$maxSize=0,$minSize=1){
		if (isset($this->blocks[$block])){ 
			$this->blocks[$block]["fields"][$field]["priority"] = $priority;
			$this->blocks[$block]["fields"][$field]["order"] = $order;
			$this->blocks[$block]["fields"][$field]["maxSize"] = $maxSize;
			$this->blocks[$block]["fields"][$field]["style"] = $style;
			$this->blocks[$block]["fields"][$field]["align"] = $align;
		}
		else{
			global $_LOG;
			$_LOG->log("Ha intentado configurar un bloque inexistente. POS::ticketPrinter::configBlock");
		}
    }
    
    
    private function getOrderFieldByBlock($id){
		$order = array();
		$orderArray = array();

		foreach($this->blocks[$id]["fields"] as $field => $struc){
			$orderArray[$struc["order"]][] = array("field"=>$field,"maxSize"=>$struc["maxSize"],"priority"=>$struc["priority"],"style"=>$struc["style"],"align"=>$struc["align"]);
		}
		
		global $_LOG;
// 		$_LOG->debug("Valor preordenado",$orderArray);
		
		$numFields = count($orderArray);
		for($ind=0;$ind <= $numFields; $ind++){
			if (isset($orderArray[$ind])){
				$orderItems = $orderArray[$ind];
				foreach($orderItems as $field){
					$order[] = $field;
				}
			}
		}
		
		return $order;
    }
    
    private function alignText($text,$textSize,$maxSize,$type="none"){
    
		switch ($type){
			case "left":
				$text .= str_repeat(" ",$maxSize-$textSize);
				return $text;
			break;
			case "center":
				$left=1;
				for($ind=0;$ind < ($maxSize-$textSize) ; $ind++){
					if ($left == 1){
						$text = " ".$text;
						$left=0;
					}
					else{
						$text .= " ";
						$left=1;
					}
				}
				return $text;
			break;
			case "none":
				return $text;
			break;
			case "right":
				$text = str_repeat(" ",$maxSize-$textSize).$text;
				return $text;
			break;		
		}
		global $_LOG;
		$_LOG->log("ticketPrinter::alignText. Se ha utilizado un tipo de alineado incorrecto. $type");
		return "##Error";
    }
    
    private function emphasized_Text($text,$type="bold"){
		switch ($type){
			case "bold":
				return $this->commands["bold"]."1".$text.$this->commands["bold"]."0";
			break;
			
			case "italic":
			case "none":
				return $text;
			break;
			
			case "underscore":
				return $this->commands["underscore"]."1".$text.$this->commands["underscore"]."0";			
			break;		
		}
		global $_LOG;
		$_LOG->log("ticketPrinter::emphasized_Text. Se ha utilizado un tipo de énfasis incorrecto. $type");
		return "";
    }
    
    private function autoAjustSize($fields){
		global $_LOG;
		$nFields = count($fields);
		$realLineSize = $this->lineSize - ($nFields-1)*$this->defaultSeparation;
		$boxLineSize=0;
		$autoAjust = array();
		
		if ($nFields == 1)$nFields++;
		
		foreach($fields as $id => $field){
			if ($field["maxSize"] != -1)$boxLineSize += $field["maxSize"];
			else{
				$autoAjust[] = $id;
			}
		}
		
		if ($boxLineSize <= $realLineSize){
			$newSize = 0;
			if (count($autoAjust) != 0){
				$newSize = ($realLineSize - $boxLineSize) / count($autoAjust) ;
				$newSize = ceil($newSize);
				foreach($autoAjust as $idField){
					
					$fields[$idField]["maxSize"]= $newSize;
					$boxLineSize += $fields[$idField]["maxSize"];
				}
			}
			$separation = ($this->lineSize - $boxLineSize)/($nFields-1);
			$separation = ceil($separation);
			$_LOG->debug("Valor del tamaño:$newSize, Separacion:$separation, Num elem: ".count($autoAjust),array());
		}
		else{ // Debemos hacer un auto ajuste.  ### De momento no se tiene en cuenta la prioridad
			
		}
		$this->separation = $separation;
		
		return $fields;
		
    }
    
    
    private function formatText($text,$size,$style="",$align="none"){
		$textSize = strlen($text);
		global $_LOG;
		$_LOG->log("tamaño del size:   ".$size);
		
		if ($textSize > $size){
			$text = substr($text,0,$size);
		}
		
		$emphasis = explode(",",$style);
		foreach($emphasis as $item){
			$text = $this->emphasized_Text($text,$item);
		}
		$text = $this->alignText($text,$textSize,$size,$align);
		
		return $text;
    }
    
    private function currentSeparation($numFields){
    
    }
    
	private function textBlock($id){
		$out = "";
		$typography = $this->blocks[$id]["typography"];
		$alignBlock = $this->blocks[$id]["align"];
		$out = $this->typography[$typography].$this->commands["standarMode"].$this->commands[$alignBlock];
		$orderFields = $this->getOrderFieldByBlock($id);
		global $_LOG;
// 		$_LOG->debug("antes de la transformacion",$orderFields);
		
		$orderFields = $this->autoAjustSize($orderFields);
		
// 		$_LOG->debug("Despues de la transformacion",$orderFields);

		$data = $this->blocks[$id]["data"];
// 		$_LOG->debug("Informacion del bloque ",$data);
		foreach($data as $row){
			$sep="";
			$indField=0;
			$numFields  = count($orderFields);
			if ($numFields == 1){
				$out .= $this->formatText($row[$orderFields[0]["field"]],strlen($row[$orderFields[0]["field"]]),$orderFields[0]["style"],$orderFields[0]["align"]);
			}
			else{
				foreach($orderFields as $order){
					$out .= $sep.$this->formatText($row[$order["field"]],$order["maxSize"],$order["style"],$order["align"]);
					$sep = str_repeat(" ",$this->separation);
	// 				$sep = $this->currentSeparation($indField,$numFields);
					$indField++;
				}
			}
			$out.="\n";
		}
		return $out;
    }
    
    public function printTicket(){
		
		$out = $this->buildTicket();
		$filename= $this->randomName(15,"/var/www/print/ticket_");//"ticket.dat";

        if ($file= fopen($filename,'x+')){
            fwrite($file,$out);
            fclose($file);
        }
        $this->printNow($filename);
        
        unlink($filename);
    }
    
    
    
    private function buildTicket(){
		$out = "";
		if (isset($this->header)) $out.= $this->textHeader($this->header);
		
		foreach($this->blocks as $blockID => $empty){
			$out .= $this->textBlock($blockID);
		}
		
		if (isset($this->footer)) $out.= $this->textHeader($this->footer);
		
		$out .= $this->commands["cutPaper"];
		
		return $out;
    }
    
    private function textHeader($block){
		global $_LOG;
		
// 		$_LOG->debug("Entramos en el textHEader!!!!",array());
		$out = "";
		$typography = $block["typography"];
		$alignBlock = $block["align"];
		$out = $this->typography[$typography].$this->commands["standarMode"].$this->commands[$alignBlock];
		

		$data = $block["data"];
// 		$_LOG->debug("Informacion del bloque ",$data);
		
		$out .= $this->formatText($data ,strlen($data),$block["style"],"none");
		$out .="\n";
		return $out;    
    }
    
    public function setHeader($data,$typography="condensed",$align="alignCenter",$emphasis="none",$charSize="normal"){
		$this->header = array("data"=>$data,"style"=>$emphasis,"align"=>$align,"typography"=>$typography,"charSize"=>$charSize);
    }
    
    public function setFooter($data,$typography="condensed",$align="alignCenter",$emphasis="none",$charSize="normal"){
		$this->footer = array("data"=>$data,"style"=>$emphasis,"align"=>$align,"typography"=>$typography,"charSize"=>$charSize);    
    }
    
    
    public function textOnly(){
		$out = $this->buildTicket();
		return $out;
    }
    
    private function printNow($filename){
		chmod($filename, 0666);
		$salida =  shell_exec("cat $filename | tcpconnect -i {$this->ip} {$this->port} 2>&1");
		
		if (!is_null($salida)) return " SE ha producido un error en la emisión. Asegúrece que la impresora está activa\n";
		else return $salida;
    }
    
    
	private function printAlignExample(){
		$text  = $this->commands["standarMode"].$this->commands["alignCenter"]."Centrado \n";
		
		$text .= $this->commands["standarMode"].$this->commands["alignLeft"]."Izquierda \n";
		
		$text .= $this->commands["standarMode"].$this->commands["alignRight"]."Derecha \n";
		
		$text .= $this->commands["cutPaper"];
		return $text;
    }
    
    private function printCodeBarExample(){
    
		$text  = $this->commands["codeBarWidth"]."\x01".$this->commands["codeBarPrint"]."4007817304693\x00 \n W:1, H:Default".$this->commands["cutPaper"];
		
        $text .= $this->commands["codeBarWidth"]."\x02".$this->commands["codeBarPrint"]."4007817304693\x00 \n W:2, H:Default".$this->commands["cutPaper"];
		$text .=$this->commands["codeBarWidth"]."\x03".$this->commands["codeBarHeight"]."w".$this->commands["codeBarPrint"]."4007817304693\x00 \n W:3, H: W".$this->commands["cutPaper"];

		$text .=$this->commands["codeBarWidth"]."\x04".$this->commands["codeBarHeight"]."w".$this->commands["codeBarPrint"]."4007817304693\x00 \n W:4, H: W".$this->commands["cutPaper"];
		$text .=$this->commands["codeBarWidth"]."\x05".$this->commands["codeBarHeight"]."w".$this->commands["codeBarPrint"]."4007817304693\x00 \n W:5, H: W".$this->commands["cutPaper"];
		$text .=$this->commands["codeBarWidth"]."\x06".$this->commands["codeBarHeight"]."w".$this->commands["codeBarPrint"]."4007817304693\x00 \n W:6, H: W".$this->commands["cutPaper"];
        
        
        $text .=$this->commands["codeBarWidth"]."\x04".$this->commands["codeBarHeight"]."\xDC".$this->commands["codeBarPrint"]."4007817304693\x00 \n W:4, H: DC".$this->commands["cutPaper"];
		$text .=$this->commands["codeBarWidth"]."\x05".$this->commands["codeBarHeight"]."w".$this->commands["codeBarPrint"]."4007817304693\x00 \n W:5, H: W".$this->commands["cutPaper"];
		$text .=$this->commands["codeBarWidth"]."\x06".$this->commands["codeBarHeight"]."6".$this->commands["codeBarPrint"]."4007817304693\x00 \n W:6, H: 6".$this->commands["cutPaper"];
        
		return $text;
    }
    
    
    private function printBitMapSimple(){
		$text = "\x1b*\x01\x00\x02";
		//$text .="\xff\x81\x81\x81\x81\x81\x81\x81";
		for($ind=0;$ind<63;$ind++)		$text .="\xff\x81\x81\x81\x81\x81\x81\xff";
		$text .="\xff\xff\xff\xff\xff\xff\xff\xff";
		//$text .="\x81\x81\x81\x81\x81\x81\x81\xff";
// 		$text .= "\x1b*\x00\x08\x00\xff\x81\x81\x81\x81\x81\x81\xff \n";
		
		$text .= $this->commands["cutPaper"];
		return $text;
    }

	private function print_Emphasized(){
		$text  = $this->commands["bold"]."1 BOLD ".$this->commands["bold"]."0";
		$text .= $this->commands["italic"]." Italic ".$this->commands["italic"];
		$text .= $this->commands["underscore"]."1 UnderScore ".$this->commands["underscore"]."0";
		$text .= "FIN".$this->commands["cutPaper"];
		return $text;
    }
    
    public function main(){
        $filename= $this->randomName(15,"ticket_");//"ticket.dat";
        
//         $text = $this->commands["rot90"]." Fijacion de espacios".$this->commands["rot90"]."  comoooooooo ".$this->commands["rot90"]."\n moooooolaaaaa \n estoooooo \n son 3.48  \x1dVA1";
        $text = $this->print_Emphasized();

        if ($file= fopen($filename,'w')){
            fwrite($file,$text);
            fclose($file);
            
            chmod($filename, 0666);
            $salida =  shell_exec("cat $filename | tcpconnect -i {$this->ip} {$this->port} 2>&1");
            
            
            if (!is_null($salida)) echo " SE ha producido un error en la emisión. Asegúrece que la impresora está activa\n";
            else echo $salida;
            
            unlink($filename);
            echo "\nHave a nice day.\n";
        }
        else{
            echo "No ha podido acceder el fichero {$filename}.";
        }   
    } 
    
    private function randomName($size,$prefix=""){
		$items = array();
		for ($ind=0; $ind <= 9; $ind++){
			$items[] = $ind;
		}
		for ($ind='A'; $ind <= 'Z'; $ind++){
			$items[] = $ind;
		}
		
		$out=$prefix;
		for ($ind=0;$ind<$size;$ind++){
			$pos = rand(0,35);
			$out.=$items[$pos];
		}
		return $out;    
    }

}  
?>