CREATE TABLE candidates (
    id int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
    first_name varchar(50) NOT NULL,
    last_name varchar(50) NOT NULL,
    email varchar(320) NOT NULL,
    phone varchar(20),
    password varchar(100) NOT NULL
);

CREATE TABLE companies (
    id int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
    company_name varchar(100) NOT NULL,
    email varchar(320) NOT NULL,
    address varchar(150),
    password varchar(100) NOT NULL
);

CREATE TABLE candidate_experience (
    id int(11),
    title varchar(100) NOT NULL,
    company_id int(11),
    company_name varchar(100) NOT NULL,
    type varchar(30),
    start_month varchar(10) NOT NULL,
    start_year int(11) NOT NULL,
    end_month varchar(10),
    end_year int(11),
    description varchar(1500),
    FOREIGN KEY (id) REFERENCES candidates(id),
    FOREIGN KEY (company_id) REFERENCES companies(id)
);