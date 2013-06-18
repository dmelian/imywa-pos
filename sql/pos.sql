create table if not exists item(
	item varchar(20) not null primary key,
	type enum ('group','item'),
	itemGroup varchar(20),
	description varchar(30),
	price double,
	foreign key (itemGroup) references item(item) on delete RESTRICT on update cascade
) engine InnoDB, default character set utf8;


create table if not exists workDay(
	workDay date not null primary key,
	opening datetime,
	closing datetime
) engine InnoDB, default character set utf8;


create table if not exists ticket(
	ticketNo varchar(20) not null primary key,
	workDay date,
	creationTime datetime,
	description varchar(30),
	saleAmount double,
	discountAmount double,
	VATAmount double,
	totalAmount double,
	foreign key (workDay) references workDay(workDay) on delete RESTRICT on update cascade
) engine InnoDB, default character set utf8;

create table if not exists ticketLine(
	ticketNo varchar(20) not null,
	lineNo integer not null,
	item varchar(20) not null,
	description varchar(30),
	quantity float,
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
	listAmount double,
	discount float,
	discountAmount double,
	saleAmount double,
	chargedAmount double,
	owedAmount double,
	primary key (workDay,saleNo),
	foreign key (workDay) references workDay(workDay) on delete RESTRICT on update cascade
) engine InnoDB, default character set utf8;

create table if not exists saleVersion(
	saleNo integer not null,
	workDay date,
	version integer not null,
	creationTime datetime,
	reason enum('new','delete','bill','revise'),
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
	quantity float,
	price double,
	listPrice double,
	primary key (saleNo,workDay,version,lineNo),
	foreign key (saleNo,workDay,version) references saleVersion(saleNo,workDay,version) on delete RESTRICT on update cascade,
	foreign key (item) references item(item) on delete RESTRICT on update RESTRICT
) engine InnoDB, default character set utf8;
