const { SocketAddress } = require("net");

const httpServer = require("http").createServer();
const io = require("socket.io")(httpServer, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"],
    credentials: true,
  },
});
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
  });
  socket.on("loggedOut", function (user) {
    socket.leave(user.username);
    if (user.user_type == "A") {
      socket.leave("administrator");
    } else {
      socket.leave("vcard");
    }
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
  socket.on("moneySent", function ({ receiver, sender, amount }) {
    socket.in(receiver).emit("moneySentNotification", { sender, amount });
  });

  socket.on("blocked", function ({ user }) {
    socket.in(user).emit("blockedNotification", { user });
  });

  socket.on("changedStatus", function ({ user, status }) {
    socket.in("administrator").emit("changedStatusNotification", { user, status });
  });

  socket.on("requestMoney", function ({ receiver, sender, amount }) {
    console.log("Boas mano: " + receiver, sender, amount);
    socket.to(sender).emit("requestMoneyNotification", { receiver, amount });
  });
});

