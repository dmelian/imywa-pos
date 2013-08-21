<?php
class pos_app extends bas_sysx_app{
    
    
	public function OnPaintDashBoard(){
		$out = "<h4>Terminal Punto de Venta</h4>";
		
		$out .= $this->OnPaintDisplay();
		return $out;
	}
	
	private function OnPaintDisplay(){
		
		$out = "";
		$out .= "<div class=\"saleDisplay\" style=\"width: 100%;\">";
		$out .= "</div>";
		
		$out .= "<div class=\"buttonDisplay\" style=\"position:relative;left:-10pt;padding-top:21px;height:62%;width: 100%;\">";
		$out .= "</div>";
		return $out;
	}
    
    
}


