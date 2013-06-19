create table if not exists serialNumber(
	serialNo varchar(10) not null primary key,
	prefix varchar(5),
	calendarPrefix varchar(5),
	padding integer,
	periodReset varchar (10)
) engine InnoDB, default character set utf8;

create table if not exists serialLine(
	serialNo varchar(10) not null,
	period varchar(10),
	counter integer,
	primary key (serialNo,period),
	foreign key (serialNo) references serialNumber(serialNo) on delete RESTRICT on update cascade
) engine InnoDB, default character set utf8;


create table if not exists item(
	item varchar(20) not null primary key,
	type enum ('group','item'),
	itemGroup varchar(20),
	description varchar(30),
	price double default 0,
	foreign key (itemGroup) references item(item) on delete RESTRICT on update cascade
) engine InnoDB, default character set utf8;


create table if not exists workDay(
	workDay date not null primary key,
	opening datetime,
	closing datetime
) engine InnoDB, default character set utf8;

create table if not exists pos(
	id integer primary key,
	workDay date,
	pricesIncludeVAT boolean default false,
	VATPercentage float,
	mainItemGroup varchar(20),
	ticketSerialNo varchar(10),
	foreign key (workDay) references workDay(workDay) on delete RESTRICT on update cascade
) engine InnoDB, default character set utf8;


create table if not exists ticket(
	ticketNo varchar(20) not null primary key,
	workDay date,
	creationTime datetime,
	description varchar(30),
	saleAmount double default 0,
	discountAmount double default 0,
	VATAmount double default 0,
	totalAmount double default 0,
	foreign key (workDay) references workDay(workDay) on delete RESTRICT on update cascade
) engine InnoDB, default character set utf8;

create table if not exists ticketLine(
	ticketNo varchar(20) not null,
	lineNo integer not null,
	item varchar(20) not null,
	description varchar(30),
	quantity integer,
	price double,
	discountAmount double,
	lineAmount double,
	PRIMARY KEY (ticketNo,lineNo),
	foreign key (ticketNo) references ticket(ticketNo) on delete RESTRICT on update cascade,
	foreign key (item) references item(item) on delete RESTRICT on update RESTRICT
	
) engine InnoDB, default character set utf8;

create table if not exists sale(
	workDay date,
	saleNo integer not null,
	state enum ('draft','deleted','billed','revised','paid'),
	listAmount double default 0,
	discount float default 0,
	discountAmount double default 0,
	saleAmount double default 0,
	chargedAmount double default 0,
	owedAmount double default 0,
	primary key (workDay,saleNo),
	foreign key (workDay) references workDay(workDay) on delete RESTRICT on update cascade
) engine InnoDB, default character set utf8;

create table if not exists saleVersion(
	saleNo integer not null,
	workDay date,
	version integer not null,
	creationTime datetime,
	reason enum('new','delete','bill','revise','paid'),
	ticketNo varchar(20),
	primary key (saleNo,workDay,version),
	foreign key (workDay,saleNo) references sale(workDay,saleNo) on delete RESTRICT on update cascade,
	foreign key (ticketNo) references ticket(ticketNo) on delete RESTRICT on update cascade
) engine InnoDB, default character set utf8;

create table if not exists saleLine(
	saleNo integer,
	workDay date,
	version integer,
	lineNo integer,
	creationTime datetime,
	item varchar(20) not null,
	quantity integer default 0,
	price double default 0,
	listPrice double default 0,
	primary key (saleNo,workDay,version,lineNo),
	foreign key (saleNo,workDay,version) references saleVersion(saleNo,workDay,version) on delete RESTRICT on update cascade,
	foreign key (item) references item(item) on delete RESTRICT on update RESTRICT
) engine InnoDB, default character set utf8;
