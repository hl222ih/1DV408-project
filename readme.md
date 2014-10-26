
```
Use Cases
Test report 2014-10-26
Database UML diagram
Class Diagram for application
Activity diagram (kind of) for login logic overview
Activity diagram (kind of) for model view overview
Database Dump MySql Export

http://blisskom.besaba.com/1DV408-project/
För underlättande av testning, förskapade konton med några skapade händelser:

Förälder 1: Pappa/Password
Förälder 2: Mamma/Password
Barn 1: Peter/Password
Barn 2: Lotta/Password

Everything to be found here:
https://drive.google.com/open?id=0B2xSA_T08RU_R0FlTzRPRkRCaWs&authuser=0

==Run instructions==

1. Create the database
2. Import the structure with help from the database dump.
3. Make changes in /dao/database-config.php which looks like this:


-------------------------------
<?php

namespace BoostMyAllowanceApp\Model\Dao;


class DatabaseConfig {
    public $username = "the_db_username";
    public $password = "the_db_password";
    public $database = "the_db_name";
    public $host = "localhost";
}
-------------------------------
```