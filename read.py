import os
from dotenv import load_dotenv
import time
import json
import re

import sqlite3

from selenium.webdriver.remote.webdriver import WebDriver
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.by import By

load_dotenv()

database_file = 'weeks.db'

connection = sqlite3.connect(database_file)

cursor = connection.cursor()




connection = sqlite3.connect(database_file)
cursor = connection.cursor()

# *** ADD TABLE CREATION CODE HERE ***
cursor.execute('''
    CREATE TABLE IF NOT EXISTS Weeks (
        week_id INTEGER PRIMARY KEY AUTOINCREMENT,
        year INTEGER NOT NULL,
        month INTEGER NOT NULL CHECK (month BETWEEN 1 AND 12),
        day INTEGER NOT NULL CHECK (day BETWEEN 1 AND 31),
        UNIQUE (year, month, day)
    )
''')

cursor.execute('''
    CREATE TABLE IF NOT EXISTS Employees (
        employee_id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        role TEXT,
        isOnSchedule INT CHECK (isOnSchedule BETWEEN 0 and 1)
    )
''')

cursor.execute('''
    CREATE TABLE IF NOT EXISTS Availability (
        availability_id INTEGER PRIMARY KEY AUTOINCREMENT,
        employee_id INTEGER,
        week_id INTEGER,
        day_of_week INTEGER NOT NULL CHECK (day_of_week BETWEEN 0 AND 6),
        start_time REAL,
        end_time REAL,
        FOREIGN KEY (employee_id) REFERENCES Employees(employee_id),
        FOREIGN KEY (week_id) REFERENCES Weeks(week_id),
        UNIQUE (employee_id, week_id, day_of_week)
    )
''')

cursor.execute('''
    CREATE TABLE IF NOT EXISTS Shifts (
        shift_id INTEGER PRIMARY KEY AUTOINCREMENT,
        employee_id INTEGER,
        week_id INTEGER,
        day_of_week INTEGER NOT NULL CHECK (day_of_week BETWEEN 0 AND 6),
        start_time REAL,
        end_time REAL,
        assigned_role TEXT,
        FOREIGN KEY (employee_id) REFERENCES Employees(employee_id),
        FOREIGN KEY (week_id) REFERENCES Weeks(week_id)
    )
''')

connection.commit()



outfile = "currentWeek.json"

shift_roles_string = os.environ.get('SHIFT_ROLES')
shift_roles = []

try:
    shift_roles = json.loads(shift_roles_string)
except json.JSONDecodeError as e:
    print(f"Error decoding shift_roles from environment variable: {e}")
    shift_roles = [] # Initialize as an empty list in case of error


