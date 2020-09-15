function receiveMessage(event)
{
  // Do we trust the sender of this message?  (might be
  // different from what we originally opened, for example).
  if (event.origin !== HTTP_ROOT_DIR) {
      return;
  } else {
      if (event.data == 'endVideochat') {
          if (location.href.indexOf('view.php') != -1) {
              $j('iframe.ada-videochat-embed').remove();
          } else if (location.href.indexOf('videochat.php') != -1) {
            closeMeAndReloadParent();
        }
      }
  }
}
window.addEventListener("message", receiveMessage, false);
