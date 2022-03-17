const { ipcRenderer } = require('electron')

//const loadButton = document.getElementById('loadCap')

//console.log(loadButton)

ipcRenderer.on('load-cap', (event, data) => {
    console.log($("#loadCap").val())
    $("#loadCap").click()
});