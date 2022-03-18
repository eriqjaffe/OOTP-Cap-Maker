// main.js

// Modules to control application life and create native browser window
const { app, BrowserWindow, dialog, Menu, shell, webContents  } = require('electron')
const path = require('path')
const express = require('express');
const Jimp = require('jimp');
const imagemagickCli = require('imagemagick-cli');
//const imgur = require('imgur');
const os = require('os');
const tempDir = os.tmpdir()
const fs = require('fs')
const app2 = express();
const ttfInfo = require('ttfinfo');
const url = require('url');
const isMac = process.platform === 'darwin'
const archiver = require('archiver')
const font2base64 = require("node-font2base64")
const Store = require("electron-store")

const server = app2.listen(0, () => {
	console.log(`Server running on port ${server.address().port}`);
});

const store = new Store();

const preferredColorFormat = store.get("preferredColorFormat", "hex")
const preferredTexture = store.get("preferredTexture", "texture")

app2.use(express.urlencoded({limit: '50mb', extended: true, parameterLimit: 50000}));

app2.get("/uploadCap", (req, res) => {
	const file = dialog.showOpenDialogSync(null, {
		properties: ['openFile'],
		filters: [
			{ name: 'Ballcap Files', extensions: ['cap'] }
		]
	})
	res.end(JSON.stringify(JSON.parse(fs.readFileSync(file[0]).toString())))
})

app2.get("/uploadImage", (req, res) => {
	dialog.showOpenDialog(null, {
		properties: ['openFile'],
		filters: [
			{ name: 'Images', extensions: ['jpg', 'png', 'gif'] }
		]
	  }).then(result => {
		  if(!result.canceled) {
			//console.log(result.filePaths)
			Jimp.read(result.filePaths[0], (err, image) => {
				if (err) {
					console.log(err);
				} else {
					image.getBase64(Jimp.AUTO, (err, ret) => {
						//console.log(ret);
						//res.json({
						//	"filename": "hello world",
						//	"image": res
						//  });
						res.end(ret);
					})
				}
			});
		  }
	  }).catch(err => {
		console.log(err)
	  })
})

/* app2.post("/imgur", (req, res)  => {
	imgur.setClientId('c9ff708b19a4996');
	imgur.setAPIUrl('https://api.imgur.com/3/');
	var buffer = Buffer.from(req.body.imgdata.replace(/^data:image\/(png|gif|jpeg);base64,/,''), 'base64');
	Jimp.read(buffer, (err, fir_img) => {
			if(err) {
				console.log(err);
			} else {
					Jimp.read('./images/cm_watermark.png', (err, sec_img) => {
						if(err) {
							console.log(err);
						} else {
							fir_img.composite(sec_img, 0, 0);
							fir_img.getBuffer(Jimp.MIME_PNG, (err, buffer) => {
								var finalImage = Buffer.from(buffer).toString('base64');
								//new Promise((resolve, reject) => {
									imgur
										.uploadBase64(finalImage.replace(/^data:image\/(png|gif|jpeg);base64,/,''))
										.then((json) => {
											console.log(json.link);
											return json.link;
										})
										.catch((err) => {
											console.error(err.message);
										});
									})
							  //});
							
						}
					})
				}
			});
}) */

app2.post('/savecap', (req, res) => {
	const buffer = Buffer.from(req.body.imgdata.replace(/^data:image\/(png|gif|jpeg);base64,/,''), 'base64');
	const json = Buffer.from(req.body.canvas, 'utf-8')

	const output = fs.createWriteStream(tempDir + '/'+req.body.name+'.zip');

	output.on('close', function() {
		var data = fs.readFileSync(tempDir + '/'+req.body.name+'.zip');
		var saveOptions = {
		  defaultPath: app.getPath('downloads') + '/' + req.body.name+'.zip',
		}
		dialog.showSaveDialog(null, saveOptions).then((result) => { 
		  if (!result.canceled) {
			fs.writeFile(result.filePath, data, function(err) {
			  if (err) {
				res.end("success")
				fs.unlink(tempDir + '/'+req.body.name+'.zip', (err) => {
				  if (err) {
					console.error(err)
					return
				  }
				})
				res.end("success")
			  } else {
				fs.unlink(tempDir + '/'+req.body.name+'.zip', (err) => {
				  if (err) {
					console.error(err)
					return
				  }
				})
				res.end("success")
			  };
			})
		  } else {
			fs.unlink(tempDir + '/'+req.body.name+'.zip', (err) => {
			  if (err) {
				console.error(err)
				return
			  }
			})
			res.end("success");
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
							archive.append(finalImage, {name: req.body.name+".png"})
							archive.append(json, {name: req.body.name+".cap"})
							archive.finalize()
							});
						
					}
				})
			}
		}); 
});

app2.post("/removeBorder", (req, res) => {
	var buffer = Buffer.from(req.body.imgdata.replace(/^data:image\/(png|gif|jpeg);base64,/,''), 'base64');
	var fuzz = parseInt(req.body.fuzz);
	Jimp.read(buffer, (err, image) => {
		if (err) {
			console.log(err);
		} else {
			image.write(tempDir + '/temp.png');
			imagemagickCli.exec('magick convert -trim -fuzz '+fuzz+'% '+tempDir+'/temp.png '+tempDir+'/temp.png').then(({ stdout, stderr }) => {
				Jimp.read(tempDir + '/temp.png', (err, image) => {
					if (err) {
						console.log(err);
					} else {
						image.getBase64(Jimp.AUTO, (err, ret) => {
							res.end(ret);
						})
					}
				})
			})
		}
	})
})

