CREATE DATABASE IF NOT EXISTS fz_petshop;
USE fz_petshop;

CREATE TABLE IF NOT EXISTS roles (
  id int NOT NULL AUTO_INCREMENT,
  name varchar(50) NOT NULL,
  description varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS users (
  id int NOT NULL AUTO_INCREMENT,
  username varchar(100) NOT NULL,
  password varchar(255) NOT NULL,
  role_id int NOT NULL,
  alamat varchar(100) DEFAULT NULL,
  email varchar(100) DEFAULT NULL,
  phone varchar(15) DEFAULT NULL,
  created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY role_id (role_id),
  CONSTRAINT users_ibfk_1 FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS categories (
  id int NOT NULL AUTO_INCREMENT,
  name varchar(100) NOT NULL,
  description varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS products (
  id int NOT NULL AUTO_INCREMENT,
  category_id int NOT NULL,
  name varchar(100) NOT NULL,
  description text,
  price decimal(10,2) DEFAULT NULL,
  stock int DEFAULT NULL,
  image varchar(255) DEFAULT NULL,
  status tinyint(1) DEFAULT '1',
  PRIMARY KEY (id),
  KEY category_id (category_id),
  CONSTRAINT products_ibfk_1 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
  id int NOT NULL AUTO_INCREMENT,
  user_id int NOT NULL,
  order_code varchar(100) NOT NULL,
  total_amount decimal(10,2) NOT NULL,
  status varchar(50) DEFAULT NULL,
  created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  berat_total decimal(10,2) DEFAULT '0.00',
  daerah varchar(100) DEFAULT '',
  PRIMARY KEY (id),
  KEY user_id (user_id),
  CONSTRAINT orders_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_items (
  id int NOT NULL AUTO_INCREMENT,
  order_id int NOT NULL,
  product_id int NOT NULL,
  quantity int NOT NULL,
  price decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY order_id (order_id),
  KEY product_id (product_id),
  CONSTRAINT order_items_ibfk_1 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
  CONSTRAINT order_items_ibfk_2 FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS payments (
  id int NOT NULL AUTO_INCREMENT,
  order_id int NOT NULL,
  method varchar(100) DEFAULT NULL,
  status varchar(100) DEFAULT NULL,
  amount decimal(10,2) DEFAULT NULL,
  paid_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY order_id (order_id),
  CONSTRAINT payments_ibfk_1 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS shippings (
  id int NOT NULL AUTO_INCREMENT,
  order_id int NOT NULL,
  courier varchar(100) DEFAULT NULL,
  tracking_number varchar(100) DEFAULT NULL,
  cost decimal(10,2) DEFAULT NULL,
  status varchar(100) DEFAULT NULL,
  estimated_delivery date DEFAULT NULL,
  PRIMARY KEY (id),
  KEY order_id (order_id),
  CONSTRAINT shippings_ibfk_1 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reports (
  id int NOT NULL AUTO_INCREMENT,
  title varchar(100) DEFAULT NULL,
  description text,
  type varchar(100) DEFAULT NULL,
  created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS blogs (
  id int NOT NULL AUTO_INCREMENT,
  title varchar(100) DEFAULT NULL,
  content text,
  category varchar(100) DEFAULT NULL,
  created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS comments (
  id int NOT NULL AUTO_INCREMENT,
  blog_id int NOT NULL,
  user_id int NOT NULL,
  content text,
  created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY blog_id (blog_id),
  KEY user_id (user_id),
  CONSTRAINT comments_ibfk_1 FOREIGN KEY (blog_id) REFERENCES blogs (id) ON DELETE CASCADE,
  CONSTRAINT comments_ibfk_2 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS settings (
  id int NOT NULL AUTO_INCREMENT,
  name varchar(100) NOT NULL,
  value varchar(255) DEFAULT NULL,
  description varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS shipping_rates (
  id int NOT NULL AUTO_INCREMENT,
  daerah varchar(100) NOT NULL,
  berat_min decimal(10,2) NOT NULL,
  berat_max decimal(10,2) NOT NULL,
  tarif decimal(12,2) NOT NULL,
  PRIMARY KEY (id)
);