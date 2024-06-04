# Formulator - Self Serve Form Builder

## Overview

Formulator is a self-serve form builder application built from scratch using PHP, MySQL, Symfony, JavaScript, and jQuery. The application allows users to create, manage, and analyze forms with ease. Formulator features include user authentication, a comprehensive dashboard, and a versatile form builder that supports various input types and advanced settings.

## Features

- **User Authentication**: Secure login and registration system.
- **Dashboard**: Index page with counters and charts displaying form submission statistics.
- **Form Management**: Full CRUD (Create, Read, Update, Delete) operations for forms.
- **Form Settings**: Options to set expiration dates and redirections after form submission.
- **Input Types**:
  - Input field
  - Paragraph
  - Single choice
  - Multiple choice
  - Date picker
  - Time picker
  - File upload
- **Submission Details**: Detailed result page showing user information such as IP address and device agent.
- **Easy-to-Use**: Intuitive and user-friendly form builder interface.

## Technologies Used

- **Backend**: PHP, Symfony, MySQL
- **Frontend**: Bootstrap, DataTables, ChartJS, JavaScript, jQuery, HTML, CSS

## Installation

1.  **Clone the repository**:

    ```bash
    git clone <https://github.com/isemary/formulator.git>

    ```

2.  **Navigate to the project directory**:

    ```bash
    cd formulator

    ```

3.  **Install dependencies**:

    ```bash
    composer install
    ```

4.  **Set up environment variables**:
    Copy the `.env` file and configure your database settings and other environment variables.
    
        ```bash
        cp .env.example .env

        ```
6.  **Set up the database**:

    ```bash
    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate

    ```

7.  **Convert SCSS to CSS**:

    ```bash
    sass --watch public/dashboard/css/main.scss:public/dashboard/css/main.css
    ```

8.  **Start the Symfony server**:

    ```bash
    symfony server:start

    ```

## Usage

1. **Register and Login**:
   - Navigate to the registration page to create a new account.
   - Use the login page to access your dashboard.
2. **Dashboard**:
   - View counters and charts that provide insights into form submissions.
3. **Creating a Form**:
   - Use the form builder to add various input types.
   - Configure form settings such as expiration date and post-submission redirection.
4. **Managing Forms**:
   - Access the forms list to edit or delete existing forms.
5. **Viewing Submissions**:
   - Navigate to the results page to view detailed information about each form submission, including the user's IP address and device agent.

## Contact

For any inquiries or support, please email me at [abdelrahmansamirmostafa@gmail.com](mailto:abdelrahmansamirmostafa@gmail.com) or visit my website at [abdelrahman.online](https://www.abdelrahman.online/).
