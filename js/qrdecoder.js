function showScanner() {

  var isCNA = !(Modernizr.pagevisibility && Modernizr.sessionstorage && Modernizr.localstorage && Modernizr.hashchange);

  var promisifiedOldGUM = function (constraints, successCallback, errorCallback) {
    // First get ahold of getUserMedia, if present
    var getUserMedia = (navigator.getUserMedia ||
      navigator.webkitGetUserMedia ||
      navigator.mozGetUserMedia);

    // Some browsers just don't implement it - return a rejected promise with an error
    // to keep a consistent interface
    if (!getUserMedia) {
      return Promise.reject(new Error('Feature is not implemented in this browser<br/>Please access page via Chrome/Safari/Mozilla browser<br/>or type in the 4-digits code manually'));
    }

    // Otherwise, wrap the call to the old navigator.getUserMedia with a Promise
    return new Promise(function (successCallback, errorCallback) {
      getUserMedia.call(navigator, constraints, successCallback, errorCallback);
    });

  }

  if (isCNA) {
    var error = document.getElementById("qrerror");
    error.style.display = "block";
    error.innerHTML = 'Feature can\'t be accessed through Captive Portal<br/>Please access page via Chrome/Safari/Mozilla browser<br/>or type in the 4-digits code manually';
  }
  else {
    // Older browsers might not implement mediaDevices at all, so we set an empty object first
    if (navigator.mediaDevices === undefined) {
      navigator.mediaDevices = {};
    }

    // Some browsers partially implement mediaDevices. We can't just assign an object
    // with getUserMedia as it would overwrite existing properties.
    // Here, we will just add the getUserMedia property if it's missing.
    if (navigator.mediaDevices.getUserMedia === undefined) {
      navigator.mediaDevices.getUserMedia = promisifiedOldGUM;
    }

    /* Ask for "environment" (rear) camera if available (mobile), will fallback to only available otherwise (desktop).
     * User will be prompted if (s)he allows camera to be started */

    document.getElementById("qrerror").innerHTML = "";
    navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" }, audio: false }).then(function (stream) {
      var video = document.getElementById("video-preview");
      video.style.display = "block";
      video.srcObject = stream;
      video.setAttribute("playsinline", true); /* otherwise iOS safari starts fullscreen */
      video.play();
      setTimeout(tick, 100); /* We launch the tick function 100ms later (see next step) */
    })
      .catch(function (err) {
        console.log(err); /* User probably refused to grant access*/
        var error = document.getElementById("qrerror");
        error.style.display = "block";
        error.innerHTML = `${err.name}</br>${err.message}`;
      });
  }
};

function tick() {
  var video = document.getElementById("video-preview");
  var qrCanvasElement = document.getElementById("qr-canvas");
  var qrCanvas = qrCanvasElement.getContext("2d");
  var input = document.getElementById("code");

  if (video.readyState === video.HAVE_ENOUGH_DATA) {
    qrCanvasElement.height = video.videoHeight;
    qrCanvasElement.width = video.videoWidth;
    qrCanvas.drawImage(video, 0, 0, qrCanvasElement.width, qrCanvasElement.height);
    try {
      input.value = qrcode.decode();

      /* Video can now be stopped */
      video.pause();
      video.src = "";
      video.srcObject.getVideoTracks().forEach(track => track.stop());

      /* Display Canvas and hide video stream */
      qrCanvasElement.classList.remove("hidden");
      video.classList.add("hidden");
      video.style.display = "none";
    } catch (e) {
      /* No Op */
      console.log(e);
    }
  }

  /* If no QR could be decoded from image copied in canvas */
  if (!video.classList.contains("hidden"))
    setTimeout(tick, 100);
}
