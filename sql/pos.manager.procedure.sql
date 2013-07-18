delimiter $$

drop procedure if exists item_new$$
create procedure item_new(
	in iitem varchar(20),
	in itype enum('group','item'),
	in iitemGroup varchar(20),
	in idescription varchar(30),
	in iprice double
)
begin
	insert into item (item,type,itemGroup,description,price) values (iitem,itype,iitemGroup,idescription,iprice);
	select 0 as error;

end$$

drop procedure if exists item_delete$$
create procedure item_delete(
	in iitem varchar(20)
)
begin
	delete from item where item = iitem;
	select 0 as error;
end$$

drop procedure if exists item_edit$$
create procedure item_edit(
	in iNEWitem varchar(20),
	in itype enum('group','item'),
	in iitemGroup varchar(20),
	in idescription varchar(30),
	in iprice double,
	in iOLDitem varchar(20)
)
begin
	update item set item = iNEWitem,type=itype,itemGroup=iitemGroup,description=idescription,price=iprice where item = iOLDitem;
	select 0 as error;
end$$

drop procedure if exists pos_edit$$
create procedure pos_edit(
	in iworkDay date,
	in ipricesIncludeVAT boolean ,
	in iVATPercentage float,
	in imainItemGroup varchar(20),
	in iticketSerialNo varchar(10),
	in iticketHeader varchar(150),
	in iticketFooter varchar(150)
)
begin
	update pos set workDay = iworkDay,pricesIncludeVAT=ipricesIncludeVAT,VATPercentage=iVATPercentage,
	              mainItemGroup=imainItemGroup,ticketSerialNo=iticketSerialNo,ticketFooter=iticketFooter,ticketHeader=iticketHeader where id = 1;
	select 0 as error;
end$$

delimiter ;