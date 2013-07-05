<?php
class pos_ticketPrinter{
	private $ip= "192.168.33.48";
	private $port = "50000";
    private $noImportacion;
    
    protected $blocks = array();
    protected $commands=array();
    
    protected $lineSize=42;
    

    public function __construct($ip="192.168.33.48",$port="50000"){
		$this->ip = $ip;
		$this->setCommands();
    }

    private function setCommands(){
		$this->commands["cutPaper"] = "\x1dVA1";
		$this->commands["rot90"] = "\x1B\x561";
		
		$this->commands["bold"] = "";
		$this->commands["italic"] = "";
		$this->commands["underscore"] = "";
		
		
    }
    
    
    public function insertBlocK($id,$data){
		if (!isset($this->blocks[$id])) $this->blocks[$id] = array("data"=>$data,"order"=>"","priority"=>"");
		else{
			global $_LOG;
			$_LOG->log("Ha intentado introducir dos bloques con el mismo identificador. POS::ticketPrinter::insertBloc");
		}
    }
    
    public function configBlock($block,$field,$order,$priority=1,$minSize=1){
		if (isset($this->blocks[$block])){ 
			$this->blocks[$block]["fields"][$field]["priority"] = $priority;
			$this->blocks[$block]["fields"][$field]["order"] = $order;
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
			$orderArray[$struc["order"]][] = $field;
		}
		
		foreach($orderArray as $orderItems){
			foreach($orderItems as $field){
				$order[] = $field;
			}
		}
		
		return $order;
    }
    
    private function alignText($text,$sizeMax,$type="right"){
		
    }
    
	private function textBlock($id){
		$out = "";
		$orderFields = $this->getOrderFieldByBlock($id);
		$data = $this->blocks[$id]["data"];
		foreach($data as $row){
			$sep="";
			foreach($orderFields as $order){
				$out .= $sep.$row[$order];
				$sep = " ";
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
    
    
    private function printNow($filename){
		chmod($filename, 0666);
		$salida =  shell_exec("cat $filename | tcpconnect -i {$this->ip} {$this->port} 2>&1");
		
		if (!is_null($salida)) return " SE ha producido un error en la emisión. Asegúrece que la impresora está activa\n";
		else return $salida;
    
    }
    

    
    
    public function main(){
        $filename= $this->randomName(15,"ticket_");//"ticket.dat";
        $text = $this->commands["rot90"]." Fijacion de espacios".$this->commands["rot90"]."  comoooooooo ".$this->commands["rot90"]."\n moooooolaaaaa \n estoooooo \n son 3.48  \x1dVA1";

        // 		$text="\x1c\x7054 \x0\x1 211";

        if ($file= fopen($filename,'w')){
//             $this->importFile($file);
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