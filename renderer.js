const { ipcRenderer } = require('electron')

ipcRenderer.on('load-cap', (event, data) => {
    $("#loadCap").click()
});

ipcRenderer.on('save-cap', (event, data) => {
    $("#save").click()
});

ipcRenderer.on('about', (event, data) => {
    $("#aboutCapmaker").click()
});