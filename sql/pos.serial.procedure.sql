delimiter $$
DROP function IF EXISTS create_ticketLine$$
create function create_ticketLine(
	inumberTicket varchar(20),
	isale int,
	ipositive int
)
    returns varchar(50)
begin
    declare iquantiy int;
    declare iprice double;
    declare iitem varchar(20); 
    declare ireason varchar(10);
    declare iexit varchar(50);
    declare icount int;
    
    declare notfound boolean default false;
    declare endds boolean;
    declare ds cursor for
        select item,sum(quantity) as quantity,price from saleLine
        where saleNo = isale
        group by item having sum(quantity) > 0;
    declare continue handler for not found set notfound = true;

--     select isale ;
--     select inumberTicket ;
    select reason into ireason from saleVersion where saleNo=isale and (reason='bill' or reason='revise' or reason='annull') and ticketNo =inumberTicket order by version DESC limit 1;

    if ireason is not null then
		set iexit = 'OK';
		set icount = 1;
		open ds;
		repeat
			set notfound = false;
			fetch ds into iitem, iquantiy,iprice;
			set endds = notfound;

			if not endds then
				insert into ticketLine (ticketNo,lineNo,item,quantity,price,lineAmount) values (inumberTicket,icount,iitem,iquantiy*ipositive,iprice,ipositive*iquantiy*iprice);
				set icount = icount +1;
			end if;

		until endds end repeat;
		close ds;
		if icount = 1 then
			set iexit = 'error';
		end if;
	else
		set iexit = 'El ticket indicado no corresponde a la venta referenciada.';
	end if;
    return iexit;
end
$$

DROP FUNCTION IF EXISTS nextSerialNumber$$
create function nextSerialNumber()
returns varchar(20)

begin
    declare iprefix varchar(5);
    declare icalendarPrefix varchar(5);
    declare ipadding integer;
    declare iperiodReset varchar(5);
    declare ireset varchar(10);
    declare iworkday date;
    declare iticketSerialNo varchar(10);
    
    declare iserial varchar(20);
    declare icounter integer;
    
    select ticketSerialNo into iticketSerialNo from pos where id=1;
    
    select workDay into iworkday from pos where id =1;
    select prefix,calendarPrefix,padding,periodReset into iprefix,icalendarPrefix,ipadding,iperiodReset from serialNumber where serialNo = iticketSerialNo;
    select date_format(iworkday,iperiodReset) into ireset;
    
    select counter into icounter from serialLine where serialNo = iticketSerialNo and period = ireset;
    if icounter is null then
		insert into serialLine (serialNo,period,counter) values (iticketSerialNo,ireset,1);
		set icounter = 1;
    else
		update serialLine set counter=counter+1 where serialNo = iticketSerialNo;
		set icounter=icounter+1;
    end if;
    
    select concat(iprefix,date_format(iworkday,icalendarPrefix),lpad(icounter,ipadding,'0')) into iserial;
	 
    return iserial;
end
$$



delimiter ;