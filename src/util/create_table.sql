--users
CREATE TABLE candidates (
    id int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
    first_name varchar(50) NOT NULL,
    last_name varchar(50) NOT NULL,
    email varchar(320) NOT NULL,
    phone varchar(20),
    location varchar(100),
    password varchar(100) NOT NULL
);

CREATE TABLE companies (
    id int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
    company_name varchar(100) NOT NULL,
    email varchar(320) NOT NULL,
    address varchar(150),
    website varchar(100),
    password varchar(100) NOT NULL
);

--candidate data
CREATE TABLE candidate_about (
    candidate_id int(11) NOT NULL,
    text varchar(1500),
    FOREIGN KEY (candidate_id) REFERENCES candidates(id)
);

CREATE TABLE candidate_experience (
    id int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
    title varchar(100) NOT NULL,
    candidate_id int(11) NOT NULL,
    company_id int(11),
    company_name varchar(100) NOT NULL,
    type varchar(30),
    start_month varchar(10) NOT NULL,
    start_year int(11) NOT NULL,
    end_month varchar(10),
    end_year int(11),
    description varchar(1500),
    FOREIGN KEY (candidate_id) REFERENCES candidates(id),
    FOREIGN KEY (company_id) REFERENCES companies(id)
);

CREATE TABLE candidate_education (
    id int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
    candidate_id int(11) NOT NULL,
    institution_name varchar(100) NOT NULL,
    start_month varchar(10) NOT NULL,
    start_year int(11) NOT NULL,
    end_month varchar(10),
    end_year int(11),
    degree varchar(100),
    study_field varchar(100),
    description varchar(1500),
    FOREIGN KEY (candidate_id) REFERENCES candidates(id)
);

CREATE TABLE candidate_projects (
    id int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
    candidate_id int(11) NOT NULL,
    project_name varchar(100) NOT NULL,
    start_month varchar(10) NOT NULL,
    start_year int(11) NOT NULL,
    end_month varchar(10),
    end_year int(11),
    project_link varchar(200),
    description varchar(1500),
    FOREIGN KEY (candidate_id) REFERENCES candidates(id)
);

--job-related data
CREATE TABLE jobs (
    id int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
    company_id int(11) NOT NULL,
    title varchar(100) NOT NULL,
    skills json,
    type varchar(20) NOT NULL,
    level varchar(20) NOT NULL,
    location_name VARCHAR(200) CHARSET utf8 NOT NULL,
    location_coords json NOT NULL,
    physical varchar(10) NOT NULL,
    salary varchar(30),
    date_posted date NOT NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id)
);

CREATE TABLE applicants (
    candidate_id int(11) NOT NULL,
    job_id int(11) NOT NULL,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id),
    FOREIGN KEY (job_id) REFERENCES jobs(id)
);

CREATE TABLE bookmarks (
    candidate_id int(11) NOT NULL,
    job_id int(11) NOT NULL,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id),
    FOREIGN KEY (job_id) REFERENCES jobs(id)
);

CREATE TABLE hidden (
    candidate_id int(11) NOT NULL,
    job_id int(11) NOT NULL,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id),
    FOREIGN KEY (job_id) REFERENCES jobs(id)
);