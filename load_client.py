from selenium import webdriver
from selenium.webdriver.chrome.service import Service as ChromeService
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.chrome.options import Options as ChromeOptions

import os
from dotenv import load_dotenv

load_dotenv()
chrome_exec_path = os.environ.get('CHROME_BINARY_PATH')

print(chrome_exec_path)

if chrome_exec_path:
    chrome_options = ChromeOptions()

    if(os.environ.get('USE_HEADLESS') == "True"):
        chrome_options.add_argument("--headless")

    chrome_options.binary_location = chrome_exec_path
    service = ChromeService(ChromeDriverManager().install())
    try:
        driver = webdriver.Chrome(service=service, options=chrome_options)
        print("Chrome launched successfully by Selenium!")
            
        url = os.environ.get('SCHEDULE_VIEW_URL')
        driver.get(url) 



        driver.quit()
    except Exception as e:
        print(f"Error launching Chrome with Selenium: {e}")
else:
    print("CHROME_DRIVER_PATH environment variable not set")