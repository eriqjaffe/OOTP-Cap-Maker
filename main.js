// main.js

// Modules to control application life and create native browser window
const { app, BrowserWindow, dialog, Menu, shell, webContents, ipcMain } = require('electron')
const path = require('path')
const Jimp = require('jimp');
const os = require('os');
const tempDir = os.tmpdir()
const fs = require('fs')
const ttfInfo = require('ttfinfo');
const url = require('url');
const isMac = process.platform === 'darwin'
const archiver = require('archiver')
//const font2base64 = require("node-font2base64")
const Store = require("electron-store")

const store = new Store();

const preferredColorFormat = store.get("preferredColorFormat", "hex")
const preferredTexture = store.get("preferredTexture", "texture")

ipcMain.on('upload-cap', (event, arg) => {
	const file = dialog.showOpenDialogSync(null, {
		properties: ['openFile'],
		filters: [
			{ name: 'Ballcap Files', extensions: ['cap'] }
		]
	})
	event.sender.send('upload-cap-response', JSON.stringify(JSON.parse(fs.readFileSync(file[0]).toString())))
})

ipcMain.on("upload-image", (event, arg) => {
	let json = {}
	dialog.showOpenDialog(null, {
		properties: ['openFile'],
		filters: [
			{ name: 'Images', extensions: ['jpg', 'png', 'gif'] }
		]
	  }).then(result => {
		  if(!result.canceled) {
			Jimp.read(result.filePaths[0], (err, image) => {
				if (err) {
					console.log(err);
				} else {
					image.getBase64(Jimp.AUTO, (err, ret) => {
						json.filename = path.basename(result.filePaths[0])
						json.image = ret
						event.sender.send('upload-image-response', json);
					})
				}
			});
		  }
	  }).catch(err => {
		console.log(err)
	  })
})

ipcMain.on('save-cap', (event, arg) => {
	const buffer = Buffer.from(arg.imgdata.replace(/^data:image\/(png|gif|jpeg);base64,/,''), 'base64');
	const json = Buffer.from(arg.canvas, 'utf-8')

	const output = fs.createWriteStream(tempDir + '/'+arg.name+'.zip');

	output.on('close', function() {
		var data = fs.readFileSync(tempDir + '/'+arg.name+'.zip');
		var saveOptions = {
			defaultPath: app.getPath('downloads') + '/' + arg.name+'.zip',
		}
		dialog.showSaveDialog(null, saveOptions).then((result) => { 
			if (!result.canceled) {
			fs.writeFile(result.filePath, data, function(err) {
				if (err) {
				//res.end("success")
				fs.unlink(tempDir + '/'+arg.name+'.zip', (err) => {
					if (err) {
					console.error(err)
					return
					}
				})
				//res.end("success")
				} else {
				fs.unlink(tempDir + '/'+arg.name+'.zip', (err) => {
					if (err) {
					console.error(err)
					return
					}
				})
				//res.end("success")
				};
			})
			} else {
			fs.unlink(tempDir + '/'+arg.name+'.zip', (err) => {
				if (err) {
				console.error(err)
				return
				}
			})
			//res.end("success");
			}
		})
	});

	const archive = archiver('zip', {
		lib: { level: 9 } // Sets the compression level.
	});
		
	archive.on('error', function(err) {
		throw err;
	});

	archive.pipe(output)
	
	Jimp.read(buffer, (err, fir_img) => {
		if(err) {
			console.log(err);
		} else {
			var watermark = fs.readFileSync(__dirname + "/images/cm_watermark.png", {encoding: 'base64'});
			var buffer = Buffer.from(watermark, 'base64');
				Jimp.read(buffer, (err, sec_img) => {
					if(err) {
						console.log(err);
					} else {
						fir_img.composite(sec_img, 0, 0);
						fir_img.getBuffer(Jimp.MIME_PNG, (err, buffer) => {
							const finalImage = Buffer.from(buffer);
							archive.append(finalImage, {name: arg.name+".png"})
							archive.append(json, {name: arg.name+".cap"})
							archive.finalize()
							});
						
					}
				})
			}
		}); 
});

ipcMain.on('remove-border', (event, arg) => {
	var buffer = Buffer.from(arg.imgdata.replace(/^data:image\/(png|gif|jpeg);base64,/,''), 'base64');
	var fuzz = parseInt(arg.fuzz);
	Jimp.read(buffer, (err, image) => {
		if (err) {
			console.log(err);
		} else {
			image.autocrop()
			image.getBase64(Jimp.AUTO, (err, ret) => {
				event.sender.send('imagemagick-response', ret)
			})
		}
	})
})

