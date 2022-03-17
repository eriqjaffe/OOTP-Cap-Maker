const { ipcRenderer } = require('electron')

//const loadButton = document.getElementById('loadCap')

//console.log(loadButton)

ipcRenderer.on('load-cap', (event, data) => {
    $("#loadCap").click()
});

ipcRenderer.on('save-cap', (event, data) => {
    $("#save").click()
});

ipcRenderer.on('about', (event, data) => {
    $("#aboutCapmaker").click()
});