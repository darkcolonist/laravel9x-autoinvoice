<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{env("APP_NAME").(env("APP_ENV")!=="production"?" [".env("APP_ENV")."]":"")}}</title>
</head>
<body>
  <div id="app"></div>
  <script> 
    var appToken = '{{csrf_token()}}';
    var appBaseURL = '{{URL::to('/')}}';
    var appConfig = {!!JSON_ENCODE(config("react"))!!};
  </script>
  <script src="{{mix("js/app.js")}}"></script>
</body>
</html>