ipcMain.on('replace-color', (event, arg) => {	
	var buffer = Buffer.from(arg.replace(/^data:image\/(png|gif|jpeg);base64,/,''), 'base64');
	Jimp.read(buffer, (err, image) => {
		if (err) {
			console.log(err);
		} else {
			image.getBase64(Jimp.AUTO, (err, ret) => {
				event.sender.send('imagemagick-response', ret)
			})
		}
	})
})

ipcMain.on("custom-font", (event, arg) => {
	let json = {}
	dialog.showOpenDialog(null, {
		properties: ['openFile'],
		filters: [
			{ name: 'Fonts', extensions: ['ttf', 'otf'] }
		]
	}).then(result => {
		if(!result.canceled) {
			ttfInfo(result.filePaths[0], function(err, info) {
			var ext = getExtension(result.filePaths[0])
				const dataUrl = ""
				var fontPath = url.pathToFileURL(tempDir + '/'+path.basename(result.filePaths[0]))
				fs.copyFile(result.filePaths[0], tempDir + '/'+path.basename(result.filePaths[0]), (err) => {
					if (err) {
						console.log(err)
					} else {
						json.fontName = info.tables.name[1],
						json.fontStyle = info.tables.name[2],
						json.familyName = info.tables.name[6],
						json.fontFormat = ext,
						json.fontMimetype = 'font/' + ext,
						json.fontData = fontPath.href,
						json.fontBase64 = dataUrl
						event.sender.send('custom-font-response', json)
					}
				})
			});
		}
	}).catch(err => {
		console.log(err)
	})
})

ipcMain.on('set-preference', (event, arg) => {
	const pref = arg.pref;
	const val = arg.val;
	store.set(pref, val)
});

function getExtension(filename) {
	var ext = path.extname(filename||'').split('.');
	return ext[ext.length - 1];
  }

const port = 8081;

function createWindow () {
  const mainWindow = new BrowserWindow({
    width: 1280,
    height: 760,
	icon: (__dirname + '/images/ballcap.png'),
    webPreferences: {
		nodeIntegration: true,
	  	contextIsolation: false 
    }
  })
  
  const template = [
	...(isMac ? [{
		label: app.name,
		submenu: [
		{ role: 'about' },
		{ type: 'separator' },
		{ role: 'services' },
		{ type: 'separator' },
		{ role: 'hide' },
		{ role: 'hideOthers' },
		{ role: 'unhide' },
		{ type: 'separator' },
		{ role: 'quit' }
		]
	}] : []),
	{
		label: 'File',
		submenu: [
		{
			click: () => mainWindow.webContents.send('load-cap','click'),
			accelerator: process.platform === 'darwin' ? 'Cmd+L' : 'Control+L',
			label: 'Load Cap',
		},
		{
			click: () => mainWindow.webContents.send('save-cap','click'),
			accelerator: process.platform === 'darwin' ? 'Cmd+S' : 'Control+S',
			label: 'Save Cap',
		},
		isMac ? { role: 'close' } : { role: 'quit' }
		]
	},
	{
		label: 'View',
		submenu: [
		{ role: 'reload' },
		{ role: 'forceReload' },
		{ role: 'toggleDevTools' },
		{ type: 'separator' },
		{ role: 'resetZoom' },
		{ role: 'zoomIn' },
		{ role: 'zoomOut' },
		{ type: 'separator' },
		{ role: 'togglefullscreen' }
		]
	},
	{
		label: 'About',
		submenu: [
		{
			click: () => mainWindow.webContents.send('about','click'),
				label: 'About the OOTP Cap Maker',
		},
		{
			label: 'About OOTP Baseball',
			click: async () => {    
			await shell.openExternal('https://www.ootpdevelopments.com/out-of-the-park-baseball-home/')
			}
		},
		{
			label: 'About Node.js',
			click: async () => {    
			await shell.openExternal('https://nodejs.org/en/about/')
			}
		},
		{
			label: 'About Electron',
			click: async () => {
			await shell.openExternal('https://electronjs.org')
			}
		},
		{
			label: 'View project on GitHub',
			click: async () => {
			await shell.openExternal('https://github.com/eriqjaffe/OOTP-Cap-Maker')
			}
		}
		]
	}
	]
	
	const menu = Menu.buildFromTemplate(template)
	Menu.setApplicationMenu(menu)

  mainWindow.loadURL(`file://${__dirname}/index.html?preferredColorFormat=${preferredColorFormat}&preferredTexture=${preferredTexture}`);

  mainWindow.webContents.setWindowOpenHandler(({ url }) => {
	shell.openExternal(url);
	return { action: 'deny' };
  });

  // Open the DevTools.
  // mainWindow.maximize()
  // mainWindow.webContents.openDevTools()
}

app.whenReady().then(() => {
  	createWindow()

  	app.on('activate', function () {
    	if (BrowserWindow.getAllWindows().length === 0) createWindow()
  	})
})

app.on('window-all-closed', function () {
  if (process.platform !== 'darwin') app.quit()
})