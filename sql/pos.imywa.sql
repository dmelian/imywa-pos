
insert into imywa.sources(source) values ('pos');

insert into imywa.dbs (db) values ('pos');

insert into imywa.apps(app,dbServer,source, theme) values
	('pos','localhost','pos','amedita');

insert into imywa.appDbs(app, db, dbName, main) values
	('pos', 'pos', 'pos', true);

insert into imywa.appRoles(app,role,startClass,defPermissionType) values
	('pos','user','pos_start','allow');

insert into imywa.userRoles(usr,app,role)	values 
	('dmelian', 'pos', 'user')
	, ('root', 'pos', 'user');


