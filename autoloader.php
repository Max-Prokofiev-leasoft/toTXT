<?php

include "Log/Log.php";
include "Http/Hash.php";
include "Components/Traits/WebRequestTrait.php";
include "Components/Traits/CodeBlockTrait.php";
include "Data/Data_Transaction.php";
include "Components/Traits/AppConnectorTrait.php";
include "Components/Classes/ClientBuilder.php";
include "Components/Classes/Helper.php";
include "Components/Classes/OrderBuilder.php";
include "Components/Classes/PluginGateway.php";
include "Components/Classes/FunctionalityGateway.php";
include "Components/Classes/Redefiner.php";
include "Components/Library/vendor/autoload.php";
include "Components/Configurators/AppConfig.php";
include "Components/Configurators/GingerConfig.php";
include "Components/Configurators/BankConfig.php";
include "Entities/PaymentMethod.php";
include "Entities/Transaction.php";
include "Entities/Transaction/Address.php";
include "Services/PaymentMethodsRepository/PaymentMethodsCollection.php";
include "Sql/Connection.php";
include "Data/Translations.php";
include "Data/Data_Credential.php";
include "Entities/Credential.php";
include "Exceptions/InvalidApiResponse.php";
include "Exceptions/InvalidCredentialException.php";
include "Exceptions/InvalidHashException.php";
include "Exceptions/InvalidJsonException.php";
include "Json/JsonSerializer.php";
include "Http/WebRequest.php";

function isRequiredAutoloader(): bool
{
    return true;
}