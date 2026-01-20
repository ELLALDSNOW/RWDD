
CREATE TABLE contacts_temp (
    id INT PRIMARY KEY AUTO_INCREMENT,
    contact_name VARCHAR(255),
    contact_phone VARCHAR(255),
    contact_email VARCHAR(255),
    contact_address TEXT,
    contact_gender VARCHAR(50),
    contact_relationship VARCHAR(255),
    contact_dob DATE
);


INSERT INTO contacts_temp (contact_name, contact_phone, contact_email, contact_address, contact_gender, contact_relationship, contact_dob)
SELECT contact_name, contact_phone, contact_email, contact_address, contact_gender, contact_relationship, contact_dob
FROM contacts;


DROP TABLE contacts;


RENAME TABLE contacts_temp TO contacts;