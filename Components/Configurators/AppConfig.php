<?php

namespace GingerPlugins\Components\Configurators;

class AppConfig
{
    const HeaderXHash = 'x-hash';

    const IMAGE_FOLDER = 'Images';

    /**
     * This is the base of the app URI.
     * This Uri is used in the update schema example
     * Note: Remove possible trailing slashes.
     */
    const AppUri = 'https://twenty-donkeys-bow.loca.lt';

    /**
     * This contains a secret key which is unique for this App.
     * You can find this as a property of the App in the Developer App Center
     * This key is used in the AppConnectorTrait while validate the Hash
     */
    const AppSecretKey = 'srnembxie7d9lrgx4xzyzkxinjnuyqny';

    /**
     * This is the URI of the handshake. Use this to validate calls from the App store.
     * Example: https://demo.securearea.eu/Handshake.php
     * This Uri is used in the AppConnectorTrait.php
     */
    const AppHandshakeUri = BankConfig::AppUri . '/handshake';

    /**
     * This is the URI of the Uninstall. Use this to validate calls from the App store.
     * Example: https://demo.securearea.eu/UnInstall.php
     * This Uri is used in the AppConnectorTrait.php
     */
    const AppUninstallUri = BankConfig::AppUri . '/uninstall';

    /**
     * This is the URI of the Install. Use this to validate calls from the App store.
     * Example: https://demo.securearea.eu/Install.php
     * This Uri is used in the AppConnectorTrait.php
     */
    const AppInstallUri = BankConfig::AppUri . '/install';

    /**
     * If CredentialStorageType is SQL setup the databasehost for storage
     * This setting is used in Sql\Connecion.php called by Data_Credential.php
     */
    const DatabaseHost = 'ID264285_gpeccvshop.db.hosting-cluster.nl';

    /**
     * If CredentialStorageType is SQL setup the databasename for storage
     * This setting is used in Sql\Connecion.php called by Data_Credential.php
     */
    const DatabaseName = 'ID264285_gpeccvshop';

    /**
     * If CredentialStorageType is SQL setup the databaseuser for storage
     * This setting is used in Sql\Connecion.php called by Data_Credential.php
     */
    const DatabaseUsername = 'ID264285_gpeccvshop';

    /**
     * If CredentialStorageType is SQL setup the databasepassword for storage
     * This setting is used in Sql\Connecion.php called by Data_Credential.php
     */
    const DatabasePassword = 'totGag-wibduv-7vywde';

}