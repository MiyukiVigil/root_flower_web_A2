<?php
    // Database credentials
    $servername = "localhost";
    $username = "root";
    $password = "Miyuki404";
    $dbname = "RootFlower";

    try {
        // Step 1: Connect to MySQL (no DB selected yet)
        $conn = new PDO("mysql:host=$servername", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Step 2: Create database if it doesn’t exist
        $conn->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
        echo "Database '$dbname' created or already exists.<br>";

        // Step 3: Use the database
        $conn->exec("USE `$dbname`");

        // Step 4: Create user_table
        $sql = "CREATE TABLE IF NOT EXISTS user_table (
            email VARCHAR(50) NOT NULL PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            dob DATE NULL,
            gender VARCHAR(6) NOT NULL,
            hometown VARCHAR(50) NOT NULL,
            profile_image VARCHAR(100) NULL,
            resume VARCHAR(255) NULL
        )";
        $conn->exec($sql);
        echo "Table 'user_table' created.<br>";

        // Step 5: Create account_table
        $sql = "CREATE TABLE IF NOT EXISTS account_table (
            email VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL,
            type VARCHAR(5) NOT NULL,
            FOREIGN KEY (email) REFERENCES user_table(email)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        )";
        $conn->exec($sql);
        echo "Table 'account_table' created.<br>";

        // Step 6: Create flower_table
        $sql = "CREATE TABLE IF NOT EXISTS flower_table (
            id INT(4) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            Scientific_Name VARCHAR(50) NOT NULL,
            Common_Name VARCHAR(50) NOT NULL,
            plants_image VARCHAR(100) NULL,
            description VARCHAR(100) NULL
        )";
        $conn->exec($sql);
        echo "Table 'flower_table' created.<br>";

        // Step 7: Create workshop_table
        $sql = "CREATE TABLE IF NOT EXISTS workshop_table (
            id INT(4) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(50) NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            workshop_title VARCHAR(100) NOT NULL,
            date DATE NULL,
            time TIME NULL,
            contact_number VARCHAR(15) NULL
        )";
        $conn->exec($sql);
        echo "Table 'workshop_table' created.<br>";

        // Step 8: Create studentwork_table
        $sql = "CREATE TABLE IF NOT EXISTS studentwork_table (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            workshop_title VARCHAR(50) NOT NULL,
            workshop_image VARCHAR(100) NULL,
            description TEXT NOT NULL
        )";
        $conn->exec($sql);
        echo "Table 'studentwork_table' created.<br>";

        // Step 9: Insert dummy data if tables are empty

        // --- USER TABLE ---
        $check = $conn->query("SELECT COUNT(*) FROM user_table")->fetchColumn();
        if ($check == 0) {
            $conn->exec("
                INSERT INTO user_table (email, first_name, last_name, dob, gender, hometown, profile_image) VALUES
                ('admin@swin.edu.my', 'Admin', 'User', '1990-01-01', 'Male', 'Kuching', 'uploads/admin.jpg'),
                ('alice@gmail.com', 'Alice', 'Tan', '2001-05-20', 'Female', 'Penang', 'uploads/alice.jpg'),
                ('bob@gmail.com', 'Bob', 'Lim', '2000-03-10', 'Male', 'Johor', 'uploads/bob.jpg'),
                ('carol@gmail.com', 'Carol', 'Lee', '2002-07-25', 'Female', 'Kuala Lumpur', 'uploads/carol.jpg')
            ");
            echo "Dummy data inserted into 'user_table'.<br>";
        }

        // --- ACCOUNT TABLE ---
        $check = $conn->query("SELECT COUNT(*) FROM account_table")->fetchColumn();
        if ($check == 0) {
            $admin_pass = password_hash("admin", PASSWORD_DEFAULT);
            $user_pass = password_hash("password123", PASSWORD_DEFAULT);

            $conn->exec("
                INSERT INTO account_table (email, password, type) VALUES
                ('admin@swin.edu.my', '$admin_pass', 'admin'),
                ('alice@gmail.com', '$user_pass', 'user'),
                ('bob@gmail.com', '$user_pass', 'user'),
                ('carol@gmail.com', '$user_pass', 'user')
            ");
            echo "Dummy data inserted into 'account_table'.<br>";
        }

        // --- WORKSHOP TABLE ---
        $check = $conn->query("SELECT COUNT(*) FROM workshop_table")->fetchColumn();
        if ($check == 0) {
            $conn->exec("
                INSERT INTO workshop_table (email, first_name, last_name, date, time, contact_number) VALUES
                ('alice@gmail.com', 'Alice', 'Tan', '2025-12-10', '10:00:00', '0123456789'),
                ('bob@gmail.com', 'Bob', 'Lim', '2025-12-10', '10:00:00', '0139876543'),
                ('carol@gmail.com', 'Carol', 'Lee', '2025-12-11', '14:00:00', '0162233445'),
                ('admin@swin.edu.my', 'Admin', 'User', '2025-12-09', '09:00:00', '0191122334')
            ");
            echo "Dummy data inserted into 'workshop_table'.<br>";
        }

        // --- STUDENTWORK TABLE ---
        $check = $conn->query("SELECT COUNT(*) FROM studentwork_table")->fetchColumn();
        if ($check == 0) {
            $conn->exec("
                INSERT INTO studentwork_table (first_name, last_name, workshop_title, workshop_image, description) VALUES
                ('Amelia', 'Tan', 'Hobby Class', 'work1.jpg', 'My first hand-tied bouquet from the beginner''s class. I''m so proud of it!'),
                ('Ben', 'Carter', 'Hand-tied Bouquet Course', 'work2.jpg', 'Created this little world in the terrarium workshop. It was so much fun!'),
                ('Chloe', 'Davis', 'Florist To Be 1', 'work3.jpg', 'An ambitious centerpiece I made in the advanced course. Loved working with these vibrant colors.'),
                ('David', 'Lee', 'Hobby Class', 'work4.jpg', 'A simple and sweet bouquet. The spiral technique was tricky at first but so worth it!'),
                ('Eva', 'Chen', 'Hand-tied Bouquet Course', 'work5.jpg', 'Learning the Russian bouquet style was my favorite part of the course.'),
                ('Farid', 'Ismail', 'Florist To Be 2', 'work6.jpg', 'A gift for my mom. She loved it! Thanks for the great class.')
            ");
            echo "Dummy data inserted into 'studentwork_table'.<br>";
        }



        echo "<br>All tables and data successfully created and populated.";

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
?>
