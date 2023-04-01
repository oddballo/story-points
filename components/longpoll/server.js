let http = require('http');
let url = require('node:url');
let querystring = require('querystring');

let subscribers = Object.create(null);

function onSubscribe(topic, req, res) {
  let id = Math.random();

  res.setHeader('Content-Type', 'text/plain;charset=utf-8');
  res.setHeader("Cache-Control", "no-cache, must-revalidate");

  subscribers[id] = {"res":res, "topic":topic};

  req.on('close', function() {
    delete subscribers[id];
  });

}

function publish(body) {

  let data = querystring.parse(body);
  if (!data.topic){
    return;
  }
  
  for (let id in subscribers) {
    if (! subscribers[id].topic || subscribers[id].topic != data.topic ){
        continue;
    }
    let res = subscribers[id].res;
    res.end("nudge");
    delete subscribers[id];
  }
}

function accept(req, res) {
  let aurl = url.parse(req.url, true);
  let topic = aurl.query.topic;
  let pathname = aurl.pathname;

  // Subscribe
  if (pathname == '/subscribe' && topic && topic != "") {
    onSubscribe(topic, req, res);
    return;
  }

  // Publish
  if (pathname == '/publish' && req.method == 'POST') {
    // accept POST
    req.setEncoding('utf8');
    let message = '';
    req.on('data', function(chunk) {
      message += chunk;
    }).on('end', function() {
      publish(message); // publish it to everyone
      res.end("ok");
    });

    return;
  }

  res.end();
}

function close() {
  for (let id in subscribers) {
    let res = subscribers[id];
    res.end();
  }
}

if (!module.parent) {
  http.createServer(accept).listen(8081);
  console.log('Server running on port 8081');
} else {
  exports.accept = accept;

  if (process.send) {
     process.on('message', (msg) => {
       if (msg === 'shutdown') {
         close();
       }
     });
  }

  process.on('SIGINT', close);
}
