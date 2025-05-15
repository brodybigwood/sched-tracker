import os
from dotenv import load_dotenv
import time

from selenium.webdriver.remote.webdriver import WebDriver
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.by import By

load_dotenv()

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

                availability = []
                shifts = []
                try:
                    for i, day in enumerate(days):
                        availability.append(day.find_element(By.XPATH, "./"+availability_xpath).get_attribute('textContent'))

                        role = ""
                        hours = ""
                        try:
                            role = day.find_element(By.XPATH, "./"+role_xpath).get_attribute('textContent')
                            hours = day.find_element(By.XPATH, "./"+hours_xpath).get_attribute('textContent')
                        except:
                            pass
                        

                        shift = {
                            "Role": role,
                            "Hours": hours
                        }
                        shifts.append(shift)
                    print(shifts)
                except Exception as e:
                    print(f"error in day info: {e}")

                        

            except Exception as e:
                print(f"Error finding or listing children of '{member}': {e}")
            

            employee = {
                'name': name 
            }
            print("employee found: "+employee["name"])
        except Exception as e:
            print(f"unable to find element by xpath: {e}")

    time.sleep(30000)
