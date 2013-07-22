<?php
class pos_display{
	private $lastItems = array();
	private $total=0;
	private $lastSale="";
	
	public function __construct($sale,$total,$lastItems=array()){
		$this->lastSale = $sale;
		$this->total = $total;
		
		foreach($lastItems as $item){
			$this->lastItems[] = array("item"=>$item["item"],"quantity"=>$item["quantity"],"price"=>$item["price"]);
		}
    }
    
    
    public function printDisplay($actions){
    
		$html = $this->displayContent();
		$html = addcslashes($html,'"\\/');
		$out= '{"command":"load","contents":[';
		$out .='{"selector":".saleDisplay","content":"'.$html.'"},';
		
		$html = $this->actionsContent($actions);
		$out .='{"selector":".buttonDisplay","content":"'.$html.'"}]}';
		
		echo $out;
    }
    
    private function displayContent(){
		$out = "";
		$out.= $this->OnPaintCurrentSale();
		$out.= $this->OnPaintLastItems();
		$out.= $this->OnPaintTotalSale();
		return $out;
    }
    
    private function actionsContent($actionsGrid){
		ob_start();
			$actionsGrid->OnPaint();
			$html = ob_get_contents();
		ob_end_clean();
		$html = addcslashes($html,'"\\/');
		return $html;
    }
    
	private function OnPaintCurrentSale(){
		$out = "";
		$out .=	"<table border=1 class=\"currentSale\" style=\"width: 100%;\">";
		$out .=		"<tr >";
		$out .=			"<td colspan=\"2\">Venta actual</td>";
		$out .=			"<td>{$this->lastSale}</td>";
		$out .=		"</tr>";

		$out .=	"</table>";
		
		return $out;
	}
	
	private function OnPaintLastItems(){
		$out = "";
		$out .=	"<table border=1 class=\"lastItems\" style=\"width: 100%;\">";
		$out .=		"<tr>";
		$out .=			"<td>Producto</td>";
		$out .=			"<td>Cantidad</td>";
		$out .=			"<td>Venta</td>";
		$out .=		"</tr>";

		foreach($this->lastItems as $row){
			$out .= $this->OnPaintItem($row["item"],$row["quantity"],$row["price"]);
		}

		$out .=	"</table>";
		
		return $out;
	}
	
	public function addItem($item,$quantity,$price){
		array_unshift($this->lastItems, array("item"=>$item,"quantity"=>$quantity,"price"=>$price));
		array_splice($this->lastItems, count($this->lastItems)-1, 1);
		$this->total += $price;
	}
	
	private function OnPaintItem($item,$quantity,$price){
		$out ="<tr>";
			$out .= "<td>   $item   </td>"."<td>   $quantity   </td>"."<td>   $price   </td>";
		$out .="</tr>";
		return $out;
	}
	
	private function OnPaintTotalSale(){
		$out = "";
		$out .=	"<table border=1 class=\"totalSale\" style=\"width: 100%;\">";
		$out .=		"<tr >";
		$out .=		"<td colspan=\"2\">Total Venta</td>";
		$out .=		"<td>{$this->total}€</td>";
		$out .=		"</tr>";
		$out .=	"</table>";
		
		return $out;
	}
    
    
    
}  
?>