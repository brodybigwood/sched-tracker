import os
from dotenv import load_dotenv
import time
import json
import re

from selenium.webdriver.remote.webdriver import WebDriver
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.by import By

load_dotenv()

outfile = "currentWeek.json"

shift_roles_string = os.environ.get('SHIFT_ROLES')
shift_roles = []

try:
    shift_roles = json.loads(shift_roles_string)
except json.JSONDecodeError as e:
    print(f"Error decoding shift_roles from environment variable: {e}")
    shift_roles = [] # Initialize as an empty list in case of error


def readData(driver):

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
    

    for member in members:
        try:
            name = member.find_element(By.XPATH, "./"+name_xpath).text
            if not name:
                continue



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

                        shifts[i] = workday

                except Exception as e:
                    print(f"error in day info: {e}")

                        

            except Exception as e:
                print(f"Error finding or listing children of '{member}': {e}")
            

            employee = {
                'name': name,
                'availability': availability,
                'shifts': shifts
            }
            employees.append(employee)

            
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


def extractRole(shift_str):
    for aliases in shift_roles:
        for alias in aliases:
            alias = alias.lower()
            if alias in shift_str:
                return aliases[0]