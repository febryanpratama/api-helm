<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RuangAjar - Meet</title>
  <script src='https://meet.jit.si/external_api.js'></script>
  <script>
    window.onload = () => {
      // Initialize
      const roomNameR   = document.getElementById('room-name').value
      const username    = document.getElementById('name-auth').value
      const emailAuth   = document.getElementById('email-auth').value
      const avatarAuth  = document.getElementById('avatar-auth').value
      const domain      = 'meet.jit.si';

      const options = {
          roomName: `${roomNameR}`,
          width: '100%',
          height: '100%',
          parentNode: document.querySelector('#meet'),
          lang: 'id',
          configOverwrite: { startWithAudioMuted: true },
          interfaceConfigOverwrite: { DISABLE_DOMINANT_SPEAKER_INDICATOR: true },
          userInfo: {
              email: emailAuth,
              displayName: username,
              avatarURL: avatarAuth
          }
      };

      const api = new JitsiMeetExternalAPI(domain, options);
    };
  </script>
</head>

<!-- Hidden Element -->
<input type="hidden" id="room-name" value="{{ $roomName }}">
<input type="hidden" id="name-auth" value="{{ auth()->user()->name }}">
<input type="hidden" id="email-auth" value="{{ auth()->user()->email }}">
<input type="hidden" id="avatar-auth" value="{{ (auth()->user()->avatar) ? auth()->user()->avatar : 'https://st4.depositphotos.com/4329009/19956/v/600/depositphotos_199564354-stock-illustration-creative-vector-illustration-default-avatar.jpg' }}">

<body style="margin: 0; padding: 0;">
  <div id="meet" style="width: 100%;  position: fixed;top: 0;left: 0;height: 100%;"></div>
</body>
</html>
