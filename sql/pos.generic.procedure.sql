delimiter $$

drop procedure if exists workDay_open$$
create procedure workDay_open()
begin
	declare iworkday date;
	select workDay into iworkday from pos;
	
	if iworkday is null then
		set iworkday = curdate();
		insert into workDay (workDay,opening) values (iworkday,now());
		update pos set workDay=iworkday where id=1;	
		
		select 0 as error;
	else
		select 1 as error, 'El día ya se encuentra abierto' as message, 'generic_err01' as idMessage, iworkday as workday;
	end if;
end$$

drop procedure if exists workDay_close$$
create procedure workDay_close()
begin
	declare iworkday date;
	select workDay into iworkday from pos;
	
	if iworkday is null then
		select 1 as error, 'El día no se encuentra abierto' as message, 'generic_err02' as idMessage, iworkday as workday;
	else
		update workDay set closing=now() where workDay=iworkday;
		update pos set workDay=null where id=1;
		select 0 as error;
	end if;
end$$

drop procedure if exists insert_item$$
create procedure insert_item(
-- 	in isaleNo integer,
	in iitem varchar(20),
	in iquantity integer
) begin
	declare iworkday date;
	declare isaleNo integer;
	declare iprice double;
	declare iversion integer;
	declare ilineNo integer;
	declare istate varchar(20);
	declare iquantityAmount integer;
	declare idiscountAmount double;
	
	select workDay into iworkday from pos;
	
	
	if iworkday is null then
		select 1 as error, 'No ha realizado la apertura del día' as message, 'generic_err03' as idMessage;
	else
		select max(saleNo) into isaleNo from sale where workDay=iworkday;
		select state into istate from sale where workDay=iworkday and saleNo = isaleNo and (state='draft' or state='revised');
		
		if istate is null then
			select 1 as error, 'La venta no puede modificarse' as message, 'generic_err04' as idMessage;
		else
			select max(version) into iversion from saleVersion where saleNo = isaleNo and workDay = iworkday;
			select max(lineNo) into ilineNo from saleLine where saleNo = isaleNo and workDay = iworkday and version = iversion;
			if ilineNo is null then
				set ilineNo = 1;
			else
				set ilineNo = ilineNo +1;
			end if;
			select price into iprice from item where item = iitem;

-- 			update sale set saleAmount = saleAmount+(iprice*iquantity) and discountAmount= discountAmount+ *idiscountAmount+(iprice*iquantity)where saleNo=isaleNo;
			update sale set saleAmount = round(saleAmount+(iprice*iquantity),2) where saleNo=isaleNo;
			
			select sum(quantity) into iquantityAmount from saleLine where saleNo = isaleNo and item=iitem group by item;

			if iquantityAmount is null then
				set iquantityAmount=0;
			end if;
			
			if (iquantityAmount< (iquantity*-1)) then
				set iquantityAmount =  (-1*iquantity) - iquantityAmount;
				set iquantity = iquantity + iquantityAmount;
				set iquantityAmount = 0;
			else
				set iquantityAmount =  iquantity + iquantityAmount;
			end if;
			
			insert into saleLine (workDay,saleNo,version,lineNo,creationTime,item,quantity,price,listPrice)
					values (iworkday,isaleNo,iversion,ilineNo,now(),iitem,iquantity,iprice,iprice);
			
			select 0 as error, "$_quantityAmount" as message,iquantityAmount as quantityAmount;
		end if;
	end if;
end$$
-- select item, sum(quantity),sum(price) from saleLine where saleNo=0 group by item having sum(quantity) > 0;


delimiter ;