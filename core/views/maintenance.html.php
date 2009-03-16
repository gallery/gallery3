<html>
  <head>
    <title>
      <?= t("Gallery - Maintenance Mode") ?>
    </title>
    <style>
      body {
        background: #ccc;
      }
      form {
        border: 1px solid #555;
        background: #999;
        width: 300px;
      }
      fieldset {
        border: none;
      }
      fieldset legend {
        font-size: 24px;
        display: none !important;
        padding-left: 0px;
      }
      ul {
        list-style-type: none;
        margin-top: 0px;
        padding-left: 0px;
        bullet-style: none;
      }
      ul li {
        margin-left: 0px;
      }
      label {
        width: 60px;
        display: block;
      }
    </style>
  </head>
  <body>
    <h1>
      <?= t("Gallery - Maintenance Mode") ?>
    </h1>
    <p>
      <?= t("This site is currently only accessible by site administrators.") ?>
    </p>
    <?= user::get_login_form("login/auth_html") ?>
  </body>
</html>


