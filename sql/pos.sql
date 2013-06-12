create table if not exists item(
	item varchar(20) not null primary key,
	type enum ('group','item'),
	itemGroup varchar(20),
	description varchar(30),
	price double,
	foreign key (itemGroup) references item(item) on delete cascade on update cascade
) engine InnoDB, default character set utf8;