console.log(`debug mqtt/index.js `);

var websocketclient;

function init(res) {
  websocketclient = {
    client: null,
    lastMessageId: 1,
    lastSubId: 1,
    subscriptions: [],
    messages: [],
    connected: false,
  
    connect: function () {
      var host = "mqtt.netpie.io";
      var port = 443;
      var clientId = res.clientid;
      var username = res.token;
      var password = res.secret;
      var keepAlive = 60;
      var cleanSession = true;
      var lwTopic = "";
      var lwQos = 0;
      var lwRetain = false;
      var lwMessage = "";
      var ssl = true;
  
      this.client = new Messaging.Client(host, port, clientId);
      this.client.onConnectionLost = this.onConnectionLost;
      this.client.onMessageArrived = this.onMessageArrived;
  
      var options = {
        timeout: 3,
        keepAliveInterval: keepAlive,
        cleanSession: cleanSession,
        useSSL: ssl,
        onSuccess: this.onConnect,
        onFailure: this.onFail,
      };
  
      if (username.length > 0) {
        options.userName = username;
      }
      if (password.length > 0) {
        options.password = password;
      }
      if (lwTopic.length > 0) {
        var willmsg = new Messaging.Message(lwMessage);
        willmsg.qos = lwQos;
        willmsg.destinationName = lwTopic;
        willmsg.retained = lwRetain;
        options.willMessage = willmsg;
      }
  
      this.client.connect(options);
    },
  
    onConnect: function () {
      websocketclient.connected = true;
      console.log("connected");
    },
  
    onFail: function (message) {
      websocketclient.connected = false;
      console.log("error: " + message.errorMessage);
    },
  
    onConnectionLost: function (responseObject) {
      websocketclient.connected = false;
      if (responseObject.errorCode !== 0) {
        console.log("onConnectionLost:" + responseObject.errorMessage);
      }
    },
  
    onMessageArrived: function (message) {
      //        console.log("onMessageArrived:" + message.payloadString + " qos: " + message.qos);
  
      var subscription = websocketclient.getSubscriptionForTopic(message.destinationName);
  
      var messageObj = {
        topic: message.destinationName,
        retained: message.retained,
        qos: message.qos,
        payload: message.payloadString,
        timestamp: moment(),
        subscriptionId: subscription.id,
        color: websocketclient.getColorForSubscription(subscription.id),
      };
  
      console.log(messageObj);
    },
  
    disconnect: function () {
      this.client.disconnect();
    },
  
    publish: function (topic, payload, qos, retain) {
      if (!websocketclient.connected) {
        return false;
      }
  
      var message = new Messaging.Message(payload);
      message.destinationName = topic;
      message.qos = qos;
      message.retained = retain;
      this.client.send(message);
    },
  
    subscribe: function (topic, qosNr, color) {
      if (!websocketclient.connected) {
        return false;
      }
  
      if (topic.length < 1) {
        return false;
      }
  
      if (_.find(this.subscriptions, { topic: topic })) {
        return false;
      }
  
      this.client.subscribe(topic, { qos: qosNr });
      if (color.length < 1) {
        color = "999999";
      }
  
      var subscription = { topic: topic, qos: qosNr, color: color };
  
      this.subscriptions.push(subscription);
      return true;
    },
  
    unsubscribe: function (id) {
      var subs = _.find(websocketclient.subscriptions, { id: id });
      this.client.unsubscribe(subs.topic);
      websocketclient.subscriptions = _.filter(websocketclient.subscriptions, function (item) {
        return item.id != id;
      });
    },
  
    deleteSubscription: function (id) {
      var elem = $("#sub" + id);
  
      if (confirm("Are you sure ?")) {
        elem.remove();
        this.unsubscribe(id);
      }
    },
  
    getRandomColor: function () {
      var r = Math.round(Math.random() * 255).toString(16);
      var g = Math.round(Math.random() * 255).toString(16);
      var b = Math.round(Math.random() * 255).toString(16);
      return r + g + b;
    },
  
    getSubscriptionForTopic: function (topic) {
      var i;
      for (i = 0; i < this.subscriptions.length; i++) {
        if (this.compareTopics(topic, this.subscriptions[i].topic)) {
          return this.subscriptions[i];
        }
      }
      return false;
    },
  
    getColorForPublishTopic: function (topic) {
      var id = this.getSubscriptionForTopic(topic);
      return this.getColorForSubscription(id);
    },
  
    getColorForSubscription: function (id) {
      try {
        if (!id) {
          return "99999";
        }
  
        var sub = _.find(this.subscriptions, { id: id });
        if (!sub) {
          return "999999";
        } else {
          return sub.color;
        }
      } catch (e) {
        return "999999";
      }
    },
  
    compareTopics: function (topic, subTopic) {
      var pattern = subTopic.replace("+", "(.*?)").replace("#", "(.*)");
      var regex = new RegExp("^" + pattern + "$");
      return regex.test(topic);
    },
  };
  
  websocketclient.connect();
}

var xmlhttp = new XMLHttpRequest();
xmlhttp.open("GET", "http://wp-dev-http:81/wp-json/iot-interact/v1/mqtt-config");
xmlhttp.onload = function () {
  var res = xmlhttp.response;
  res = JSON.parse(res);
  // console.log(res);

  init(res);
};
xmlhttp.send(null);


