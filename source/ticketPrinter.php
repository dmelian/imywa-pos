<?php
class pos_ticketPrinter{
	private $ip= "192.168.33.48";
	private $port = "50000";
    private $noImportacion;
    
    private $typography=array();
    protected $blocks = array();
    protected $commands=array();
    
//     protected $lineSize=42;
    protected $lineSize=57;
    protected $defaultSeparation=1;
    
    protected $separation;

    public function __construct($ip="192.168.33.48",$port="50000"){
		$this->ip = $ip;
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
		$this->insertBlock("SEP",array(array("data"=>str_repeat($char,$this->lineSize-1))) );// Identificador aleatorio
		$this->configBlock("SEP","data",1,1,"none",$this->lineSize-1);
    }
    
    public function configBlock($block,$field,$order,$priority=1,$style="none",$maxSize=0,$minSize=1){
		if (isset($this->blocks[$block])){ 
			$this->blocks[$block]["fields"][$field]["priority"] = $priority;
			$this->blocks[$block]["fields"][$field]["order"] = $order;
			$this->blocks[$block]["fields"][$field]["maxSize"] = $maxSize;
			$this->blocks[$block]["fields"][$field]["style"] = $style;
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
			$orderArray[$struc["order"]][] = array("field"=>$field,"maxSize"=>$struc["maxSize"],"priority"=>$struc["priority"],"style"=>$struc["style"]);
		}
		
		global $_LOG;
		$_LOG->debug("Valor preordenado",$orderArray);
		
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
    
    private function alignText($text,$sizeMax,$type="right"){
		
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
		$nFields = count($fields);
		$realLineSize = $this->lineSize - $nFields*$this->defaultSeparation;
		$boxLineSize=0;
		
		if ($nFields == 1)$nFields++;
		
		foreach($fields as $field){
			$boxLineSize += $field["maxSize"];
		}
		
		if ($boxLineSize <= $realLineSize){
			$separation = ($this->lineSize - $boxLineSize)/($nFields-1);
			$separation = ceil($separation);
		}
		else{ // Debemos hacer un auto ajuste.
		
		}
		$this->separation = $separation;
		
		return $fields;
		
    }
    
    
    private function formatText($text,$size,$style=""){
		$textSize = strlen($text);
		global $_LOG;
		$_LOG->log("tamaño del size:   ".$size);
		if ($textSize <= $size){
			for($ind=0;$ind < ($size-$textSize) ; $ind++){
				$text .= " ";
			}
		}
		else{
			$text = substr($text,0,$size);
		}
		
		$emphasis = explode(",",$style);
		foreach($emphasis as $item){
			$text = $this->emphasized_Text($text,$item);
		}
		
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
		$_LOG->debug("antes de la transformacion",$orderFields);
		
		$orderFields = $this->autoAjustSize($orderFields);
		
		$_LOG->debug("Despues de la transformacion",$orderFields);

		$data = $this->blocks[$id]["data"];
		$_LOG->debug("Informacion del bloque ",$data);
		foreach($data as $row){
			$sep="";
			$indField=0;
			$numFields  = count($orderFields);
			if ($numFields == 1){
				$out .= $this->formatText($row[$orderFields[0]["field"]],strlen($row[$orderFields[0]["field"]]),$orderFields[0]["style"]);
			}
			else{
				foreach($orderFields as $order){
					$out .= $sep.$this->formatText($row[$order["field"]],$order["maxSize"],$order["style"]);
					$sep = str_repeat(" ",$this->separation-1);
	// 				$sep = $this->currentSeparation($indField,$numFields);
					$indField++;
				}
			}
			$out.="\n";
		}
		return $out;
    }
    
    public function printTicket(){
		$out = "";
		foreach($this->blocks as $blockID => $empty){
			$out .= $this->textBlock($blockID);
		}
		$out .= $this->commands["cutPaper"];
		
		$filename= $this->randomName(15,"/var/www/print/ticket_");//"ticket.dat";

        if ($file= fopen($filename,'x+')){
            fwrite($file,$out);
            fclose($file);
        }
        
        $this->printNow($filename);
        
        unlink($filename);
    }
    
    
    public function textOnly(){
		$out = "";
		foreach($this->blocks as $blockID => $empty){
			$out .= $this->textBlock($blockID);
		}
		$out .= $this->commands["cutPaper"];
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