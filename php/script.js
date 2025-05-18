const dropdown = document.querySelector('#dropdown');
const dropdownList = document.querySelector('.dropdown-list');
const selectedOptionSpan = document.querySelector('#selected-option');
const dropdownItems = document.querySelectorAll('.dropdown-list li');

const currentWeekDisplay = document.getElementById('current-week-display');

const viewType = document.getElementById('viewType');
table = document.getElementById('table');

dropdown.addEventListener('click', () => {
    const isDropdownOpen = dropdownList.classList.contains('open');
  
    if (!isDropdownOpen) {
      dropdownList.classList.toggle('open');
    }
});

dropdownItems.forEach(item => {
  item.addEventListener('click', () => {
    const text = item.textContent;
    dropdownList.classList.remove('open');
    const selectedValue = item.textContent.replace(/ /g, '_');

    setView(selectedValue);

  });
});

// Close the dropdown if the user clicks outside of it
document.addEventListener('click', (event) => {
    const isClickInsideDropdown = event.target.closest('#dropdown');
    const isDropdownOpen = dropdownList.classList.contains('open');
  
    // Close the dropdown only if it's currently open AND the click was outside the dropdown
    if (isDropdownOpen && !isClickInsideDropdown) {
      dropdownList.classList.remove('open');
    }
  });


  function setView(value) {
    setViewIndicator(value);
    if(value == 'Employees') {
        return fetchJSON(currentWeek, 'Employees')
            .then(data => {
                if (data === null) {
                    return false;
                }
                constructDefaultPattern(data, employeeSortPattern);
                employees = sort(data, employeeSortPattern);
                listEmployees(employees);
                return true;
            });
    } else {
        return Promise.resolve(true);
    }
}

function setViewIndicator(string) {
    viewType.textContent = "Search "+string.toLowerCase()+"...";
}

function listEmployees(employees) {
    const new_table = document.createElement('div');
    position = ""
    for(const employee of employees) {
        if(employee.isOnSchedule == 0) {
            continue;
        }
        if(position != employee.position) {
            position = employee.position;
            position_header = document.createElement('div');
                position_header.classList.add('position-header');
                position_header.innerText = position;
            new_table.appendChild(position_header);
        }
        _name = employee.name;

        const employeeDiv = document.createElement('div');
        employeeDiv.classList.add('employee');
            header = document.createElement('h3');
            header.innerText = _name;
            employeeDiv.appendChild(header);

            shiftList = document.createElement('div');
            shiftList.classList.add('shiftList');
                shifts = employee.shifts;
                day_of_week = 0;
                prevEnd = 0;

                const days = Array.from({ length: 7 }, () => {
                    const dayDiv = document.createElement('div');
                    dayDiv.classList.add('workday');
                    return dayDiv;
                  });

                workday = days[day_of_week];

                for(const shift of shifts) {
                    if(shift.day_of_week != day_of_week) {
                        day_of_week = shift.day_of_week;
                        workday = days[day_of_week];
                        prevEnd = 0;
                    }

                    //hours = shift.hours;
                    start = shift.start_time;
                    end = shift.end_time;

                    emptyTime = document.createElement('div');
                    emptyTime.classList.add('empty-time');
                    emptyTime.style.setProperty('height', `calc(100% * (${start - prevEnd}) / 24)`);
                        assigned_position = shift.assigned_position;
                        posText = document.createElement('h3');
                        posText.innerText = assigned_position;
                        emptyTime.appendChild(posText);
                    workday.appendChild(emptyTime);

                    result = generateShiftTime(shift);

                    prevEnd = result.end;
                    shiftBlock = result.shift;

                    workday.appendChild(shiftBlock);
                }
                emptyTime = document.createElement('div');
                emptyTime.classList.add('empty-time');
                emptyTime.style.setProperty('height',`calc(100% * (${24 - prevEnd}) / 24)`);
                
                days.forEach(dayDiv => {
                    shiftList.appendChild(dayDiv);
                });
            employeeDiv.appendChild(shiftList);
        new_table.appendChild(employeeDiv);
    }
    table.innerHTML = new_table.innerHTML;
}

