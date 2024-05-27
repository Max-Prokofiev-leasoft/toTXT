<html>
<head>
    <title></title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
          integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css"
          integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
</head>
<body>
<h1><?php echo $gingerTranslations['GINGER_BANK_LABEL'] ?></h1>
<?php use GingerPlugins\Components\Configurators\BankConfig;

if (!$ginger_api_key) {
    echo $gingerTranslations['GINGER_INSTALLMANUAL'];
} else {
    echo $gingerTranslations['GINGER_EDITMANUAL'];
} ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Paymethods</h3>
    </div>
    <div class="panel-body">
        <p>The following paymethods will become available in your webshop.</p>

        <?php foreach ($paymentMethods as $oPaymethod) {
            echo '<span style="display: inline-block; width: 75px;">';
            echo sprintf('<img src="%s" style="width: 50px; padding: 7px 0" /> ',$oPaymethod->icon);
            echo '</span>';
            echo $oPaymethod->name;
            echo '<br />';
        } ?>

        <form action="<?php echo BankConfig::AppInstallUri; ?>" method="post">
            <input type="hidden" name="language" id="language" value="<?php echo $language ?>"/>
            <input type="hidden" name="api_public" id="api_public" value="<?php echo $api_public ?>"/>
            <input type="hidden" name="install_type" id="install_type" value="app_psp"/>

            <label for="ginger_api_key"
                   style="margin-top: 10px"><?php echo $gingerTranslations['GINGER_APIKEY']; ?></label>
            <input type="text" name="ginger_api_key" id="ginger_api_key" value="<?php echo $ginger_api_key; ?>"
                   style="width: 25%"/><br>

            <label for="ginger_api_key"
                   style="margin-top: 10px"><?php echo $gingerTranslations['GINGER_AFTERPAYAPIKEY']; ?></label>
            <input type="text" name="ginger_afterpay_test_api_key" id="ginger_afterpay_test_api_key"
                   value="<?php echo $ginger_afterpay_test_api_key; ?>" style="width: 25%"/><br>
            <span style="color: #959595; font-style: italic"><?php echo $gingerTranslations['GINGER_AFTERPAYAPIKEYDESC']; ?></span><br>

            <label for="ginger_api_key"
                   style="margin-top: 10px"><?php echo $gingerTranslations['GINGER_KLARNAAPIKEY']; ?></label>
            <input type="text" name="ginger_klarna_test_api_key" id="ginger_klarna_test_api_key"
                   value="<?php echo $ginger_klarna_test_api_key; ?>" style="width: 25%"/><br>
            <span style="color: #959595; font-style: italic"><?php echo $gingerTranslations['GINGER_KLARNAAPIKEYDESC']; ?></span><br>

            <label for="ginger_afterpay_countries"
                   style="margin-top: 10px"><?php echo $gingerTranslations['GINGER_AFTERPAYCOUNTRIES']; ?></label>
            <input type="text" name="ginger_afterpay_countries" id="ginger_afterpay_countries"
                   value="<?php if (!$ginger_api_key && !$ginger_afterpay_countries) echo 'NL, BE'; else echo $ginger_afterpay_countries; ?>"
                   style="width: 25%"/><br>
            <span style="color: #959595; font-style: italic"><?php echo $gingerTranslations['GINGER_AFTERPAYCOUNTRIESDESC']; ?></span><br>

            <?php if (!$ginger_api_key) { ?>
                <button name="action" class="btn btn-success" style="margin-top: 20px"
                        value="install"><?php echo $gingerTranslations['GINGER_INSTALLBTN']; ?></button>
            <?php } else { ?>
                <button name="action" class="btn btn-success" style="margin-top: 20px" value='update'>Save</button>
            <?php } ?>
        </form>
    </div>
</div>
</body>
</html>