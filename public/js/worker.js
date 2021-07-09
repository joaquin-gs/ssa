//--------------------------------------------------------------------
// This file is executed only once (when the Sharedworker is created).
//--------------------------------------------------------------------

// This array will contain objects in the format {user: <string>, port: <MessagePort>, tabURL: <string>}
// Such objects are the Tabs the user has opened using links/urls from the same domain.
// If another browser is opened with the same domain, another variable (array) will be created for that browser
// and will not include the user and the opened tabs of the first browser. This happens because the thread
// is not shared between different browsers.
let AllPorts = [];

// The connection to the WebSocket Server.
var socket = new WebSocket("ws://ssa:8090");

// Called when the WebSocket Server accepts the connection.
socket.onopen = function(e) {
   //
};

// Event handler fired when the WebSocket Server sends a message to this client.
socket.onmessage = function(e) {
   var message = JSON.parse(e.data);
   for (var i = 0; i < AllPorts.length; i++) {
      if (message.to.includes(AllPorts[i].user)) {
         AllPorts[i].port.postMessage(message);
      }
   }
};

socket.onclose = function(event) {
   if (event.wasClean) {
      console.log('Websocket closed normally');
   }
   else {
      console.log('Websocket closed unexpectedly.');
   }
};

socket.onerror = function(error) {
   console.log('Error on client websocket: ', error.message);
};



// This event is fired every time a browser tab connects to this Sharedworker.
onconnect = function(ev) {
   let port = ev.ports[0];

   // Event handler fired when a tab/window sends a message to this Sharedworker.
   port.onmessage = function(e) {
      let currentUser = e.data.username;
      var userIsConnected = false;

      switch (e.data.action) {
         case "connect":
            // Check if the received user name has at least one tab in AllPorts array.
            for (var j = 0; j < AllPorts.length; j++) {
               if (AllPorts[j].user == currentUser) {
                  userIsConnected = true;
               }
            }

            // Add new connected tab to AllPorts array.
            AllPorts.push({user: currentUser, port: port, tabURL: e.data.tab});

            if (!userIsConnected) {
               // User names are added to the user list in the WebSocket Server only once.
               setTimeout(() => {
                  socket.send(JSON.stringify({action: 'connect', username: currentUser}));
               }, 650);
            }
            break;

         case "close":
            // Find the current tab URL in AllPorts array.
            var index;
            for (var i = 0; i < AllPorts.length; i++) {
               if ((AllPorts[i].tabURL == e.data.tab) && (AllPorts[i].user == e.data.username)) {
                  index = i;
               }
            }
            AllPorts.splice(index, 1);
            userIsConnected = false;
            // Check if user has any other tab connected to the Sharedworker.
            for (var i = 0; i < AllPorts.length; i++) {
               if (AllPorts[i].user == e.data.username) {
                  userIsConnected = true;
               }
            }
            if (!userIsConnected) {
               // User doesn't have more tabs opened. Remove user from WebSocket Server.
               socket.send(JSON.stringify({action: 'disconnect', username: e.data.username}));
            }
            break;

         case "notify":
            socket.send(JSON.stringify({action: 'notify', to: currentUser, message: e.data.message}));
            break;

         case "list":
            socket.send(JSON.stringify({action: 'list', to: currentUser}));
            break;
         
         case "help":
            socket.send(JSON.stringify({action: 'help'}));
      } // switch
   } // port.onmessage

} // onconnect
