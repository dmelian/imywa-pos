delimiter $$

-- create procedure productoxcontrato_updatefechafin(
--     in icomercializadora varchar(20),
--     in ipoliza int
--     )
-- begin
--     declare firststep int;
--     declare lastfecha date;
--     declare dsfechainicio date;
--     declare dsfechafin date;
--     declare notfound boolean default false;
--     declare endds boolean;
--     declare ds cursor for
--         select fechainicio, fechafin from productoxcontrato
--         where poliza = ipoliza and comercializadora = icomercializadora
--         order by fechainicio desc;
--     declare continue handler for not found set notfound = true;
-- 
--     open ds;
--     set lastfecha = null;
--     repeat
--         set notfound = false;
--         fetch ds into dsfechainicio, dsfechafin;
--         set endds = notfound;
-- 
--         if not endds then
--             if not lastfecha is null or dsfechafin <= dsfechainicio then
--                 update productoxcontrato set fechafin = lastfecha
--                     where comercializadora = icomercializadora and poliza = ipoliza and fechainicio = dsfechainicio;
--             end if;
--             set lastfecha = dsfechainicio;
--         end if;
-- 
--     until endds end repeat;
--     close ds;
-- end
-- $$

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
    declare iticketSeriaNo varchar(10);
    
    declare iserial varchar(20);
    declare icounter integer;
    
    select ticketSeriaNo into iticketSeriaNo from pos where id=1;
    
    select workDay into iworkday from pos where id =1;
    select prefix,calendarPrefix,padding,periodReset into iprefix,icalendarPrefix,ipadding,iperiodReset from serialNumber where serialNo = iticketSeriaNo;
    select date_format(iworkday,iperiodReset) into ireset;
    
    select counter into icounter from serialLine where serialNo = iticketSeriaNo and period = ireset;
    if icounter is null then
		insert into serialLine (serialNo,period,counter) values (iticketSeriaNo,ireset,1);
		set icounter = 1;
    else
		update serialLine set counter=counter+1 where serialNo = iticketSeriaNo;
    end if;
    
    select concat(iprefix,date_format(iworkday,icalendarPrefix),lpad(icounter,ipadding,'0')) into iserial;
	 
    return iserial;
end
$$



delimiter ;