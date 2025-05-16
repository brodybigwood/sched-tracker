console.log('Service worker loaded');

self.addEventListener('install', event => {
  console.log('Service worker installing...');

});

self.addEventListener('activate', event => {
  console.log('Service worker activated.');

});

self.addEventListener('fetch', event => {

});