from selenium import webdriver
from selenium.webdriver.chrome.service import Service as ChromeService
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.chrome.options import Options as ChromeOptions
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

import os
from dotenv import load_dotenv

import time
import json

load_dotenv()

url = os.environ.get('SCHEDULE_URL')

submit_button_text = os.environ.get('LOGIN_BUTTON_TEXT')
submit_button_type = os.environ.get('LOGIN_BUTTON_TYPE')

login_params_str = os.environ.get('LOGIN_PARAMS')
schedule_pattern_str = os.environ.get('SCHEDULE_CLICK_PATTERN')
login_params = {}
schedule_pattern = {}

if login_params_str:
    login_params = json.loads(login_params_str)
else:
    print("LOGIN_PARAMS not found in .env file.")

if schedule_pattern_str:
    schedule_pattern = json.loads(schedule_pattern_str)
else:
    print("SCHEDULE_CLICK_PATTERN not found in .env file.")



print(login_params)

def login(driver):


    driver.get(url)

    try:
        for key, value in login_params.items():
            try:
                # Construct the ID of the input field based on the key
                # You'll need to know the actual IDs of the input fields
                input_id = key.lower()  # Assuming IDs are lowercase versions of keys

                text_input = WebDriverWait(driver, 10).until(
                    EC.presence_of_element_located((By.ID, input_id))
                )
                text_input.clear()
                text_input.send_keys(value)
                print(f"Entered '{value}' into field with ID '{input_id}'")

            except Exception as e:
                print(f"Error interacting with field '{key}': {e}")

        # After filling all fields, you would typically find and click the login button
        login_button_xpath = f"//button[@type='{submit_button_type}'][contains(text(), '{submit_button_text}')]"
        submit = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.XPATH, login_button_xpath))
        )
        print("found login button")

        time.sleep(10)

        submit.click()
        print("logging in")

        time.sleep(10)

        for id in schedule_pattern:
            try:
                element = WebDriverWait(driver, 10).until(
                    EC.presence_of_element_located((By.ID, id))
                )
                element.click()
                print(f"clicked navigator: '{id}'")
                time.sleep(2)
            except Exception as e:
                print(f"unable to interact with navigator '{id}': {e}")
    except Exception as e:
        print("Unable to interact with login fields, client must be logged in")


    time.sleep(30000)
