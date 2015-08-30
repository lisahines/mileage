<?php
//define( "SITE_MODE", "jonathanhines.ca");
define( "SITE_MODE", "local");
switch( SITE_MODE ) {
    case "jonathanhines.ca":
        define( "DB_NAME", "jhines_mileage" );
        define( "DB_USER", "jhines_driver" );
        define( "DB_SERVER", "localhost" );
        define( "DB_PASSWORD", "zoom2" );
        define( "HTTP_SITE_ROOT", "/" );
        break;
    
    case "local":
    default:
        define( "DB_NAME", "mileage" );
        define( "DB_USER", "root" );
        define( "DB_SERVER", "localhost" );
        define( "DB_PASSWORD", "admin" );
        define( "HTTP_SITE_ROOT", "/mileage/" );
        break;
    
}
?>