app2.post("/replaceColor", (req, res) => {
	var buffer = Buffer.from(req.body.imgdata.replace(/^data:image\/(png|gif|jpeg);base64,/,''), 'base64');
	var x = parseInt(req.body.x);
	var y = parseInt(req.body.y);
	var color = req.body.color;
	var newcolor = req.body.newcolor;
	var action = req.body.action;
	var fuzz = parseInt(req.body.fuzz);
	var cmdString;
	Jimp.read(buffer, (err, image) => {
		if (err) {
			console.log(err);
		} else {
			image.write(tempDir + '/temp.png');
			if (action == "replaceColorRange") {
				cmdString = 'magick convert '+tempDir+'/temp.png -fuzz '+fuzz+'% -fill '+newcolor+' -draw "color '+x+','+y+' floodfill" '+tempDir+'/temp.png';		
			} else {
				cmdString = 'magick convert '+tempDir+'/temp.png -fuzz 50% -fill '+newcolor+' -opaque '+color+' '+tempDir+'/temp.png';	
			}
			imagemagickCli.exec(cmdString).then(({ stdout, stderr }) => {
				Jimp.read(tempDir + '/temp.png', (err, image) => {
					if (err) {
						console.log(err);
					} else {
						image.getBase64(Jimp.AUTO, (err, ret) => {
							res.end(ret);
						})
					}
				})
			})
		}
	})
})

app2.post("/removeColorRange", (req, res) => {
	var buffer = Buffer.from(req.body.imgdata.replace(/^data:image\/(png|gif|jpeg);base64,/,''), 'base64');
	var x = parseInt(req.body.x);
	var y = parseInt(req.body.y);
	var fuzz = parseInt(req.body.fuzz);
	Jimp.read(buffer, (err, image) => {
		if (err) {
			console.log(err);
		} else {
			image.write(tempDir + '/temp.png', (err) => {
				imagemagickCli.exec('magick convert '+tempDir+'/temp.png -fuzz '+fuzz+'% -fill none -draw "color '+x+','+y+' floodfill" '+tempDir+'/temp.png').then(({ stdout, stderr }) => {
					Jimp.read(tempDir + '/temp.png', (err, image) => {
						if (err) {
							console.log(err);
						} else {
							image.getBase64(Jimp.AUTO, (err, ret) => {
								res.end(ret);
							})
						}
					})
				})
			})
		}
 	})
})

app2.post('/removeAllColor', (req, res) => {
	var buffer = Buffer.from(req.body.imgdata.replace(/^data:image\/(png|gif|jpeg);base64,/,''), 'base64');
	var x = parseInt(req.body.x);
	var y = parseInt(req.body.y);
	var color = req.body.color;
	var fuzz = parseInt(req.body.fuzz);
	Jimp.read(buffer, (err, image) => {
		if (err) {
			console.log(err);		
		} else {
			image.write(tempDir + '/temp.png', (err) => {
				var cmdString = 'magick convert '+tempDir+'/temp.png -fuzz '+fuzz+'% -transparent '+color+' '+tempDir+'/temp.png';
				console.log(cmdString);
				imagemagickCli.exec(cmdString).then(({ stdout, stderr }) => {
					Jimp.read(tempDir + '/temp.png', (err, image) => {
						if (err) {
							console.log(err);
						} else {
							image.getBase64(Jimp.AUTO, (err, ret) => {
								res.end(ret);
							})
						}
					})
				})
			})
		}
	})
});

app2.get("/customFont", (req, res) => {
	dialog.showOpenDialog(null, {
		properties: ['openFile'],
		filters: [
			{ name: 'Fonts', extensions: ['ttf', 'otf'] }
		]
	}).then(result => {
		if(!result.canceled) {
			ttfInfo(result.filePaths[0], function(err, info) {
			var ext = getExtension(result.filePaths[0])
				const dataUrl = font2base64.encodeToDataUrlSync(result.filePaths[0])
				var fontPath = url.pathToFileURL(tempDir + '/'+path.basename(result.filePaths[0]))
				fs.copyFile(result.filePaths[0], tempDir + '/'+path.basename(result.filePaths[0]), (err) => {
					if (err) {
						console.log(err)
					} else {
						res.json({
							"fontName": info.tables.name[1],
							"fontStyle": info.tables.name[2],
							"familyName": info.tables.name[6],
							"fontFormat": ext,
							"fontMimetype": 'font/' + ext,
							"fontData": fontPath.href,
							"fontBase64": dataUrl
						});
						res.end()
					}
				})
			});
		}
	}).catch(err => {
		console.log(err)
	})
})

app2.post('/setPreference', (req, res) => {
	const pref = req.body.pref;
	const val = req.body.val;
	store.set(pref, val)
	res.end()
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
			label: 'Load Cap',
		},
		{
			click: () => mainWindow.webContents.send('save-cap','click'),
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
		label: 'Window',
		submenu: [
		{ role: 'minimize' },
		{ role: 'zoom' },
		...(isMac ? [
			{ type: 'separator' },
			{ role: 'front' },
			{ type: 'separator' },
			{ role: 'window' }
		] : [
			{ role: 'close' }
		])
		]
	},
	{
		role: 'help',
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

  mainWindow.loadURL(`file://${__dirname}/index.html?port=${server.address().port}&preferredColorFormat=${preferredColorFormat}&preferredTexture=${preferredTexture}`);

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