const http = require('http');
const fs = require('fs');
const path = require('path');

const root = __dirname;
const port = 8787;

const mimeTypes = {
  html: 'text/html',
  json: 'application/json',
  jpg: 'image/jpeg',
  png: 'image/png',
  svg: 'image/svg+xml',
  mp3: 'audio/mpeg',
};

http.createServer((req, res) => {
  const urlPath = req.url.split('?')[0];
  const filePath = path.join(root, decodeURIComponent(urlPath === '/' ? '/index.html' : urlPath));

  if (!filePath.startsWith(root + path.sep) && filePath !== root) {
    res.writeHead(403);
    res.end('Forbidden');
    return;
  }

  fs.readFile(filePath, (err, data) => {
    if (err) {
      res.writeHead(404);
      res.end('Not found');
      return;
    }
    const ext = filePath.split('.').pop();
    res.writeHead(200, { 'Content-Type': mimeTypes[ext] || 'text/plain' });
    res.end(data);
  });
}).listen(port, () => {
  console.log(`RetroRaten dev server running at http://localhost:${port}`);
});
