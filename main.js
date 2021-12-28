// main.js

// Modules to control application life and create native browser window
const { app, BrowserWindow, dialog, Menu, shell } = require('electron')
const path = require('path')
const express = require('express');
const Jimp = require('jimp');
const imagemagickCli = require('imagemagick-cli');
const imgur = require('imgur');
const os = require('os');
const tempDir = os.tmpdir()
const fs = require('fs')
const app2 = express();
const ttfInfo = require('ttfinfo');
const url = require('url');
const isMac = process.platform === 'darwin'
const watermark = "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gAAAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAQAAAAAAgAAAAAAABAAAAAAgAAAAAQAAAAAAAAgAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAEAAAAAAAAIAAAAAEAAAAACAAAAAAAAEAAAAACAAAAAAAAEAAACAAAAAAAAEAAAAACAAAAAAAAEAAAAACAAAAABAAAAAAAACAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAQAAAAAAAAgAAAAAQAAAAAIAAAAAAQAAAAAAAAgAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAEAAAAAAAAIAAAAAEAAAAACAAAAAAAAEAAAAACAAAAAAAAEAAAAACAAAAABAAAAAACAAAAAAAAEAAAAACAAAAABAAAAAAAACAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAQAAAAAAAAgAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAEAAAAAAAAIAAAAAEAAAAACAAAAAAAAEAAAAACAAAAAAAAEAAAAACAAAAABAAAAAAAACAAAAABAAAAAACAAAAABAAAAAAAACAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAQAAAAAAAAgAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAEAAAAAAIAAAAAAAAQAAAAAIAAAAAEAAAAAAAAIAAAAAEAAAAACAAAAAAAAEAAAAACAAAAAAAAEAAAAACAAAAABAAAAAAAACAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAQAAAAAAAAgAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAEAAAAAAAAIAAAAAEAAAAAAIAAAAAEAAAAAAAAIAAAAAEAAAAACAAAAAAAAEAAAAACAAAAAAAAEAAAAACAAAAABAAAAAAAACAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAQAAAAAAAAgAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAEAAAAAAAAIAAAAAEAAAAACAAAAAAAAEAAAAAAIAAAAAEAAAAACAAAAAAAAEAAAAACAAAAAAAAEAAAAACAAAAABAAAAAAAACAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAQAAAAAAAAgAAAAAQAAAAAAgAAAAAQAAAAAAAAgAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAEAAAAAAAAIAAAAAEAAAAACAAAAAAAAEAAAAACAAAAAAAAEAAAAACAAAAAAEAAAAACAAAAAAAAEAAAAACAAAAABAAAAAAAACAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAQAAAAAAAAgAAAAAQAAAAAIAAAAAAAAQAAAAAAgAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAEAAAAAAAAIAAAAAEAAAAACAAAAAAAAEAAAAACAAAAAAAAEAAAAACAAAAABAAAAAAAACAAAAAAEAAAAACAAAAABAAAAAAAACAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAQAAAAAAAAgAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAAAAQAAAIAAAAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAEAAAAAAAAIAAAAAEAAAAACAAAAAAAAEAAAAACAAAAAAAAEAAAAACAAAAABAAAAAAAACAAAAABAAAAAAgAAAAAAABAAAAAACAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAQAAAAAAAAgAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAEAAAAAAAAIAAAAAAQAAAAAIAAAAAEAAAAAAAAIAAAAAEAAAAACAAAAAAAAEAAAAACAAAAAAAAEAAAAACAAAAABAAAAAAAACAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAAABAAAAgAAAAAAABAAAAAAgAAAAAAABAAAAAAgAAAAAQAAAAAAAAgAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAAAAQAAAAAIAAAAAEAAAAAAAAIAAAAAEAAAAACAAAD4/+zWgQAAAACAIH/rFQYoigAEAAAQAABAAAAAAQAABAAAEAAAQAAAAAEAAAQAABAAAEAAAAABAAAEAAAQAADgSwABBgAzPeRWWKFOagAAAABJRU5ErkJggg==";

const template = [
// { role: 'appMenu' }
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
// { role: 'fileMenu' }
{
	label: 'File',
	submenu: [
	isMac ? { role: 'close' } : { role: 'quit' }
	]
},
// { role: 'viewMenu' }
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
// { role: 'windowMenu' }
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
		await shell.openExternal('https://github.com/eriqjaffe/capmaker')
		}
	}
	]
}
]

const menu = Menu.buildFromTemplate(template)
Menu.setApplicationMenu(menu)

app2.use(express.urlencoded({limit: '50mb', extended: true, parameterLimit: 50000}));

