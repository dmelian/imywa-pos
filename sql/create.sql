drop database pos;
create database pos;
use pos

select 'Creating tables' as step;
source pos.sql

select 'Loading data' as step;
source pos.data.sql

select 'Configuring imywa' as step;
source pos.imywa.sql