function getTimeString(time24) {
    if (time24 < 0 || time24 > 24) {
        return "Invalid 24-hour format";
    }

    hours24 = Math.floor(time24);
    minutes = Math.round((time24 - hours24) * 60);

    if (minutes === 60) {
        hours24++;
        minutes = 0;
    }

    period = "AM";
    hours12 = hours24;

    if (hours24 === 0) {
        hours12 = 12; // Midnight
    } else if (hours24 === 12) {
        period = "PM"; // Noon
    } else if (hours24 > 12) {
        hours12 = hours24 - 12;
        period = "PM";
    }

    minuteString = minutes.toString().padStart(2, '0');

    return hours12 + ":" + minuteString + period;
}


function generateShiftTime(shift) {


    start = shift.start_time;
    end = shift.end_time;

    start_str = getTimeString(start);
    end_str = getTimeString(end);

    let shiftBlock = document.createElement('div');
    shiftBlock.style.height = `calc(100% * (${end - start}) / 24)`;
    shiftBlock.classList.add('shift');

        const time = document.createElement('div');
        time.classList.add('shiftTime');
            
            const header = document.createElement('h3');
            header.innerText = start_str+' to '+end_str;
            time.appendChild(header);
            shiftBlock.appendChild(time);


    return {end: end, shift: shiftBlock};
}

function searchEmployees(term) {

}

positionSortPattern = []

employees = [];
employeeSortPattern = {
    parameter: 'position',
    pattern: positionSortPattern
}

function constructDefaultPattern(elements, sortPattern) {
    parameter = sortPattern.parameter;
    pattern = sortPattern.pattern;

    updated_pattern = [];

    for(const element in elements) {
        value = element[parameter];
        if(!pattern.includes(value)) {
            updated_pattern.push(value);
        }
    }

}

constructDefaultPattern


function sort(elements, sortPattern) {

    parameter = sortPattern.parameter;
    pattern = sortPattern.pattern;
    
    const sortedData = [...elements]; // Create a copy to avoid modifying the original array

    sortedData.sort((a, b) => {

        const paramA = a[parameter];
        const paramB = b[parameter];

        aIndex = false;
        bIndex = false;


        aIndex = pattern.indexOf(paramA);

        bIndex = pattern.indexOf(paramB);

        if(aIndex == bIndex == -1) {
            return 0;
        }

        if(aIndex != -1 && bIndex == -1 || aIndex < bIndex) {
            return -1;
        }

        if(bIndex != -1 && aIndex == -1 || aIndex > bIndex) {
            return 1;
        }
    });
    return sortedData;
}





function fetchJSON(week, queryType) {
    const url = `db_to_json.php?week=${week}&queryType=${queryType}`;
    console.log(`url ${url}`)
    return fetch(url)
        .then(response => {
            if(!response.ok) {
                throw new Error(`HTTP error ${response.status}`);
            }
            return response.json();
        })
}



function getWeek() {
    const today = new Date();
    const offsetDate = new Date(today); 
  
    offsetDate.setDate(today.getDate() + (7 * weekOffset));

    const dayOfWeek = offsetDate.getDay(); 
    const diff = offsetDate.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1); 
    const monday = new Date(offsetDate.setDate(diff));
    const year = monday.getFullYear();
    const month = monday.getMonth();
    const day = monday.getDate();
    return `${year}-${month}-${day}`;
  }
  
weekOffset = 0;

function week(move) {
    prevWeekOffset = weekOffset;
    prevCurrentWeek = currentWeek;
    weekOffset+=move;
    currentWeek = getWeek();
    setView('Employees')
    .then(result => {
        if (result === true) {
            console.log("Employee view updated successfully.");

            const today = new Date();
            const offsetDate = new Date(today); 
          
            offsetDate.setDate(today.getDate() + (7 * weekOffset));

            const dayOfWeek = offsetDate.getDay(); 

            const diff = offsetDate.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1); 
            const monday = new Date(offsetDate.setDate(diff));

            const optionsMonth = { month: 'long' };
            const monthName = monday.toLocaleDateString(undefined, optionsMonth);

            const optionsDay = { day: 'numeric' }; 
            const dayNumber = monday.toLocaleDateString(undefined, optionsDay);

            currentWeekDisplay.textContent = 'Week: ' + monthName + ' ' + dayNumber;
            
        } else {
            alert('no data for desired week!');
            weekOffset = prevWeekOffset;
            currentWeek = prevCurrentWeek;
        }
    });
}

currentWeek = getWeek();

console.log(currentWeek)

week(0);







if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/service-worker.js')
        .then((registration) => {
          console.log('Service worker registered! Scope:', registration.scope);

        })
        .catch((err) => {
          console.log('Service worker registration failed:', err);

        });
    });
  } else {
    console.log('Service workers are not supported in this browser.');
  }