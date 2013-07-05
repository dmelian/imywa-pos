delimiter $$

drop procedure if exists sale_new$$
create procedure sale_new()
begin
	declare iworkday date;
	declare isaleNo integer;
	select workDay into iworkday from pos;
	
	if iworkday is null then
		select 1 as error, 'No ha realizado la apertura del día' as message;
	else
		select max(saleNo) into isaleNo from sale;
		if isaleNo is null then
			set isaleNo = 1;
		else
			set isaleNo = isaleNo +1;
		end if;
		insert into sale (workDay,saleNo,state) values (iworkday,isaleNo,'draft');
		insert into saleVersion (workDay,saleNo,version,creationTime,action) values (iworkday,isaleNo,1,now(),'new');
		
		select 0 as error, "$_saleNo" as message,isaleNo as saleNo;
	end if;
end$$

drop procedure if exists sale_delete$$
create procedure sale_delete()
begin
	declare isaleNo integer;
	declare iworkday date;
	declare iversion integer;

	select workDay into iworkday from pos where id=1;
	select max(saleNo) into isaleNo from sale where workDay=iworkday and state='draft';
	
	if isaleNo is null then
		select 1 as error, 'La venta no puede eliminarse' as message;
	else
		update sale set state='deleted' where saleNo = isaleNo;
		select max(version) into iversion from saleVersion where saleNo=isaleNo;
		insert into saleVersion (workDay,saleNo,version,creationTime,action) values (iworkday,isaleNo,iversion+1,now(),'delete');
		select 0 as error;
	end if;
end$$

drop procedure if exists sale_bill$$
create procedure sale_bill()
begin
	declare isaleNo integer;
	declare iworkday date;
	declare iversion integer;
	declare inextTicket varchar(20);
	declare iVATpercentage float;
	declare function_return varchar(50);
	
	select workDay into iworkday from pos where id=1;
	select max(saleNo) into isaleNo from sale where workDay=iworkday and (state='draft' or state='revised');
	
	
	if isaleNo is null then
		select 1 as error, 'La venta no puede facturarse' as message;
	else
		update sale set state='billed' , discountAmount=round(discount*saleAmount,2) where saleNo = isaleNo;
		
		-- Generamos el nuevo ticket. Agrupamos los items y construimos las líneas de este.
		select nextSerialNumber() into inextTicket ;
		select pricesIncludeVAT*VATPercentage into iVATpercentage from pos where id=1;
		insert into ticket(	ticketNo,workDay,creationTime,description,saleAmount,discountAmount,VATAmount,totalAmount) select inextTicket,iworkday,now(),"ticket",saleAmount,discountAmount,round((saleAmount-discountAmount)*iVATpercentage,2),round((saleAmount-discountAmount)*(1+iVATpercentage),2) from sale where saleNo = isaleNo ;
		
		select max(version) into iversion from saleVersion where saleNo=isaleNo;
		insert into saleVersion (workDay,saleNo,version,creationTime,action,ticketNo) values (iworkday,isaleNo,iversion+1,now(),'bill',inextTicket);

-- 		Creamos las lineas del ticket.		
		select create_ticketLine(inextTicket,isaleNo,1) into function_return;
		
-- 		Comprobamos que se ha realizado correctamente.
		if function_return = "OK" then
			select 0 as error ,"$_ticketNo" as message, inextTicket as ticketNo;
		else
			select 1 as error, function_return as message;
		end if;
	end if;
end$$

drop procedure if exists sale_revise$$
create procedure sale_revise()
begin
	declare isaleNo integer;
	declare iworkday date;
	declare iversion integer;
	declare inextTicket varchar(20);
	declare function_return varchar(50);
	declare iVATpercentage float;
	
	select workDay into iworkday from pos where id=1;
	select max(saleNo) into isaleNo from sale where workDay=iworkday and state='billed';
	
	if isaleNo is null then
		select 1 as error, 'La venta no puede revisarse' as message;
	else
		update sale set state='revised' where saleNo = isaleNo;
		select pricesIncludeVAT*VATPercentage into iVATpercentage from pos where id=1;
		
		select nextSerialNumber() into inextTicket ;
		insert into ticket(	ticketNo,workDay,creationTime,description,saleAmount,discountAmount,VATAmount,totalAmount) select inextTicket,iworkday,now(),"ticket inverso.Revision",-1*saleAmount,discountAmount,-1*round((saleAmount-discountAmount)*iVATpercentage,2),-1*round((saleAmount-discountAmount)*(1+iVATpercentage),2) from sale where saleNo = isaleNo ;

		
		select max(version) into iversion from saleVersion where saleNo=isaleNo;
		insert into saleVersion (workDay,saleNo,version,creationTime,action,ticketNo) values (iworkday,isaleNo,iversion+1,now(),'revise',inextTicket);
		
