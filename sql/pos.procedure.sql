delimiter $$

drop procedure if exists new_sale$$
create procedure new_sale(
) begin
	declare iworkday date;
	declare isaleNo integer;
	select workDay into iworkday from pos;
	
	if iworkday is null then
		select 1 as error, 'No ha realizado la apertura del día' as message;
	else
		select max(saleNo) into isaleNo from sale;
		set isaleNo = isaleNo +1;
		insert into sale (workDay,saleNo,state) values (curdate(),isaleNo,'draft');
		insert into saleVersion (workDay,saleNo,version,creationTime,reason) values (curdate(),isaleNo,1,curdatetime(),'new');
		
		select 0 as error, isaleNo as saleNo;
	end if;
end$$

drop procedure if exists insert_item$$
create procedure insert_item(
	in isaleNo integer,
	in iitem varchar(20),
	in iquantity integer
) begin
	declare iworkday date;
	declare iprice double;
	declare iversion integer;
	declare ilineNo integer;
	declare istate varchar(20);
	
	select workDay into iworkday from pos;
	
	if iworkday is null then
		select 1 as error, 'No ha realizado la apertura del día' as message;
	else
		select state into istate from sale where workDay=iworkday and saleNo = isaleNo and (state='draft' or state='revised');
		
		if istate is null then
			select 1 as error, 'La venta no puede modificarse' as message;
		else
			select max(version) into iversion from saleVersion where saleNo = isaleNo and workDay = iworkday;
			select max(lineNo) into ilineNo from saleLine where saleNo = isaleNo and workDay = iworkday and version = iversion;
			select price into iprice from item where item = iitem;
			
			insert into saleLine (workDay,saleNo,version,lineNo,creationTime,item,quantity,price,listPrice)
				values (iworkday,isaleNo,iversion,ilineNo,curdatetime(),iitem,iquantity,iprice,iprice);
			
			select 0 as error, ilineNo as lineNo;
		end if;
	end if;
end$$


delimiter ;