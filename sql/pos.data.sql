
insert into serialNumber(serialNo,prefix,calendarPrefix,padding,periodReset) values (1,'GLM','%y%w',6,'%Y');
insert into pos (id,VATPercentage,ticketSerialNo) values (1,0.07,1);

set @myItemGroup='main';	
insert into item(item, type, itemGroup, description, price) values
	('main' ,'group', null, 'Main group', null)
	,('g1' ,'group', @myItemGroup, 'Group 1', null)
	, ('g2' ,'group', @myItemGroup, 'Group 2', null)
	, ('g3' ,'group', @myItemGroup, 'Group 3', null)
	, ('g4' ,'group', @myItemGroup, 'Group 4', null)
	, ('g5' ,'group', @myItemGroup, 'Group 5', null)
	, ('g6' ,'group', @myItemGroup, 'Group 6', null)
	, ('g7' ,'group', @myItemGroup, 'Group 7', null)
;

set @myItemGroup='g1';
set @myGroupDescription='Group 1';
insert into item(item, type, itemGroup, description, price) values
	(concat('i1_',@myItemGroup),'item', @myItemGroup, concat('Item 1, ',@myGroupDescription), 1)
	,(concat('i2_',@myItemGroup),'item', @myItemGroup, concat('Item 2, ',@myGroupDescription), 1)
	,(concat('i3_',@myItemGroup),'item', @myItemGroup, concat('Item 3, ',@myGroupDescription), 1)
	,(concat('i4_',@myItemGroup),'item', @myItemGroup, concat('Item 4, ',@myGroupDescription), 1)
	,(concat('i5_',@myItemGroup),'item', @myItemGroup, concat('Item 5, ',@myGroupDescription), 1)
	,(concat('i6_',@myItemGroup),'item', @myItemGroup, concat('Item 6, ',@myGroupDescription), 1)
	,(concat('i7_',@myItemGroup),'item', @myItemGroup, concat('Item 7, ',@myGroupDescription), 1)
	,(concat('i8_',@myItemGroup),'item', @myItemGroup, concat('Item 8, ',@myGroupDescription), 1)
;

set @myItemGroup='g2';
set @myGroupDescription='Group 2';
insert into item(item, type, itemGroup, description, price) values
	(concat('i1_',@myItemGroup),'item', @myItemGroup, concat('Item 1, ',@myGroupDescription), 2)
	,(concat('i2_',@myItemGroup),'item', @myItemGroup, concat('Item 2, ',@myGroupDescription), 2)
	,(concat('i3_',@myItemGroup),'item', @myItemGroup, concat('Item 3, ',@myGroupDescription), 2)
	,(concat('i4_',@myItemGroup),'item', @myItemGroup, concat('Item 4, ',@myGroupDescription), 2)
	,(concat('i5_',@myItemGroup),'item', @myItemGroup, concat('Item 5, ',@myGroupDescription), 2)
	,(concat('i6_',@myItemGroup),'item', @myItemGroup, concat('Item 6, ',@myGroupDescription), 2)
	,(concat('i7_',@myItemGroup),'item', @myItemGroup, concat('Item 7, ',@myGroupDescription), 2)
	,(concat('i8_',@myItemGroup),'item', @myItemGroup, concat('Item 8, ',@myGroupDescription), 2)
;


set @myItemGroup='g3';
set @myGroupDescription='Group 3';
insert into item(item, type, itemGroup, description, price) values
	(concat('i1_',@myItemGroup),'item', @myItemGroup, concat('Item 1, ',@myGroupDescription), 3)
	,(concat('i2_',@myItemGroup),'item', @myItemGroup, concat('Item 2, ',@myGroupDescription), 3)
	,(concat('i3_',@myItemGroup),'item', @myItemGroup, concat('Item 3, ',@myGroupDescription), 3)
	,(concat('i4_',@myItemGroup),'item', @myItemGroup, concat('Item 4, ',@myGroupDescription), 3)
	,(concat('i5_',@myItemGroup),'item', @myItemGroup, concat('Item 5, ',@myGroupDescription), 3)
	,(concat('i6_',@myItemGroup),'item', @myItemGroup, concat('Item 6, ',@myGroupDescription), 3)
	,(concat('i7_',@myItemGroup),'item', @myItemGroup, concat('Item 7, ',@myGroupDescription), 3)
	,(concat('i8_',@myItemGroup),'item', @myItemGroup, concat('Item 8, ',@myGroupDescription), 3)
;

