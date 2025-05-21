import sqlite3
import sys
import os
from passlib.hash import bcrypt 

DATABASE_FILE = 'weeks.db'  

def set_employee_password(employee_name, new_password):
    """Sets the password for a given employee in the database."""
    try:
        conn = sqlite3.connect(DATABASE_FILE)
        cursor = conn.cursor()

        cursor.execute("SELECT employee_id FROM Employees WHERE name = ?", (employee_name,))
        result = cursor.fetchone()

        if not result:
            print(f"Error: Employee '{employee_name}' not found.")
            return

        employee_id = result[0]
        hashed_password = bcrypt.hash(new_password)

        cursor.execute("UPDATE Employees SET password_hash = ? WHERE employee_id = ?", (hashed_password, employee_id))
        conn.commit()
        print(f"Password for employee '{employee_name}' has been successfully set.")

    except sqlite3.Error as e:
        print(f"Database error: {e}")
    finally:
        if conn:
            conn.close()

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python setpassword.py <employee_name> <new_password>")
    else:
        employee_name_to_set = sys.argv[1]
        new_password_to_set = sys.argv[2]
        set_employee_password(employee_name_to_set, new_password_to_set)