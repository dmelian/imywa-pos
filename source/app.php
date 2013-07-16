<?php
class pos_app extends bas_sysx_app{
    
    
	public function OnPaintDashBoard(){
		$out = "<h4>Administración de tareas (BETA) .</h4>";
		
		$out .= $this->OnPaintDisplay();
		return $out;
	}
	
	private function OnPaintDisplay(){
		
		$out = "";
		$out .= "<div class=\"saleDisplay\" style=\"width: 100%;\">";
		
			$out.= $this->OnPaintCurrentSale();
			$out.= $this->OnPaintLastItems();
			$out.= $this->OnPaintTotalSale();
		
		$out .= "</div>";
		
		$out .= "<div class=\"buttonDisplay\" style=\"border-style:solid;padding-top:21px;height:62%;width: 100%;\">";
		$out .= "</div>";
		return $out;
	}
	
	
	private function OnPaintCurrentSale(){
		$out = "";
		$out .=	"<table border=1 class=\"currentSale\" style=\"width: 100%;\">";
		$out .=		"<tr >";
		$out .=			"<td colspan=\"2\">Última venta realizada</td>";
		$out .=			"<td>Row 1, cell 2</td>";
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

		$out .=		"<tr>";
		$out .=			"<td>Row 1, cell 1</td>";
		$out .=			"<td>Row 1, cell 2</td>";
		$out .=			"<td>Row 1, cell 2</td>";
		$out .=		"</tr>";
		$out .=	"</table>";
		
		return $out;
	}
	
	private function OnPaintTotalSale(){
		$out = "";
		$out .=	"<table border=1 class=\"totalSale\" style=\"width: 100%;\">";
		$out .=		"<tr >";
		$out .=		"<td colspan=\"2\">Total Venta</td>";
		$out .=		"<td>500€</td>";
		$out .=		"</tr>";
		$out .=	"</table>";
		
		return $out;
	}
    
    
}