set @myItemGroup='g4';
set @myGroupDescription='Group 4';
insert into item(item, type, itemGroup, description, price) values
	(concat('i1_',@myItemGroup),'item', @myItemGroup, concat('Item 1, ',@myGroupDescription), 4)
	,(concat('i2_',@myItemGroup),'item', @myItemGroup, concat('Item 2, ',@myGroupDescription), 4)
	,(concat('i3_',@myItemGroup),'item', @myItemGroup, concat('Item 3, ',@myGroupDescription), 4)
	,(concat('i4_',@myItemGroup),'item', @myItemGroup, concat('Item 4, ',@myGroupDescription), 4)
	,(concat('i5_',@myItemGroup),'item', @myItemGroup, concat('Item 5, ',@myGroupDescription), 4)
	,(concat('i6_',@myItemGroup),'item', @myItemGroup, concat('Item 6, ',@myGroupDescription), 4)
	,(concat('i7_',@myItemGroup),'item', @myItemGroup, concat('Item 7, ',@myGroupDescription), 4)
	,(concat('i8_',@myItemGroup),'item', @myItemGroup, concat('Item 8, ',@myGroupDescription), 4)
;

set @myItemGroup='g5';
set @myGroupDescription='Group 5';
insert into item(item, type, itemGroup, description, price) values
	(concat('i1_',@myItemGroup),'item', @myItemGroup, concat('Item 1, ',@myGroupDescription), 5)
	,(concat('i2_',@myItemGroup),'item', @myItemGroup, concat('Item 2, ',@myGroupDescription), 5)
	,(concat('i3_',@myItemGroup),'item', @myItemGroup, concat('Item 3, ',@myGroupDescription), 5)
	,(concat('i4_',@myItemGroup),'item', @myItemGroup, concat('Item 4, ',@myGroupDescription), 5)
	,(concat('i5_',@myItemGroup),'item', @myItemGroup, concat('Item 5, ',@myGroupDescription), 5)
	,(concat('i6_',@myItemGroup),'item', @myItemGroup, concat('Item 6, ',@myGroupDescription), 5)
	,(concat('i7_',@myItemGroup),'item', @myItemGroup, concat('Item 7, ',@myGroupDescription), 5)
	,(concat('i8_',@myItemGroup),'item', @myItemGroup, concat('Item 8, ',@myGroupDescription), 5)
;

set @myItemGroup='g6';
set @myGroupDescription='Group 6';
insert into item(item, type, itemGroup, description, price) values
	(concat('i1_',@myItemGroup),'item', @myItemGroup, concat('Item 1, ',@myGroupDescription), 6)
	,(concat('i2_',@myItemGroup),'item', @myItemGroup, concat('Item 2, ',@myGroupDescription), 6)
	,(concat('i3_',@myItemGroup),'item', @myItemGroup, concat('Item 3, ',@myGroupDescription), 6)
	,(concat('i4_',@myItemGroup),'item', @myItemGroup, concat('Item 4, ',@myGroupDescription), 6)
	,(concat('i5_',@myItemGroup),'item', @myItemGroup, concat('Item 5, ',@myGroupDescription), 6)
	,(concat('i6_',@myItemGroup),'item', @myItemGroup, concat('Item 6, ',@myGroupDescription), 6)
	,(concat('i7_',@myItemGroup),'item', @myItemGroup, concat('Item 7, ',@myGroupDescription), 6)
	,(concat('i8_',@myItemGroup),'item', @myItemGroup, concat('Item 8, ',@myGroupDescription), 6)
;

set @myItemGroup='g7';
set @myGroupDescription='Group 7';
insert into item(item, type, itemGroup, description, price) values
	(concat('i1_',@myItemGroup),'item', @myItemGroup, concat('Item 1, ',@myGroupDescription), 7)
	,(concat('i2_',@myItemGroup),'item', @myItemGroup, concat('Item 2, ',@myGroupDescription), 7)
	,(concat('i3_',@myItemGroup),'item', @myItemGroup, concat('Item 3, ',@myGroupDescription), 7)
	,(concat('i4_',@myItemGroup),'item', @myItemGroup, concat('Item 4, ',@myGroupDescription), 7)
	,(concat('i5_',@myItemGroup),'item', @myItemGroup, concat('Item 5, ',@myGroupDescription), 7)
	,(concat('i6_',@myItemGroup),'item', @myItemGroup, concat('Item 6, ',@myGroupDescription), 7)
	,(concat('i7_',@myItemGroup),'item', @myItemGroup, concat('Item 7, ',@myGroupDescription), 7)
	,(concat('i8_',@myItemGroup),'item', @myItemGroup, concat('Item 8, ',@myGroupDescription), 7)
;
