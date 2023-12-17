const { SocketAddress } = require("net");

const httpServer = require("http").createServer();
const io = require("socket.io")(httpServer, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"],
    credentials: true,
  },
});

const connectedSockets = new Map(); 

httpServer.listen(8080, () => {
  console.log("listening on *:8080");
});

io.on("connection", (socket) => {
  console.log(`client ${socket.id} has connected`);

  socket.on("loggedIn", function (user) {
    console.log(`client ${user.username} has logged in`);

    socket.join(user.username);
    if (user.user_type == "A") {
      socket.join("administrator");
    } else {
      socket.join("vcard");
    }
    connectedSockets.set(user.username, socket.id);
  });

  socket.on("loggedOut", function (user) {
    socket.leave(user.username);
    if (user.user_type == "A") {
      socket.leave("administrator");
    } else {
      socket.leave("vcard");
    }
    connectedSockets.delete(user.username);
  });

  // Admin Events
  socket.on("insertedUser", function (user) {
    socket.in("administrator").emit("insertedUser", user);
  });

  socket.on("updatedUser", function (user) {
    socket.in("administrator").except(user.username).emit("updatedUser", user);
    socket.in(user.username).emit("updatedUser", user);
  });
  socket.on("deletedUser", function (userID) {
    socket.in("administrator").emit("deletedUser", userID);
  });

  // VCard Events
  // No lado do servidor
  socket.on("moneySent", ({ receiver, sender, amount }) => {
    handleMoneySent(socket, receiver, sender, amount);
  });

  async function handleMoneySent(socket, receiver, sender, amount) {
    const receiverSocketId = connectedSockets.get(receiver);
  
    if (receiverSocketId) {
      socket.to(receiver).emit("moneySentNotification", { sender, amount });
    } else {
      socket.emit('receiverNotLoggedIn', { receiver, sender, amount });
    }
  }

  socket.on("blocked", function ({ user }) {
    socket.in(user).emit("blockedNotification", { user });
  });

  socket.on("changedStatus", function ({ user, status }) {
    socket.in("administrator").emit("changedStatusNotification", { user, status });
  });

  socket.on("requestMoney", function ({ receiver, sender, amount }) {
    socket.to(sender).emit("requestMoneyNotification", { receiver, amount });
  });

  socket.on("acceptMoney", function ({ receiver, sender, amount }) {
    socket.to(receiver).emit("acceptMoneyNotification", { sender, amount });
  });

  socket.on("rejectMoney", function ({ receiver, sender, amount, whoRejected }) {
    if(whoRejected == 'S'){
      socket.to(receiver).emit("rejectMoneyNotification", { sender, amount, whoRejected });
    }else{
      socket.to(sender).emit("rejectMoneyNotification", { receiver, amount, whoRejected });
    }
  });

  socket.on("disconnect", () => {
    console.log(`client ${socket.id} has disconnected`);
    // Remove the mapping when the socket disconnects
    connectedSockets.forEach((value, key) => {
      if (value === socket.id) {
        connectedSockets.delete(key);
      }
    });
  });
});

