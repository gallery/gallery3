<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript" src="http://api.recaptcha.net/js/recaptcha_ajax.js"></script>
<script>
var site = (document.location.protocol == "http:") ? "<?= $form->recaptcha_site ?>" : "<?= $form->recaptcha_ssl_site ?>";
var RecaptchaOptions = {lang: 'en', theme: "white"};

$("#gConfigureRecaptchaForm").ready(function() {
  $("#gConfigureRecaptchaForm :submit").before("<ul><li id=recaptcha_div /></ul>");
  $("#public_key").change(function() {
    showRecaptcha($(this).val());
  });
  var original = $("#public_key").val();
  if (original != "") {
    showRecaptcha(original);
  }
});

function showRecaptcha(public_key) {
  if (public_key != "") {
    Recaptcha.widget = document.getElementById("recaptcha_div");
    $.ajax({url: "<?= url::site("admin/recaptcha/gethtml") ?>/" + public_key <? if (!empty($form->captcha_error)): ?> + "/<?= $form->captcha_error ?>" <? endif ?> ,
          dataType: "json",
          cache: false,
          error: function(request, textStatus, errorThrown) {
            var public_key = $("#gConfigureRecaptchaForm ul li:first-child");
            public_key.addClass("gError");
            $("#gConfigureRecaptchaForm ul li:first-child p").replaceWith("");
            public_key.append('<p class="gError">' + request.responseText + "</p>");
          },
          success: function(data, textStatus) {
            var public_key = $("#gConfigureRecaptchaForm ul li:first-child");
            public_key.removeClass("gError");
            $("#gConfigureRecaptchaForm ul li:first-child p").replaceWith("");
            $("#recaptcha_div").html("<script type='text/javascript'>" + data.script + "</script" + ">");
          }
    });
  } else {
    if (Recaptcha.widget != undefined) {
      Recaptcha.destroy();
    }
  }
}

</script>
   
<div id="gAdminRecaptcha">
  <h1> <?= t("ReCaptcha Challenge Filtering") ?> </h1>
  <p>
    <?= t("Recaptcha is a free CAPTCHA service that helps to digitize books, newspapers and old time radio shows. automated spam filtering service.  In order to use it, you need to sign up for a <a href=\"{$form->get_key_url}\">ReCaptcha Public/Private Key pair</a>, which is also free.  Once registered, the the challenge and response strings are evaluated at <a href=\"http://recaptcha.net\">recaptcha.net</a> to determine if the form content has been entered by a bot.") ?>
  </p>

     <?= $form ?>
</div>
