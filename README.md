# Crowdfunding Platform

## Description
This is a simple crowdfunding platform built using PHP (with Object-Oriented Programming), MySQL, HTML, CSS, and JavaScript. It allows users to register, log in, create crowdfunding projects, and contribute to projects. The project follows the MVC (Model-View-Controller) architecture for better organization and maintainability.

---

## Features
- User Registration and Authentication
- Create, View, Edit, and Delete Crowdfunding projects
- Contribute to projects
- View Contribution History
- Responsive Design

---

## Project Structure
### Folder Organization
```
CrowdfundingApp/
├── App/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── ProjectController.php
│   │   ├── HomeController.php
│   │   ├── DonationController.php
│   │   ├── UserController.php
│   │   └── Router.php
│   ├── Models/
│   │   ├── User.php
│   │   └── Project.php
│   │   └── Donation.php
│   └── Views/
│       ├── auth/
│       │   ├── login.php
│       │   └── register.php
│       ├── projects/
│       │   ├── create.php
│       │   ├── edit.php
│       │   └── delete.php
│       └── donations/
│       |   ├── create.php
│       │   ├── edit.php
│       │   └── delete.php
│       ├── home.php
│       └── layout.php
├── Config/
│   └── database.php
├── public/
│   ├── css/
│   │   └── styles.css
│   ├── js/
│   │   └── script.js
│   └── index.php
└── README.md
```

---

## Requirements
- PHP >= 7.4
- MySQL
- XAMPP or any similar local server

---

## Installation
1. Clone the repository:
   ```bash
   git clone https://github.com/FrancKINANI/CrowdfundingApp.git
   ```

2. Navigate to the project directory:
   ```bash
   cd CrowdfundingApp
   ```

3. Import the database:
   - Open phpMyAdmin.
   - Create a new database (e.g., `crowdfunding`).
   - Import the `crowdfunding.sql` file located in the root directory.

4. Configure the database connection:
   - Open `Config/database.php`.
   - Update the database credentials (host, username, password, database name) according to your setup.

5. Start the server:
   - Place the project folder in the `htdocs` directory of XAMPP.
   - Start Apache and MySQL in XAMPP.
   - Open a browser and navigate to `http://localhost/CrowdfundingApp/App/Views/home.php`.

---

## Usage
1. **Register**: Create an account by visiting the registration page.
2. **Login**: Log in using your credentials.
3. **Create projects**: After logging in, create new crowdfunding project.
4. **Contribute**: View and contribute to existing project.
5. **Manage Campaigns**: Edit or delete projects you created.

---

## Technologies Used
- **Backend**: PHP (OOP)
- **Frontend**: HTML, CSS, JavaScript
- **Database**: MySQL
- **Server**: XAMPP (Apache, MySQL)

---

## Future Improvements
- Add user roles (e.g., Admin, Contributor, project Owner).
- Add search and filter functionality for projects.
- Implement email notifications for projects updates.
- Add a payment gateway for real contributions.

---

## License
This project is open-source and available under the [MIT License](LICENSE).

---

## Contact
For any inquiries, please contact:
- **Name**: David
- **Email**: fkinaninkaya@gmail.com
- **GitHub**: https://github.com/FrancKINANI/
