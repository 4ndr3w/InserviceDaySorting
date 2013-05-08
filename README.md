## InserviceDaySorting


### Installation

Prerequisites:
* A webserver able to execute PHP files
* PHP with the MySQL extention
* A MySQL Database

Installation instructions:

1. Create an empty MySQL database and import the tables from structure.sql
2. Put the contents of this folder where your webserver is serving from
3. Rename or copy config.php.example to config.php
4. Open config.php with your favorite editor and set your MySQL connection information
5. Go to the admin page with your web browser and start adding careers

### Hidden Sessions
The benefits fair session is stored in the database with the ID 999. If this is changed, config.php will need to be updated with the new ID.

The benefits fair session is automatically assigned to participants by the sorting algorithm, and is not visable on the signup form.
