create table people(
	person_id serial primary key not null,
	person_firstname varchar(30) not null,
	person_secondname varchar(30),
	person_surname varchar(30) not null,
	person_secondsurname varchar(30),
	person_sex varchar(30) not null,
	person_email varchar(80) not null unique,
	person_phone varchar(15) not null,
	person_ext varchar(5),
	person_birthdate date,
	person_occupation varchar(120),
	person_marital_status varchar(20),
	person_active int check (person_active = 1 or person_active = 0) not null default 1,
	person_delete int not null default 0,
	person_created_by int not null,
	person_created_at timestamp with time zone not null default current_timestamp,
	person_modified_by int,
	person_modified_at timestamp
);

create table users(
	user_id serial primary key not null,
	company_id int not null,
	user_username varchar(80) not null unique,
	user_password varchar(256) not null,
	person_id int references people(person_id) not null,
	user_recover int,	-- indica si el usuario desea restablecer contraseña
	user_recovertoken varchar(30), -- token enviado por correo para identificar que es el usuario
	user_recoverdate timestamp with time zone, -- fecha de solicitud de la recuperacion. si pasa un dia ya no es valido.
	user_active int not null,
	user_delete int not null default 0,
	user_created_by int not null,
	user_created_at timestamp with time zone default current_timestamp,
	user_modified_by int,
	user_modified_at timestamp with time zone
);

create table profiles(
	profile_id serial primary key not null,
	profile_name varchar(30) not null,
	profile_active int check (profile_active = 1 or profile_active = 0),
	profile_delete int not null default 0,
	profile_created_by int not null references users(user_id),
	profile_created_at timestamp with time zone not null default current_timestamp,
	profile_modified_by int references users(user_id),
	profile_modifiedat timestamp with time zone
);

create table users_profiles(
	userprofile_id serial primary key not null,
	user_id int references users(user_id) not null,
	profile_id int references profiles(profile_id) not null,
	userprofile_active int default 1 check (userprofile_active = 1 or userprofile_active = 0),
	userprofile_delete int not null default 0,
	userprofile_created_by int not null references users(user_id),
	userprofile_created_at timestamp with time zone default current_timestamp,
	userprofile_modified_by int references users(user_id),
	userprofile_modified_at timestamp with time zone
);

create table menus(
	menu_id serial primary key not null,
	menu_name varchar(30) not null,
	menu_parent int references menus(menu_id),
	menu_type varchar(30) not null,
	menu_action varchar(200),
	menu_order int not null,
	menu_icon varchar(30),
	menu_icon_library varchar(30),
	menu_active int default 1 check (menu_active = 1 or menu_active = 0),
	menu_delete int not null default 0,
	menu_created_by int not null references users(user_id),
	menu_created_at timestamp with time zone not null default current_timestamp,
	menu_modified_by int references users(user_id),
	menu_modified_at timestamp with time zone
);

create table profiles_menu(
	profilemenu_id serial primary key not null,
	profile_id int references profiles(profile_id) not null,
	menu_id int references menus(menu_id) not null,
	profilemenu_permissions varchar(60) not null,
	profilemenu_active int default 1 check (profilemenu_active = 1 or profilemenu_active = 0),
	profilemenu_delete int not null default 0,
	profilemenu_created_by int not null references users(user_id),
	profilemenu_created_at timestamp with time zone default current_timestamp,
	profilemenu_modified_by int references users(user_id),
	profilemenu_modified_at timestamp with time zone
);

create table companies(
	company_id serial not null primary key,
	company_identification_type varchar(30),
	company_identification varchar(30),
	company_name varchar(120) not null unique,
	company_dba varchar(60),
	company_logo text,
	company_website varchar(120),
	company_phone varchar(15) not null,
	company_ext varchar(10),
	company_active int not null default 1 check (company_active = 1 or company_active = 0),
	company_delete int not null default 0,
	company_created_by int not null references users(user_id),
	company_created_at timestamp with time zone not null default current_timestamp,
	company_modified_by int references users(user_id),
	company_modified_at timestamp with time zone
);

create table locations(
	location_id serial not null primary key,
	location_name varchar(80) not null,
	company_id int not null references companies(company_id),
	location_manager int not null references users(user_id),
	country_code varchar(2) not null,
	state_code varchar(8) not null,
	city_code int not null,
	location_address varchar(120),
	location_zipcode varchar(8),
	password_default varchar(255),
	location_active int not null default 1 check(location_active = 1 or location_active = 0),
	location_delete int not null default 0,
	location_created_by int not null references users(user_id),
	location_created_at timestamp with time zone not null default current_timestamp,
	location_modified_by int references users(user_id),
	location_modified_at timestamp with time zone
);

