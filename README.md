# Super20 Academy – Coaching Class Management System

PHP + MySQL/MariaDB. Admin panel (`/`) and student portal (`/Student/login.php`).

## Setup
1. Copy the folder to your web root (e.g. `htdocs/super20`). Needs PHP 8.0+.
2. Import the database: `mysql -u root -p < setup/schema.sql`
   (Upgrading an old install? Run `setup/upgrade_existing_db.sql` instead.)
3. Create a limited MySQL user (commands are at the bottom of `schema.sql`), then
   copy `config/local.example.php` to `config/local.php` and enter those details.
   Do not run the app as `root`.
4. Create the first admin: `php setup/create_admin.php`
5. Open `http://localhost/super20/` and log in.

## How student accounts work
* The office adds students in **Admin → Students** (students can no longer register themselves).
* First login: Student Code + registered mobile number. The student is then forced to set their own password (min 8 chars, stored hashed).
* Forgot password? Admin → Students → **Reset Password**, which puts the student back to first-login mode.

## How fee payments work
* Admin-entered payments count immediately.
* A student's payment is saved as **Pending** with their UPI/transaction ID. It counts as paid only after an admin clicks **Approve** in Admin → Fees (check your bank/UPI statement first).
* Totals come from each student's `total_fees` in the database (no longer a fixed ₹50,000).
* This is still not a payment gateway. For automatic confirmation, integrate Razorpay/PayU etc.

## Security notes
* CSRF tokens on every form, hardened session cookies, login throttling (5 tries / 15 min).
* Use HTTPS in production. Keep `config/local.php` out of version control.
* Back up the database regularly.
