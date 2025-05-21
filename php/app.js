


let scheduleView = {
    scrolltop: 0,
    viewType: 'Employees'
}

let view;

const currentView = document.getElementById('currentView');

async function setMainView(pageName) {
    try {

        if(pageName != view) {


            if(view == 'schedule') {
                scheduleView.scrolltop = currentView.contentWindow.scrollY;
                scheduleView.viewType = currentView.contentWindow.view;
            }
            view = pageName;
            currentView.src =`content.php?page=${view}`;

            const sendToFrame = () => {
                if (currentView.contentWindow) {
                    if(view == 'schedule') {
                        currentView.contentWindow.postMessage(JSON.stringify(scheduleView), '*');
                        console.log('JSON data sent to iframe:', scheduleView);
                    }
                }
                currentView.removeEventListener('load', sendToFrame);
            };
    
            currentView.addEventListener('load', sendToFrame);
        }

    } catch (error) {
        console.error('Error loading content:', error);
        currentView.innerHTML = '<p>Failed to load content. Please try again.</p>';
    }
}


setMainView('schedule');