-- 		Creamos las lineas del ticket inverso.
		select create_ticketLine(inextTicket,isaleNo,-1) into function_return;
		
-- 		Comprobamos que se ha realizado correctamente.
		if function_return = "OK" then
			select 0 as error ,"$_ticketNo" as message, inextTicket as ticketNo;
		else
			select 1 as error, function_return as message;
		end if;		
	end if;
end$$

drop procedure if exists sale_pay$$
create procedure sale_pay(
	in itypePayment enum ('cash','creditCard'),
	in ichargedAmount double
)
begin
	declare isaleNo integer;
	declare iworkday date;
	declare iversion integer;
	declare iticket varchar(20);
	declare iowedAmount double;
	declare itotalSale double;
	
	select workDay into iworkday from pos where id=1;
	select max(saleNo) into isaleNo from sale where workDay=iworkday and state='billed';
	
	if isaleNo is null then
		select 1 as error, 'La venta no puede cobrarse' as message;
	else
		select max(version) into iversion from saleVersion where saleNo=isaleNo;
		select ticketNo into iticket from saleVersion where saleNo=isaleNo and version=iversion;
		select totalAmount into itotalSale from ticket where ticketNo=iticket;

		if ichargedAmount is null then
-- 			select saleAmount into ichargedAmount from sale where workDay=iworkday and saleNo=isaleNo;
			set ichargedAmount = itotalSale;
		end if;
		
		update sale set state='paid' , chargedAmount=ichargedAmount , owedAmount= round(ichargedAmount-itotalSale,2) , typePayment= itypePayment where saleNo = isaleNo;

		insert into saleVersion (workDay,saleNo,version,creationTime,action,ticketNo) values (iworkday,isaleNo,iversion+1,now(),'paid',iticket);
		
		select owedAmount into iowedAmount from sale where workDay=iworkday and saleNo=isaleNo;
		
		if iowedAmount < 0 then
			select 1 as error, 'El importe entregado es menor al total' as message;
		else
			select 0 as error,"$_owedAmount" as message,iowedAmount as owedAmount;
		end if;
		
	end if;
	
end$$


drop procedure if exists sale_annull$$
create procedure sale_annull(
	in ireason varchar(15)
	)
begin
	declare isaleNo integer;
	declare iworkday date;
	declare iversion integer;
	declare inextTicket varchar(20);
	declare function_return varchar(50);
	
	select workDay into iworkday from pos where id=1;
	select max(saleNo) into isaleNo from sale where workDay=iworkday and state='paid';
	
	if isaleNo is null then
		select 1 as error, 'La venta no puede anularse' as message;
	else
		select max(version) into iversion from saleVersion where saleNo=isaleNo;
		update sale set state='anulled' where saleNo = isaleNo;
		
		select nextSerialNumber() into inextTicket ;
		insert into ticket(ticketNo,workDay,creationTime,description,saleAmount,discountAmount,VATAmount,totalAmount) select inextTicket,iworkday,now(),"ticket inverso.Anulacion",-1*saleAmount,discountAmount,-1*round((saleAmount-discountAmount)*iVATpercentage,2),-1*round((saleAmount-discountAmount)*(1+iVATpercentage),2) from sale where saleNo = isaleNo ;

		
		select max(version) into iversion from saleVersion where saleNo=isaleNo;
		insert into saleVersion (workDay,saleNo,version,creationTime,action,ticketNo,reason) values (iworkday,isaleNo,iversion+1,now(),'annul',inextTicket,ireason);
		
-- 		Creamos las lineas del ticket inverso.
		select create_ticketLine(inextTicket,isaleNo,-1) into function_return;
		
-- 		Comprobamos que se ha realizado correctamente.
		if function_return = "OK" then
			select 0 as error ,"$_ticketNo" as message, inextTicket as ticketNo;
		else
			select 1 as error, function_return as message;
		end if;
	end if;
	
end$$

-- select item, sum(quantity),sum(price) from saleLine where saleNo=0 group by item having sum(quantity) > 0;


delimiter ;