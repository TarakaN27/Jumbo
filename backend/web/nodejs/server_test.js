/**
 * Created by Yauheni Motuz on 22.1.16.
 */
var app = require('express')();
var server = require('http').Server(app);
var io = require('socket.io')(server);
var redis = require('redis');
server.listen(8889); //порт на котором работает nodejs
io.on('connection', function (socket) {
    var redisClient = redis.createClient();

    redisClient.subscribe('notification_test');  //подписываемся на канал redis

    redisClient.on("message", function(channel, message) {
        socket.emit(channel, message);
    });

    socket.on('disconnect', function() {
        redisClient.quit();
    });

});