def readData(driver):

    year, month, day = getDate(driver)

    print(f"schedule start: {month}/{day}")


    cursor.execute('''
        INSERT OR IGNORE INTO Weeks(year, month, day) VALUES (?, ?, ?)
    ''',
    (year, month, day))
    cursor.execute('''
    SELECT week_id FROM Weeks WHERE year= ? AND month = ? AND day = ?
    ''', (year,month,day))
    week_result = cursor.fetchone()
    week_id = week_result[0]
    print(week_id)

    table_xpath = os.environ.get('EMPLOYEES_XPATH')

    members = []

    try:
        parent_element = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.XPATH, table_xpath))
        )
        child_elements = parent_element.find_elements(By.XPATH, ".//tr")
        members = child_elements
    except Exception as e:
        print(f"Error finding or listing children of '{table_xpath}': {e}")

    name_xpath = os.environ.get('NAME_XPATH_EMPLOYEE')


    employees = []

    employeeRole = ""
    

    for member in members:
        try:
            name = member.find_element(By.XPATH, "./"+name_xpath).text
            if not name:
                continue

            print(f"checking if {name.lower()} is a role")
            extracted_role = extractRole(name.lower())
            if extracted_role:
                employeeRole = extracted_role
                print("yes")
                continue

            cursor.execute("INSERT OR IGNORE INTO Employees (name, role) VALUES (?, ?)", (name, employeeRole))
            cursor.execute("SELECT employee_id FROM Employees WHERE name = ?", (name,))
            employee_result = cursor.fetchone()
            employee_db_id = employee_result[0]
            cursor.execute("UPDATE Employees SET isOnSchedule = 1 WHERE employee_id = ?", (employee_db_id,))
            print(employee_db_id)

            try:
                days_xpath = os.environ.get('EMPLOYEE_DAY_ELEMENT')
                availability_xpath = os.environ.get('EMPLOYEE_DAY_AVAILABILITY')
                hours_xpath = os.environ.get('EMPLOYEE_SHIFT_HOURS')
                role_xpath = os.environ.get('EMPLOYEE_SHIFT_ROLE')

                days = []
                days = member.find_elements(By.XPATH, "./"+days_xpath)



                availability = ["n/a"] * 7
                shifts = [None] * 7

                try:
                    for i, day in enumerate(days):

                        availability_raw = day.find_element(By.XPATH, "./"+availability_xpath).get_attribute('textContent')
                        startTime, endTime = extractTime(availability_raw)

                        availability[i] = {
                            'start': startTime,
                            'end': endTime
                        }
                        if startTime is not None and endTime is not None:
                            cursor.execute("INSERT OR IGNORE INTO Availability (employee_id, week_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?, ?)",(employee_db_id, week_id, i, startTime, endTime))
                        

                        subElements = day.find_elements(By.XPATH, "./*")
                        numElements = len(subElements)
                        workday = []
                        for j in range(1,numElements-1):
                            hours_raw = subElements[j].find_element(By.XPATH, "."+hours_xpath).get_attribute('textContent').lower()
                            role_raw = subElements[j].find_element(By.XPATH, "."+role_xpath).get_attribute('textContent').lower()
                            startTime, endTime = extractTime(hours_raw)

                            role = extractRole(role_raw)

                            shift = {
                                'Hours': {
                                    'start': startTime,
                                    'end': endTime
                                },
                                'Role': role
                            }

                            workday.append(shift)

                            if startTime is not None and endTime is not None and role:
                                cursor.execute("INSERT INTO Shifts (employee_id, week_id, day_of_week, start_time, end_time, assigned_role) VALUES (?, ?, ?, ?, ?, ?)",(employee_db_id, week_id, i, startTime, endTime, role))

                        shifts[i] = workday

                except Exception as e:
                    print(f"error in day info: {e}")
        

            except Exception as e:
                print(f"Error finding or listing children of '{member}': {e}")

            employee = {
                'role': employeeRole,
                'name': name,
                'availability': availability,
                'shifts': shifts
            }
            employees.append(employee)
            connection.commit()
            
        except Exception as e:
            print(f"{e}")

    try:
        with open(outfile, 'w') as f:
            json.dump(employees, f, indent=4)
        print(f"json data has been saved to '{outfile}'")
    except IOError as e:
        print("error")


def extractTime(hours_str):
    match = re.search(r"(\d{1,2})(?::(\d{2}))?(am|pm)\s*-\s*(\d{1,2})(?::(\d{2}))?(am|pm)", hours_str, re.IGNORECASE)
    if match:
        start_hour_str, start_minute_str, start_ampm, end_hour_str, end_minute_str, end_ampm = match.groups()

        def time_to_float(hour_str, minute_str, ampm):
            hour = int(hour_str)
            minute = int(minute_str) if minute_str else 0

            if ampm.lower() == 'pm' and hour != 12:
                hour += 12
            elif ampm.lower() == 'am' and hour == 12:
                hour = 0
            return hour + minute / 60.0

        startTime = time_to_float(start_hour_str, start_minute_str, start_ampm)
        endTime = time_to_float(end_hour_str, end_minute_str, end_ampm)
        return startTime, endTime
    else:
        return None, None


def extractRole(string):
    for aliases in shift_roles:
        for alias in aliases:
            lower_alias = alias.lower()
            pattern = r"(?<![a-zA-Z])" + re.escape(lower_alias) + r"(?![a-zA-Z])"
            if re.search(pattern, string):
                return aliases[0]
    return False


date_path = os.environ.get('SCHEDULE_START_DATE_XPATH')

months = [
    "jan",
    "feb",
    "mar",
    "apr",
    "may",
    "jun",
    "jul",
    "aug",
    "sept",
    "oct",
    "nov",
    "dec"
]

def getDate(driver):
    date_raw = WebDriverWait(driver, 10).until(
        EC.presence_of_element_located((By.XPATH, date_path))
    ).text.lower()
    for i, month in enumerate(months):
        if month.lower() in date_raw:
            match = re.search(r'\d+', date_raw)
            day = int(match.group(0))
            return 2025, i+1, day
    return None        

prevWeekPath = os.environ.get('SCHEDULE_PREV_WEEK_XPATH')
nextWeekPath = os.environ.get('SCHEDULE_NEXT_WEEK_XPATH')