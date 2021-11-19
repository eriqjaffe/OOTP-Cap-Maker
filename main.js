// main.js

// Modules to control application life and create native browser window
const { app, BrowserWindow, dialog } = require('electron')
const path = require('path')
const express = require('express');
const Jimp = require('jimp');
const imagemagickCli = require('imagemagick-cli');
const imgur = require('imgur');
const os  = require('os');
const fs = require('fs')
const app2 = express();

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
					Jimp.read('./images/cm_watermark.png', (err, sec_img) => {
						if(err) {
							console.log(err);
						} else {
							fir_img.composite(sec_img, 0, 0);
							fir_img.getBuffer(Jimp.MIME_PNG, (err, buffer) => {
								const finalImage = Buffer.from(buffer).toString('base64');
								require("fs").writeFile(result.filePath, finalImage, 'base64', function(err) {
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