<?php
class pos_ticketPrinter{
	private $ip= "192.168.33.48";
	private $port = "50000";
    private $noImportacion;
    

    public function __construct($ip="192.168.33.48",$port="50000"){
		$this->ip = $ip;
    }

    public function main(){
        $filename= $this->randomName(15,"ticket_");//"ticket.dat";
        $text = " \x1B\x561Fijacion de espacios  comoooooooo \n moooooolaaaaa \n estoooooo \n son 3.48  \x1dVA1";

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