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
		insert into saleVersion (workDay,saleNo,version,creationTime,reason) values (iworkday,isaleNo,1,now(),'new');
		
		select 0 as error, isaleNo as saleNo;
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
		insert into saleVersion (workDay,saleNo,version,creationTime,reason) values (iworkday,isaleNo,iversion+1,now(),'delete');
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
		update sale set state='billed' where saleNo = isaleNo;
		
		-- Generamos el nuevo ticket. Agrupamos los items y construimos las líneas de este.
		select nextSerialNumber() into inextTicket ;
		select pricesIncludeVAT*VATPercentage into iVATpercentage from pos where id=1;
		insert into ticket(	ticketNo,workDay,creationTime,description,saleAmount,discountAmount,VATAmount,totalAmount) select inextTicket,iworkday,now(),"ticket",saleAmount,discountAmount,(saleAmount-discountAmount)*iVATpercentage,(saleAmount-discountAmount)*(1+iVATpercentage) from sale where saleNo = isaleNo ;
		
		select max(version) into iversion from saleVersion where saleNo=isaleNo;
		insert into saleVersion (workDay,saleNo,version,creationTime,reason,ticketNo) values (iworkday,isaleNo,iversion+1,now(),'bill',inextTicket);

-- 		Creamos las lineas del ticket.		
		select create_ticketLine(inextTicket,isaleNo) into function_return;
		
-- 		Comprobamos que se ha realizado correctamente.
		if function_return = "OK" then
			select 0 as error ,"" as message;
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
	
	select workDay into iworkday from pos where id=1;
	select max(saleNo) into isaleNo from sale where workDay=iworkday and state='billed';
	
	if isaleNo is null then
		select 1 as error, 'La venta no puede revisarse' as message;
	else
		update sale set state='revised' where saleNo = isaleNo;
		select max(version) into iversion from saleVersion where saleNo=isaleNo;
		insert into saleVersion (workDay,saleNo,version,creationTime,reason) values (iworkday,isaleNo,iversion+1,now(),'revise');
		select 0 as error;
	end if;
end$$

drop procedure if exists sale_pay$$
create procedure sale_pay()
begin
	declare isaleNo integer;
	declare iworkday date;
	declare iversion integer;
	declare iticket varchar(20);
	
	select workDay into iworkday from pos where id=1;
	select max(saleNo) into isaleNo from sale where workDay=iworkday and state='billed';
	
	if isaleNo is null then
		select 1 as error, 'La venta no puede cobrarse' as message;
	else
		update sale set state='paid' where saleNo = isaleNo;
		select max(version) into iversion from saleVersion where saleNo=isaleNo;
		select ticketNo into iticket from saleVersion where saleNo=isaleNo and version=iversion;
		insert into saleVersion (workDay,saleNo,version,creationTime,reason,ticketNo) values (iworkday,isaleNo,iversion+1,now(),'paid',iticket);
		select 0 as error;
	end if;
end$$
	
-- select item, sum(quantity),sum(price) from saleLine where saleNo=0 group by item having sum(quantity) > 0;


delimiter ;