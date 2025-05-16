const dropdown = document.querySelector('#dropdown');
const dropdownList = document.querySelector('.dropdown-list');
const selectedOptionSpan = document.querySelector('#selected-option');
const dropdownItems = document.querySelectorAll('.dropdown-list li');

const viewType = document.getElementById('viewType');

dropdown.addEventListener('click', () => {
    const isDropdownOpen = dropdownList.classList.contains('open');
  
    if (!isDropdownOpen) {
      dropdownList.classList.toggle('open');
    }
});

dropdownItems.forEach(item => {
  item.addEventListener('click', () => {
    const selectedText = item.textContent;
    dropdownList.classList.remove('open');
    const selectedValue = item.textContent.toLowerCase().replace(/ /g, '_');
    console.log('Selected view:', selectedValue);
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
    viewType.textContent = "by "+value;
}

setView('employee');