create table users_locations(
	userlocation_id serial not null,
	location_id int not null references locations(location_id),
	user_id int not null references users(user_id),
	userlocation_position varchar(30),
	userlocation_active int not null default 1 check(userlocation_active = 1 or userlocation_active = 0),
	userlocation_delete int not null default 0,
	userlocation_created_by int not null references users(user_id),
	userlocation_created_at timestamp with time zone not null default current_timestamp,
	userlocation_modified_by int references users(user_id),
	userlocation_modified_at timestamp with time zone,
	primary key(user_id,location_id)
);

create table users_companies(
	usercompany_id serial not null,
	company_id int not null references companies(company_id),
	user_id int not null references users(user_id),
	usercompany_active int not null default 1 check(usercompany_active = 1 or usercompany_active = 0),
	usercompany_delete int not null default 0,
	usercompany_created_by int not null references users(user_id),
	usercompany_created_at timestamp with time zone not null default current_timestamp,
	usercompany_modified_by int references users(user_id),
	usercompany_modified_at timestamp with time zone,
	primary key(user_id,company_id)
);


-- CONFIGURATION
create table settings(
	setting_id serial not null primary key,
	setting_type varchar(50) not null,
	setting_name varchar(80) not null,
	setting_name_spanish varchar(80),
	setting_short_name varchar(50) not null,
	setting_value varchar(50) not null,
	setting_code int not null,
	setting_order int,
	setting_active int not null default 1,
	setting_created_by int not null references users(user_id),
	setting_created_at timestamp with time zone not null default current_timestamp,
	setting_modified_by int references users(user_id),
	setting_modified_at timestamp with time zone
);

-- crear persona
-- crear usuario
-- crear profiles
-- crear menus
-- crear profiles_menu
-- crear users_profile


create table quotes(
	quote_id serial not null primary key,
	quote_name varchar(50) not null,
	quote_lastname varchar(50) not null,
	quote_direction varchar(80),
	quote_total numeric(12,2) not null,
	quote_created_by int not null references users(user_id),
	quote_created_at timestamp with time zone not null default current_timestamp,
	quote_modified_by int references users(user_id),
	quote_modified_at timestamp with time zone
);

create table quote_detail(
	quotedetail_id serial not null primary key,
	quote_id int not null references quotes(quote_id),
	product_id int not null references products(product_id),
	quotedetail_quantity int not null,
	quotedetail_unit_price numeric(12,2) not null,
	quotedetail_subtotal numeric(12,2) not null,
	quotedetail_created_by int not null references users(user_id),
	quotedetail_created_at timestamp with time zone not null default current_timestamp,
	quotedetail_modified_by int references users(user_id),
	quotedetail_modified_at timestamp with time zone
);


create table products(
    product_id serial not null primary key,
    product_code varchar(20),
    product_name varchar(30) not null unique,
    product_price numeric(12,2) not null,
    product_image text,
    product_active int not null default 1 check(product_active = 1 or product_active = 0), -- indica si el producto esta activo
	product_delete int not null default 0,
	product_created_by int not null references users(user_id),
	product_created_at timestamp with time zone not null default current_timestamp,
	product_modified_by int references users(user_id),
	product_modified_at timestamp with time zone
);


insert into products(product_code,product_name,product_price,product_image,product_active,product_created_by) 
values ('BRAV-001','Balon de Futbol',29.30,'',1,1);
insert into products(product_code,product_name,product_price,product_image,product_active,product_created_by) 
values ('BRAV-002','Balon de Basket',22.00,'https://wilsonstore.com.co/wp-content/uploads/2023/02/WTB9300XB07-1_0000_WTB9300XB_0_7_NBA_DRV_BSKT_OR.png.cq5dam.web_.1200.1200.jpg',1,1);
insert into products(product_code,product_name,product_price,product_image,product_active,product_created_by) 
values ('BRAV-003','Pelota de tenis',8.99,'https://assets.stickpng.com/images/580b585b2edbce24c47b2b90.png',1,1);
insert into products(product_code,product_name,product_price,product_image,product_active,product_created_by) 
values ('BRAV-004','Raqueta',49.50,'https://larrytennis.com/cdn/shop/products/WR074011U_9_900x.jpg',1,1);
insert into products(product_code,product_name,product_price,product_image,product_active,product_created_by) 
values ('BRAV-005','Palo de Golf',85.99,'https://i.ebayimg.com/thumbs/images/g/TEsAAOSwIK5fsjRp/s-l225.jpg',1,1);