app2.get("/uploadImage", (req, res) => {
	dialog.showOpenDialog(null, {
		properties: ['openFile'],
		filters: [
			{ name: 'Images', extensions: ['jpg', 'png', 'gif'] }
		]
	  }).then(result => {
		  if(!result.canceled) {
			console.log(result.filePaths)
			Jimp.read(result.filePaths[0], (err, image) => {
				if (err) {
					console.log(err);
				} else {
					image.getBase64(Jimp.AUTO, (err, ret) => {
						console.log(ret);
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

app2.post("/imgur", (req, res)  => {
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
})

app2.post('/savecap', (req, res) => {
	var buffer = Buffer.from(req.body.imgdata.replace(/^data:image\/(png|gif|jpeg);base64,/,''), 'base64');

	const options = {
		defaultPath: app.getPath('desktop') + '/' + req.body.name,
	}


	
	dialog.showSaveDialog(null, options).then((result) => {
		if (!result.canceled) {
			Jimp.read(buffer, (err, fir_img) => {
			if(err) {
				console.log(err);
			} else {
				var buffer = Buffer.from(watermark, 'base64');
					Jimp.read(buffer, (err, sec_img) => {
						if(err) {
							console.log(err);
						} else {
							fir_img.composite(sec_img, 0, 0);
							fir_img.getBuffer(Jimp.MIME_PNG, (err, buffer) => {
								const finalImage = Buffer.from(buffer).toString('base64');
								fs.writeFile(result.filePath, finalImage, 'base64', function(err) {
									console.log(err);
								});
							  });
							
						}
					})
				}
			});
		} 
	}).catch((err) => {
		console.log(err);
	});
});

app2.post("/removeBorder", (req, res) => {
	var buffer = Buffer.from(req.body.imgdata.replace(/^data:image\/(png|gif|jpeg);base64,/,''), 'base64');
	var fuzz = parseInt(req.body.fuzz);
	Jimp.read(buffer, (err, image) => {
		if (err) {
			console.log(err);
		} else {
			image.write("temp.png");
			imagemagickCli.exec('magick convert -trim -fuzz '+fuzz+'% temp.png temp.png').then(({ stdout, stderr }) => {
				Jimp.read("temp.png", (err, image) => {
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
			image.write("temp.png");
			if (action == "replaceColorRange") {
				cmdString = 'magick convert temp.png -fuzz '+fuzz+'% -fill '+newcolor+' -draw "color '+x+','+y+' floodfill" temp.png';		
			} else {
				cmdString = 'magick convert temp.png -fuzz 50% -fill '+newcolor+' -opaque '+color+' temp.png';	
			}
			imagemagickCli.exec(cmdString).then(({ stdout, stderr }) => {
				Jimp.read("temp.png", (err, image) => {
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
			image.write("temp.png", (err) => {
				imagemagickCli.exec('magick convert temp.png -fuzz '+fuzz+'% -fill none -draw "color '+x+','+y+' floodfill" temp.png').then(({ stdout, stderr }) => {
					Jimp.read("temp.png", (err, image) => {
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
			image.write("temp.png", (err) => {
				var cmdString = 'magick convert temp.png -fuzz '+fuzz+'% -transparent '+color+' temp.png';
				console.log(cmdString);
				imagemagickCli.exec(cmdString).then(({ stdout, stderr }) => {
					Jimp.read("temp.png", (err, image) => {
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
				//var buff = fs.readFileSync(result.filePaths[0]);
				//console.log(tempDir)
				var fontPath = url.pathToFileURL(tempDir + '/'+path.basename(result.filePaths[0]))
				//console.log(fontPath.href)
				fs.copyFile(result.filePaths[0], tempDir + '/'+path.basename(result.filePaths[0]), (err) => {
				//fs.copyFile(result.filePaths[0], path.join(app.getAppPath(), 'resources', 'app', 'fonts', path.basename(result.filePaths[0])), (err) => {
					if (err) {
						console.log(err)
					} else {
						res.json({
							"fontName": info.tables.name[1],
							"fontStyle": info.tables.name[2],
							"familyName": info.tables.name[6],
							"fontFormat": ext,
							"fontMimetype": 'font/' + ext,
							"fontData": fontPath.href
						});
						res.end()
					}
				})
				/* fs.writeFile(__dirname + '/fonts/'+path.basename(result.filePaths[0]), buff, function (err) {
					if (err) return console.log(err);
					res.json({
						"fontName": info.tables.name[1],
						"fontStyle": info.tables.name[2],
						"familyName": info.tables.name[6],
						"fontFormat": ext,
						"fontMimetype": 'font/' + ext,
						"fontData": 'data:'+'font/' + ext+';charset=ascii;base64,' + buff.toString('base64')
					});
				  });
				
			res.end() */
			});
		}
	}).catch(err => {
		console.log(err)
	})
})

function getExtension(filename) {
	var ext = path.extname(filename||'').split('.');
	return ext[ext.length - 1];
  }

const port = 8080;

app2.listen(port, () => {
  console.log(`Server running on port ${port}`);
});



function createWindow () {
  // Create the browser window.
  const mainWindow = new BrowserWindow({
    width: 1280,
    height: 760,
	icon: (__dirname + '/images/ballcap.png'),
    webPreferences: {
      preload: path.join(__dirname, 'preload.js')
    }
  })

  // and load the index.html of the app.
  mainWindow.loadFile('index.html')

  mainWindow.webContents.on('new-window', function(e, url) {
	e.preventDefault();
	require('electron').shell.openExternal(url);
  });

  // Open the DevTools.
  // mainWindow.webContents.openDevTools()
}

// This method will be called when Electron has finished
// initialization and is ready to create browser windows.
// Some APIs can only be used after this event occurs.
app.whenReady().then(() => {
  createWindow()

  app.on('activate', function () {
    // On macOS it's common to re-create a window in the app when the
    // dock icon is clicked and there are no other windows open.
    if (BrowserWindow.getAllWindows().length === 0) createWindow()
  })
})

// Quit when all windows are closed, except on macOS. There, it's common
// for applications and their menu bar to stay active until the user quits
// explicitly with Cmd + Q.
app.on('window-all-closed', function () {
  if (process.platform !== 'darwin') app.quit()
})

// In this file you can include the rest of your app's specific main process
// code. You can also put them in separate files and require them here.